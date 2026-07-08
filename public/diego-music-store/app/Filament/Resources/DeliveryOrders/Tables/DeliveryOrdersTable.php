<?php

namespace App\Filament\Resources\DeliveryOrders\Tables;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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

                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Pelanggan'),

                TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->label('Cabang Pengirim'),

                TextColumn::make('shipping_date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal Kirim'),

                TextColumn::make('shipping_cost')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->label('Ongkos Kirim'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'shipped' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
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
                    ->disabled(fn (Model $record): bool => 
                        in_array($record->status, ['shipped', 'delivered', 'cancelled'])
                    ),
                Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['shipped', 'delivered']))
                    ->url(fn ($record) => StockMovementResource::getUrl('index', [
                        'reference_type' => 'DO',
                        'reference_id' => $record->id,
                    ])),
                DeleteAction::make()
                    ->disabled(fn (Model $record): bool => 
                        in_array($record->status, ['shipped', 'delivered', 'cancelled'])
                    ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
