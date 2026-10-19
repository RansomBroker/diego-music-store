<?php

namespace App\Filament\Resources\StockOpnamePeriods\Pages;

use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnamePeriods extends ListRecords
{
    protected static string $resource = StockOpnamePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
