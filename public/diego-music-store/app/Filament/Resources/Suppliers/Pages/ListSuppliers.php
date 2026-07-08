<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Actions\Supplier\CreateSupplier;
use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('2xl')
                ->using(fn (array $data): Model => app(CreateSupplier::class)->execute($data)),
        ];
    }
}
