<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Actions\Procurement\UpdatePurchaseOrder as UpdatePurchaseOrderAction;
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
        $data['discount_type'] = $purchaseOrder->discount_type ?? 'fixed';
        $data['discount_value'] = $purchaseOrder->discount_value ?? 0;
        $data['items'] = [];
        foreach ($purchaseOrder->items as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount_type' => $item->discount_type ?? 'fixed',
                'discount_value' => $item->discount_value ?? 0,
                'tax_rate' => $item->tax_rate,
                'notes' => $item->notes,
                'unit_id' => $item->unit_id,
            ];
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdatePurchaseOrderAction::class)->execute($record, $data);
    }
}
