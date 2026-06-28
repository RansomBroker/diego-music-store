<?php

namespace App\Filament\Resources\DeliveryOrders\Pages;

use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use App\Actions\Procurement\CreateDeliveryOrder as CreateDeliveryOrderAction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CreateDeliveryOrderAction::class)->execute($data);
    }
}
