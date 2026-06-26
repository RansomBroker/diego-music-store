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

                TextColumn::make('classification')
                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                        'asset', 'aset / harta' => 'Aset / Harta',
                        'liability', 'kewajiban / hutang' => 'Kewajiban / Hutang',
                        'equity', 'ekuitas / modal' => 'Ekuitas / Modal',
                        'revenue', 'pendapatan / penjualan' => 'Pendapatan / Penjualan',
                        'expense', 'beban / biaya' => 'Beban / Biaya',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'asset', 'aset / harta' => 'info',
                        'liability', 'kewajiban / hutang' => 'warning',
                        'equity', 'ekuitas / modal' => 'success',
                        'revenue', 'pendapatan / penjualan' => 'primary',
                        'expense', 'beban / biaya' => 'danger',
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
