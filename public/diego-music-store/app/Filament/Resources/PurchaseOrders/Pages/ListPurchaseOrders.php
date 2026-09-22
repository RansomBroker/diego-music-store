<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Actions\Procurement\CreatePurchaseOrder as CreatePurchaseOrderAction;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl')
                ->using(fn (array $data): Model => app(CreatePurchaseOrderAction::class)->execute($data)),
        ];
    }
}
