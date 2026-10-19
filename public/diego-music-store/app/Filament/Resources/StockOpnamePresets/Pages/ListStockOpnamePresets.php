<?php

namespace App\Filament\Resources\StockOpnamePresets\Pages;

use App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnamePresets extends ListRecords
{
    protected static string $resource = StockOpnamePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
