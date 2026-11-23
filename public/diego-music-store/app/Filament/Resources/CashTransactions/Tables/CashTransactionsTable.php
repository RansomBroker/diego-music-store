<?php

namespace App\Filament\Resources\CashTransactions\Tables;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashTransactionsTable
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

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Kas Masuk',
                        'out' => 'Kas Keluar',
                        'transfer' => 'Transfer Kas',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('sourceAccount.name')
                    ->label('Kredit (Sumber / Asal)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('destinationAccount.name')
                    ->label('Debit (Beban / Tujuan)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('idr')
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
                        'posted' => 'Posted',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_detail')
                    ->label('')
                    ->tooltip('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => "Detail Transaksi {$record->transaction_no}")
                    ->modalContent(fn ($record) => view('filament.cash-transactions.view-modal', [
                        'record' => $record->loadMissing(['branch', 'sourceAccount', 'destinationAccount', 'creator', 'journalEntry.items.account']),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                EditAction::make(),

                Action::make('lihat_jurnal')
                    ->label('Jurnal')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'posted' && $record->journalEntry !== null)
                    ->url(fn ($record) => JournalEntryResource::getUrl('index', [
                        'search' => $record->journalEntry?->entry_no,
                    ])),

                Action::make('cancel_delete')
                    ->label(fn ($record) => $record->status === 'posted' ? 'Batalkan & Hapus' : 'Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn ($record) => \App\Filament\Resources\CashTransactions\CashTransactionResource::canDelete($record))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->status === 'posted' ? 'Batalkan & Hapus Transaksi Kas?' : 'Hapus Draft Transaksi Kas?')
                    ->modalDescription(fn ($record) => $record->status === 'posted'
                        ? "Transaksi {$record->transaction_no} beserta jurnal akuntansi terkait akan dihapus permanen. Saldo Kas/Bank di Neraca dan sesi kasir akan otomatis kembali seperti semula."
                        : "Draft transaksi {$record->transaction_no} akan dihapus permanen.")
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(function ($record): void {
                        app(\App\Actions\CashManagement\DeleteCashTransaction::class)->execute($record);

                        \Filament\Notifications\Notification::make()
                            ->title('Transaksi Kas Dihapus')
                            ->body("Transaksi {$record->transaction_no} dan jurnal terkait berhasil dihapus.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $action = app(\App\Actions\CashManagement\DeleteCashTransaction::class);
                            foreach ($records as $record) {
                                $action->execute($record);
                            }
                        }),
                ]),
            ]);
    }
}
