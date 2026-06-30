<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use App\Filament\Resources\StockMovements\StockMovementResource;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Branch Name'),

                TextColumn::make('phone')
                    ->searchable()
                    ->label('Phone'),

                TextColumn::make('address')
                    ->limit(50)
                    ->label('Address'),

                ToggleColumn::make('is_active')
                    ->label('Active Status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Created At'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->steps(\App\Filament\Resources\Branches\Schemas\BranchForm::getWizardSteps())
                    ->using(fn (\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Branch\UpdateBranch::class)->execute($record, $data)),
                \Filament\Actions\Action::make('kartu_stok')
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
