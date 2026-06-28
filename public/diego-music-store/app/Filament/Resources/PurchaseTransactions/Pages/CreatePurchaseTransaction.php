<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Actions\Procurement\CreatePurchaseTransaction as CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseTransaction extends CreateRecord
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CreateAction::class)->execute($data);
    }
}
