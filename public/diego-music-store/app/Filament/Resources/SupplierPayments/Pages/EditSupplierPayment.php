<?php

namespace App\Filament\Resources\SupplierPayments\Pages;

use App\Filament\Resources\SupplierPayments\SupplierPaymentResource;
use App\Actions\SupplierPayment\UpdateSupplierPayment as UpdateSupplierPaymentAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class EditSupplierPayment extends EditRecord
{
    protected static string $resource = SupplierPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $payment = $this->record;
        $data['items'] = [];
        foreach ($payment->items as $item) {
            $data['items'][] = [
                'purchase_transaction_id' => $item->purchase_transaction_id,
                'amount_due' => $item->amount_due,
                'amount_paid' => $item->amount_paid,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateSupplierPaymentAction::class)->execute($record, $data);
    }
}
