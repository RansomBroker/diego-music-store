<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Actions\Customer\CreateCustomer;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('2xl')
                ->using(fn (array $data): Model => app(CreateCustomer::class)->execute($data)),
        ];
    }
}
