<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Actions\StockOpname\CreateStockOpname as CreateStockOpnameAction;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CreateStockOpnameAction::class)->execute($data);
    }
}
