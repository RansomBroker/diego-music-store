<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Actions\PurchaseOrder\UpdatePurchaseOrder as UpdatePurchaseOrderAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $purchaseOrder = $this->record;
        $data['items'] = [];
        foreach ($purchaseOrder->items as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount_amount' => $item->discount_amount,
                'tax_rate' => $item->tax_rate,
                'notes' => $item->notes,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdatePurchaseOrderAction::class)->execute($record, $data);
    }
}
