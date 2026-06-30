<?php

namespace App\Filament\Resources\SupplierPayments\Pages;

use App\Filament\Resources\SupplierPayments\SupplierPaymentResource;
use App\Actions\SupplierPayment\CreateSupplierPayment as CreateSupplierPaymentAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplierPayment extends CreateRecord
{
    protected static string $resource = SupplierPaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateSupplierPaymentAction::class)->execute($data);
    }
}
