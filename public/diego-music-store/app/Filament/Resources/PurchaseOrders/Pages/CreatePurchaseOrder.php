<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Actions\Procurement\CreatePurchaseOrder as CreatePurchaseOrderAction;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreatePurchaseOrderAction::class)->execute($data);
    }
}
