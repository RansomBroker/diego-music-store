<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Actions\Branch\UpdateBranch;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->steps(BranchForm::getWizardSteps())
                    ->using(fn (Model $record, array $data): Model => app(UpdateBranch::class)->execute($record, $data)),

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
