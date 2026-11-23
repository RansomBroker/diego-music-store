<?php

namespace App\Filament\Resources\CashTransactions\Pages;

use App\Filament\Resources\CashTransactions\CashTransactionResource;
use App\Actions\CashManagement\UpdateCashTransaction as UpdateActionClass;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class EditCashTransaction extends EditRecord
{
    protected static string $resource = CashTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancel_delete')
                ->label(fn ($record) => $record->status === 'posted' ? 'Batalkan & Hapus' : 'Hapus')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn ($record) => CashTransactionResource::canDelete($record))
                ->requiresConfirmation()
                ->modalHeading(fn ($record) => $record->status === 'posted' ? 'Batalkan & Hapus Transaksi Kas?' : 'Hapus Draft Transaksi Kas?')
                ->modalDescription(fn ($record) => $record->status === 'posted'
                    ? "Transaksi {$record->transaction_no} beserta jurnal akuntansi terkait akan dihapus permanen. Saldo Kas/Bank di Neraca dan sesi kasir akan otomatis kembali seperti semula."
                    : "Draft transaksi {$record->transaction_no} akan dihapus permanen.")
                ->modalSubmitActionLabel('Ya, Hapus')
                ->action(function ($record) {
                    app(\App\Actions\CashManagement\DeleteCashTransaction::class)->execute($record);

                    \Filament\Notifications\Notification::make()
                        ->title('Transaksi Kas Dihapus')
                        ->body("Transaksi {$record->transaction_no} dan jurnal terkait berhasil dihapus.")
                        ->success()
                        ->send();

                    return redirect()->to(CashTransactionResource::getUrl('index'));
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateActionClass::class)->execute($record, $data);
    }
}
