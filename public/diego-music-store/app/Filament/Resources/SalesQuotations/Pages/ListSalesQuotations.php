<?php

namespace App\Filament\Resources\SalesQuotations\Pages;

use App\Actions\Sales\CreateSalesQuotation;
use App\Filament\Resources\SalesQuotations\SalesQuotationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListSalesQuotations extends ListRecords
{
    protected static string $resource = SalesQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->using(fn (array $data): Model => app(CreateSalesQuotation::class)->execute($data)),
        ];
    }
}
