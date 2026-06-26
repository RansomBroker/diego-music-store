<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Akun'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Akun'),

                TextColumn::make('classificationRelation.name')
                    ->badge()
                    ->color(fn (\App\Models\Account $record): string => match (strtolower($record->classification ?? '')) {
                        'asset' => 'info',
                        'liability' => 'warning',
                        'equity' => 'success',
                        'revenue' => 'primary',
                        'expense' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable()
                    ->label('Klasifikasi'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('md'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
