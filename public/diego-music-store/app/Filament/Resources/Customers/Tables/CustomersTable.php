<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Actions\Customer\UpdateCustomer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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

                TextColumn::make('label.name')
                    ->badge()
                    ->color('info')
                    ->label('Label'),

                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->label('Tgl Lahir'),

                IconColumn::make('is_loyalty_member')
                    ->boolean()
                    ->sortable()
                    ->label('Member'),

                TextColumn::make('loyalty_points')
                    ->numeric()
                    ->sortable()
                    ->label('Poin'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl')
                    ->using(fn (Model $record, array $data): Model => app(UpdateCustomer::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
