<?php

namespace App\Filament\Resources\SalesInvoices\Tables;

use App\Actions\Sales\PostSalesInvoice;
use App\Actions\Sales\UpdateSalesInvoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('payment_type')
                    ->label('Jenis Bayar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tunai' => 'success',
                        'Kredit' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted (Selesai)',
                        'cancelled' => 'Batal',
                        default => ucfirst($state),
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('4xl')
                    ->disabled(fn ($record) => $record->status === 'posted')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'unit_id' => $item->unit_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type,
                                'discount_value' => $item->discount_value,
                            ];
                        }
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdateSalesInvoice::class)->execute($record, $data)),
                Action::make('post')
                    ->label('Post (Posting)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Posting Faktur Penjualan')
                    ->modalDescription('Apakah Anda yakin ingin memposting faktur ini? Stok barang akan otomatis berkurang dan jurnal akuntansi akan dibuat.')
                    ->action(function ($record) {
                        app(PostSalesInvoice::class)->execute($record);
                        Notification::make()
                            ->title('Faktur Berhasil Diposting')
                            ->success()
                            ->send();
                    }),
                Action::make('print')
                    ->label('Cetak Faktur')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('backoffice.sales-invoices.print', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->status === 'posted'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
