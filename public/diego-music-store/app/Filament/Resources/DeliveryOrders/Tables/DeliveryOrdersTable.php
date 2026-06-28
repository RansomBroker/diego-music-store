<?php

namespace App\Filament\Resources\DeliveryOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('do_number')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor DO'),

                TextColumn::make('purchaseOrder.po_number')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor PO Referensi'),

                TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->label('Cabang Penerima'),

                TextColumn::make('received_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Terima'),

                TextColumn::make('shipping_cost')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->label('Ongkos Kirim'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'received' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->label('Status'),

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
                EditAction::make()
                    ->disabled(fn (\Illuminate\Database\Eloquent\Model $record): bool => 
                        $record->status === 'received' // Disable edit button in table if already received
                    ),
                DeleteAction::make()
                    ->disabled(fn (\Illuminate\Database\Eloquent\Model $record): bool => 
                        $record->status === 'received'
                    ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
