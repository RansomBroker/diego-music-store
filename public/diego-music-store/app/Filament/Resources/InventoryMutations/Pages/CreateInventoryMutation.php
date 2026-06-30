<?php

namespace App\Filament\Resources\InventoryMutations\Pages;

use App\Filament\Resources\InventoryMutations\InventoryMutationResource;
use App\Actions\InventoryMutation\CreateInventoryMutation as CreateInventoryMutationAction;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryMutation extends CreateRecord
{
    protected static string $resource = InventoryMutationResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(CreateInventoryMutationAction::class)->execute($data);
    }
}
