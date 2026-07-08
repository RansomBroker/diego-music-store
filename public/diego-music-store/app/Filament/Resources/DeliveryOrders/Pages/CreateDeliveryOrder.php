<?php

namespace App\Filament\Resources\DeliveryOrders\Pages;

use App\Actions\DeliveryOrder\CreateDeliveryOrder as CreateDeliveryOrderAction;
use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDeliveryOrderAction::class)->execute($data);
    }
}
