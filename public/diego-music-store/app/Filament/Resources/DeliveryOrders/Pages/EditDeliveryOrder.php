<?php

namespace App\Filament\Resources\DeliveryOrders\Pages;

use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use App\Actions\DeliveryOrder\UpdateDeliveryOrder as UpdateDeliveryOrderAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (): bool => in_array($this->record->status, ['shipped', 'delivered', 'cancelled'])), // Cannot delete if processed
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $do = $this->record;
        
        $data['items'] = [];
        foreach ($do->items as $item) {
            $data['items'][] = [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateDeliveryOrderAction::class)->execute($record, $data);
    }
}
