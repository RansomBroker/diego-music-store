<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pelanggan'),

                TextColumn::make('phone')
                    ->searchable()
                    ->label('Telepon'),

                TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                IconColumn::make('is_loyalty_member')
                    ->boolean()
                    ->sortable()
                    ->label('Member'),

                TextColumn::make('loyalty_points')
                    ->numeric()
                    ->sortable()
                    ->label('Poin'),

                TextColumn::make('deposit_balance')
                    ->money('idr')
                    ->sortable()
                    ->label('Saldo Deposit'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
