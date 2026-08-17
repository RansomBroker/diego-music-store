<?php

namespace App\Filament\Resources\SupplierPayments\Pages;

use App\Filament\Resources\SupplierPayments\SupplierPaymentResource;
use App\Actions\SupplierPayment\UpdateSupplierPayment as UpdateSupplierPaymentAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Actions\SupplierPayment\CancelSupplierPayment;

class EditSupplierPayment extends EditRecord
{
    protected static string $resource = SupplierPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Batalkan Pembayaran')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['draft', 'posted']))
                ->requiresConfirmation()
                ->modalHeading('Batalkan Pembayaran Supplier')
                ->modalDescription('Apakah Anda yakin ingin membatalkan pembayaran ini? Saldo hutang supplier akan diperbarui dan jurnal akan dibatalkan.')
                ->action(function () {
                    app(CancelSupplierPayment::class)->execute($this->record);
                    Notification::make()
                        ->title('Pembayaran Supplier Berhasil Dibatalkan')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $payment = $this->record;
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

        if ($payment->status === 'draft') {
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
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateSupplierPaymentAction::class)->execute($record, $data);
    }
}
