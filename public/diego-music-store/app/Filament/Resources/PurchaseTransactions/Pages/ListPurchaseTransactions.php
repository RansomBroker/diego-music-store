<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseTransactions extends ListRecords
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
