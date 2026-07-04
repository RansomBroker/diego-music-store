<?php

namespace App\Filament\Resources\CashTransactions\Pages;

use App\Filament\Resources\CashTransactions\CashTransactionResource;
use App\Actions\CashManagement\CreateCashTransaction as CreateActionClass;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCashTransaction extends CreateRecord
{
    protected static string $resource = CashTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateActionClass::class)->execute($data);
    }
}
