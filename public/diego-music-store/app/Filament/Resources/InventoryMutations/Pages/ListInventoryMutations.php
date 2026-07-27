<?php

namespace App\Filament\Resources\InventoryMutations\Pages;

use App\Actions\InventoryMutation\CreateInventoryMutation as CreateInventoryMutationAction;
use App\Filament\Resources\InventoryMutations\InventoryMutationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListInventoryMutations extends ListRecords
{
    protected static string $resource = InventoryMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->using(fn (array $data): Model => app(CreateInventoryMutationAction::class)->execute($data)),
        ];
    }
}
