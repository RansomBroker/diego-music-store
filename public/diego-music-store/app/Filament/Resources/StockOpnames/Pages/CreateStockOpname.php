<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Actions\StockOpname\CreateStockOpname as CreateStockOpnameAction;
use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateStockOpnameAction::class)->execute($data);
    }
}
