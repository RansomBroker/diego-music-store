<?php

namespace App\Filament\Resources\SupplierPayments\Tables;

use App\Actions\SupplierPayment\CancelSupplierPayment;
use App\Actions\SupplierPayment\UpdateSupplierPayment as UpdateSupplierPaymentAction;
use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_no')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account.name')
                    ->label('Kas / Bank')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('idr')
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
                        'posted' => 'Posted',
                        'cancelled' => 'Batal',
                        default => ucfirst($state),
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $payment = $record;
                        $data['items'] = [];
                        $linkedTransactionIds = [];

                        foreach ($payment->items as $item) {
                            $pt = $item->purchaseTransaction;
                            if (!$pt) continue;

                            $data['items'][] = [
                                'is_selected' => true,
                                'purchase_transaction_id' => $item->purchase_transaction_id,
                                'transaction_no' => $pt->transaction_no,
                                'invoice_number' => $pt->invoice_number,
                                'transaction_date' => $pt->transaction_date->format('Y-m-d'),
                                'due_date' => $pt->due_date?->format('Y-m-d'),
                                'grand_total' => $pt->grand_total,
                                'amount_due' => $pt->getRemainingUnpaidAmount(),
                                'amount_paid' => $item->amount_paid,
                            ];
                            $linkedTransactionIds[] = $item->purchase_transaction_id;
                        }

                        $otherUnpaidTransactions = \App\Models\PurchaseTransaction::query()
                            ->where('supplier_id', $payment->supplier_id)
                            ->where('purchase_type', 'Kredit')
                            ->where('status', 'posted')
                            ->whereNotIn('id', $linkedTransactionIds)
                            ->get()
                            ->filter(fn ($pt) => $pt->getRemainingUnpaidAmount() > 0);

                        foreach ($otherUnpaidTransactions as $pt) {
                            $data['items'][] = [
                                'is_selected' => false,
                                'purchase_transaction_id' => $pt->id,
                                'transaction_no' => $pt->transaction_no,
                                'invoice_number' => $pt->invoice_number,
                                'transaction_date' => $pt->transaction_date->format('Y-m-d'),
                                'due_date' => $pt->due_date?->format('Y-m-d'),
                                'grand_total' => $pt->grand_total,
                                'amount_due' => $pt->getRemainingUnpaidAmount(),
                                'amount_paid' => 0,
                            ];
                        }

                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdateSupplierPaymentAction::class)->execute($record, $data)),

                ViewAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => $record->status !== 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $payment = $record;
                        $data['items'] = [];

                        foreach ($payment->items as $item) {
                            $pt = $item->purchaseTransaction;
                            if (!$pt) continue;

                            $data['items'][] = [
                                'is_selected' => true,
                                'purchase_transaction_id' => $item->purchase_transaction_id,
                                'transaction_no' => $pt->transaction_no,
                                'invoice_number' => $pt->invoice_number,
                                'transaction_date' => $pt->transaction_date->format('Y-m-d'),
                                'due_date' => $pt->due_date?->format('Y-m-d'),
                                'grand_total' => $pt->grand_total,
                                'amount_due' => $pt->getRemainingUnpaidAmount(),
                                'amount_paid' => $item->amount_paid,
                            ];
                        }

                        return $data;
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'posted']))
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Pembayaran Supplier')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan pembayaran ini? Saldo hutang supplier akan diperbarui dan jurnal akan dibatalkan.')
                    ->action(function ($record) {
                        app(CancelSupplierPayment::class)->execute($record);
                        Notification::make()
                            ->title('Pembayaran Supplier Berhasil Dibatalkan')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),

                Action::make('lihat_jurnal')
                    ->label('Jurnal')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'posted')
                    ->url(fn ($record) => JournalEntryResource::getUrl('index', [
                        'search' => $record->journal_no,
                    ])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
