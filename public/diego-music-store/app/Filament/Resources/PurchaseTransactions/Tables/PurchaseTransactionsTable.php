<?php

namespace App\Filament\Resources\PurchaseTransactions\Tables;

use App\Actions\Procurement\DeletePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\Procurement\UpdatePurchaseTransaction as UpdateAction;
use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_no')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('Rujukan PO')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tunai' => 'success',
                        'Kredit' => 'warning',
                        'Konsinyasi' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
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
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'posted'], true) && PurchaseTransactionResource::canEdit($record))
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->details as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'qty_po' => $item->qty_po,
                                'qty_received' => $item->qty_received,
                                'qty_bonus' => $item->qty_bonus ?? 0,
                                'unit_id' => $item->unit_id,
                                'price' => $item->price,
                                'update_cost_price' => (bool)$item->update_cost_price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                            ];
                        }
                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        abort_unless(PurchaseTransactionResource::canEdit($record), 403);
                        try {
                            return app(UpdateAction::class)->execute($record, $data);
                        } catch (\InvalidArgumentException|\RuntimeException $e) {
                            Notification::make()
                                ->title('Tidak dapat menyimpan perubahan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            throw new Halt();
                        }
                    }),

                ViewAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn ($record) => $record->status !== 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->details as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'qty_po' => $item->qty_po,
                                'qty_received' => $item->qty_received,
                                'qty_bonus' => $item->qty_bonus ?? 0,
                                'unit_id' => $item->unit_id,
                                'price' => $item->price,
                                'update_cost_price' => (bool)$item->update_cost_price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                            ];
                        }
                        return $data;
                    }),

                DeleteAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'posted'], true) && PurchaseTransactionResource::canDelete($record))
                    ->modalDescription(fn ($record) => $record->status === 'posted'
                        ? 'Transaksi ini sudah berstatus Posted. Menghapusnya akan otomatis membatalkan (rollback) stok masuk, jurnal akuntansi, dan hutang supplier terkait. Lanjutkan?'
                        : null)
                    ->using(function (Model $record): bool {
                        abort_unless(PurchaseTransactionResource::canDelete($record), 403);
                        try {
                            app(DeletePurchaseTransaction::class)->execute($record);

                            return true;
                        } catch (\InvalidArgumentException|\RuntimeException $e) {
                            Notification::make()
                                ->title('Tidak dapat menghapus transaksi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            throw new Halt();
                        }
                    }),

                Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft' && PurchaseTransactionResource::canEdit($record))
                    ->action(function ($record) {
                        abort_unless(PurchaseTransactionResource::canEdit($record), 403);
                        app(PostPurchaseTransaction::class)->execute($record);
                    }),

                Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record) => "/backoffice/stock-movements?reference_type=PurchaseTransaction&reference_id={$record->id}")
                    ->visible(fn ($record) => $record->status === 'posted'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $deleteAction = app(DeletePurchaseTransaction::class);
                            $failedMessages = [];

                            foreach ($records as $record) {
                                if (! PurchaseTransactionResource::canDelete($record)) {
                                    $failedMessages[] = "{$record->transaction_no}: tidak memiliki izin hapus.";
                                    continue;
                                }

                                try {
                                    $deleteAction->execute($record);
                                } catch (\InvalidArgumentException|\RuntimeException $e) {
                                    $failedMessages[] = "{$record->transaction_no}: {$e->getMessage()}";
                                }
                            }

                            if (! empty($failedMessages)) {
                                Notification::make()
                                    ->title('Sebagian transaksi gagal dihapus')
                                    ->body(implode(' ', $failedMessages))
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
