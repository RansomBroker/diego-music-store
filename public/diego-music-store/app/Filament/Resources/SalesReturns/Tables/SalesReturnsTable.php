<?php

namespace App\Filament\Resources\SalesReturns\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class SalesReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->label('No. Retur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('sale.invoice_number')
                    ->label('No. Faktur Asli')
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

                TextColumn::make('total_refund')
                    ->label('Total Dana Refund')
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
                Action::make('post')
                    ->label('Post (Posting)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Posting Retur Penjualan')
                    ->modalDescription('Apakah Anda yakin ingin memposting retur ini? Stok persediaan akan otomatis bertambah dan jurnal retur akan dicatat.')
                    ->action(function ($record) {
                        app(\App\Actions\Sales\PostSalesReturn::class)->execute($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Retur Penjualan Berhasil Diposting')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
