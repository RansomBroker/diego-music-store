<?php

namespace App\Filament\Resources\SupplierPayments\Pages;

use App\Actions\SupplierPayment\CreateSupplierPayment as CreateSupplierPaymentAction;
use App\Filament\Resources\SupplierPayments\SupplierPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class ListSupplierPayments extends ListRecords
{
    protected static string $resource = SupplierPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl')
                ->using(fn (array $data): Model => app(CreateSupplierPaymentAction::class)->execute($data)),
        ];
    }
}
