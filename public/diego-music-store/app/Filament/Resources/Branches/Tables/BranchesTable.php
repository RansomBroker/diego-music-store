<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Actions\Branch\UpdateBranch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Branch Name'),

                TextColumn::make('store_name')
                    ->searchable()
                    ->sortable()
                    ->label('Store / Shop Name'),

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
