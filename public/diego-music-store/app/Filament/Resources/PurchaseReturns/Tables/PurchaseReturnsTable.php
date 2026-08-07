<?php

namespace App\Filament\Resources\PurchaseReturns\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_no')
                    ->label('No. Retur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('purchaseTransaction.transaction_no')
                    ->label('No. Transaksi Pembelian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('return_date')
                    ->label('Tanggal Retur')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Retur / Refund')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted (Selesai)',
                        default => ucfirst($state),
                    }),

                TextColumn::make('reason')
                    ->label('Alasan Retur')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('Diproses Oleh')
                    ->searchable(),
            ])
            ->actions([
                \Filament\Actions\Action::make('post')
                    ->label('Post (Posting)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Posting Retur Pembelian')
                    ->modalDescription('Apakah Anda yakin ingin memposting retur ini? Stok persediaan akan otomatis berkurang dan jurnal retur akan dicatat.')
                    ->action(function ($record) {
                        app(\App\Actions\Purchases\PostPurchaseReturn::class)->execute($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Retur Pembelian Berhasil Diposting')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
