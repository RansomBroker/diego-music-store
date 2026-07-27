<?php

namespace App\Filament\Resources\SalesInvoices\Pages;

use App\Actions\Sales\CreateSalesInvoice;
use App\Filament\Resources\SalesInvoices\SalesInvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListSalesInvoices extends ListRecords
{
    protected static string $resource = SalesInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->using(fn (array $data): Model => app(CreateSalesInvoice::class)->execute($data)),
        ];
    }
}
