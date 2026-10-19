<?php

namespace App\Filament\Resources\StockOpnamePresets\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StockOpnamePresetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Lokasi')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Cabang')->sortable(),
                TextColumn::make('description')->label('Keterangan')->limit(50)->placeholder('-'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->assignments()->doesntExist()),
            ]);
    }
}
