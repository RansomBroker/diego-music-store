<?php

namespace App\Filament\Resources\CustomerLabels\Pages;

use App\Filament\Resources\CustomerLabels\CustomerLabelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerLabels extends ListRecords
{
    protected static string $resource = CustomerLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('md'),
        ];
    }
}
