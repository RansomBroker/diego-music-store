<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Foto')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Produk'),

                TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'physical' => 'Barang Fisik',
                        'bundle' => 'Produk Bundling',
                        'service' => 'Jasa / Layanan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'physical' => 'info',
                        'bundle' => 'warning',
                        'service' => 'success',
                        default => 'gray',
                    })
                    ->label('Tipe'),

                TextColumn::make('unit.name')
                    ->label('Satuan')
                    ->toggleable(),

                TextColumn::make('sku')
                    ->getStateUsing(function ($record) {
                        $variants = $record->variants;
                        if ($variants->count() > 1) {
                            return $variants->count() . ' Varian';
                        }
                        return $variants->first()?->sku ?? '-';
                    })
                    ->label('SKU / Varian'),

                TextColumn::make('price')
                    ->getStateUsing(function ($record) {
                        $variants = $record->variants;
                        if ($variants->count() > 1) {
                            $min = $variants->min('price');
                            $max = $variants->max('price');
                            if ($min === $max) {
                                return 'Rp ' . number_format($min, 0, ',', '.');
                            }
                            return 'Rp ' . number_format($min, 0, ',', '.') . ' - Rp ' . number_format($max, 0, ',', '.');
                        }
                        $price = $variants->first()?->price ?? 0;
                        return 'Rp ' . number_format($price, 0, ',', '.');
                    })
                    ->label('Harga Jual Dasar'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

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
