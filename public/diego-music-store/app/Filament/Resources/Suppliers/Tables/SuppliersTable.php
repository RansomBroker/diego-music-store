<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Supplier / Vendor'),

                TextColumn::make('contact_person')
                    ->searchable()
                    ->label('Contact Person'),

                TextColumn::make('phone')
                    ->label('Telepon'),

                TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),

                TextColumn::make('bank_name')
                    ->label('Bank'),

                TextColumn::make('bank_account_number')
                    ->label('No. Rekening'),

                TextColumn::make('outstanding_debt')
                    ->money('idr')
                    ->sortable()
                    ->label('Hutang Berjalan'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('2xl')
                    ->using(fn (\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Supplier\UpdateSupplier::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
