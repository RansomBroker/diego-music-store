<?php

namespace App\Filament\Resources\InventoryMutations\Pages;

use App\Filament\Resources\InventoryMutations\InventoryMutationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventoryMutations extends ListRecords
{
    protected static string $resource = InventoryMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
