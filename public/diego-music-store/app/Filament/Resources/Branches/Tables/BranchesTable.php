<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Actions\Branch\RegenerateBranchProductSkus;
use App\Actions\Branch\UpdateBranch;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Branch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl('/images/default-store-logo.png'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Cabang')
                    ->weight('bold'),

                TextColumn::make('store_name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Toko / Outlet')
                    ->description(fn ($record) => $record->city ? "{$record->city}, {$record->province}" : '-'),

                TextColumn::make('manager.name')
                    ->searchable()
                    ->sortable()
                    ->label('Manajer Cabang')
                    ->placeholder('Belum Ditunjuk')
                    ->badge()
                    ->color('info'),

                TextColumn::make('phone')
                    ->searchable()
                    ->label('No. Telepon / Email')
                    ->description(fn ($record) => $record->email ?: '-'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Total Staf')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('shift_time')
                    ->label('Jam Shift Kerja')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn ($record) => ($record->shift_start_time ? substr($record->shift_start_time, 0, 5) : '09:00') . ' - ' . ($record->shift_end_time ? substr($record->shift_end_time, 0, 5) : '17:00')),

                TextColumn::make('sku_prefix')
                    ->label('Prefix SKU')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('journal_prefix')
                    ->label('Prefix Jurnal')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),

                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),

                TextColumn::make('inventoryAccount.name')
                    ->label('Akun Persediaan')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('interbranchReceivableAccount.name')
                    ->label('Piutang Antar Cabang')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('interbranchPayableAccount.name')
                    ->label('Hutang Antar Cabang')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->steps(BranchForm::getWizardSteps())
                    ->using(function (Model $record, array $data): Model {
                        $skuResult = null;
                        $updated = app(UpdateBranch::class)->execute($record, $data, $skuResult);

                        if ($skuResult !== null) {
                            Notification::make()
                                ->title('Generate SKU Berhasil')
                                ->body(RegenerateBranchProductSkus::formatResultMessage($skuResult))
                                ->success()
                                ->send();
                        }

                        return $updated;
                    }),

                Action::make('regenerate_skus')
                    ->label('Generate SKU')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->visible(fn (Branch $record): bool => BranchResource::canEdit($record))
                    ->modalHeading(fn (Branch $record): string => "Generate Ulang SKU Barang — {$record->name}")
                    ->modalDescription('SKU barang yang sudah diimport akan di-generate ulang menggunakan Prefix SKU cabang ini. Barcode, stok, harga, dan histori transaksi tetap aman.')
                    ->modalSubmitActionLabel('Generate Sekarang')
                    ->form(fn (Branch $record): array => [
                        TextInput::make('sku_prefix')
                            ->label('Prefix Kode Produk / SKU')
                            ->default($record->sku_prefix ?: 'DG')
                            ->required()
                            ->maxLength(20)
                            ->helperText('Otomatis disimpan ke cabang ini jika berbeda.'),
                        Select::make('scope')
                            ->label('Cakupan Produk Target')
                            ->options(RegenerateBranchProductSkus::scopeOptions($record))
                            ->default(RegenerateBranchProductSkus::SCOPE_ALL)
                            ->required()
                            ->native(false),
                        Select::make('mode')
                            ->label('Mode Penggantian SKU')
                            ->options(RegenerateBranchProductSkus::modeOptions())
                            ->default(RegenerateBranchProductSkus::MODE_MISSING_PREFIX)
                            ->required()
                            ->native(false),
                        Select::make('format')
                            ->label('Format Kode SKU Baru')
                            ->options(RegenerateBranchProductSkus::formatOptions())
                            ->default(RegenerateBranchProductSkus::FORMAT_RANDOM)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (Branch $record, array $data): void {
                        abort_unless(BranchResource::canEdit($record), 403);

                        $result = app(RegenerateBranchProductSkus::class)->execute(
                            branch: $record,
                            prefixOverride: $data['sku_prefix'] ?? $record->sku_prefix,
                            scope: $data['scope'] ?? RegenerateBranchProductSkus::SCOPE_ALL,
                            mode: $data['mode'] ?? RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
                            format: $data['format'] ?? RegenerateBranchProductSkus::FORMAT_RANDOM,
                            savePrefixToBranch: true,
                        );

                        Notification::make()
                            ->title('Generate SKU Berhasil')
                            ->body(RegenerateBranchProductSkus::formatResultMessage($result))
                            ->success()
                            ->send();
                    }),

                Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->url(fn ($record) => StockMovementResource::getUrl('index', [
                        'tableFilters[branch_id][value]' => $record->id,
                    ])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
