<?php

namespace App\Filament\Resources\Units\Tables;

use App\Actions\Unit\UpdateUnit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Satuan'),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Satuan'),

                ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('xl')
                    ->using(fn (Model $record, array $data): Model => app(UpdateUnit::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
