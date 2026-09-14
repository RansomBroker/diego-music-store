<?php

namespace App\Filament\Resources\SalesCommissions;

use App\Filament\Resources\SalesCommissions\Pages\ListSalesCommissions;
use App\Models\SalesCommissionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class SalesCommissionResource extends Resource
{
    protected static ?string $model = SalesCommissionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Rekap & Log Komisi Sales';

    protected static ?string $pluralModelLabel = 'Log Komisi Sales';

    protected static ?string $modelLabel = 'Log Komisi Sales';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Karyawan';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan Sales')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('ID Transaksi')
                    ->formatStateUsing(fn ($state) => '#' . $state),
                Tables\Columns\TextColumn::make('sale_amount')
                    ->label('Nilai Penjualan')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Komisi Earned')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'purple',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (SalesCommissionLog $record) => $record->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                    ]))
                    ->visible(fn (SalesCommissionLog $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesCommissions::route('/'),
        ];
    }
}
