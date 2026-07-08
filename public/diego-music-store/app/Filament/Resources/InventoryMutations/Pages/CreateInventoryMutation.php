<?php

namespace App\Filament\Resources\InventoryMutations\Pages;

use App\Actions\InventoryMutation\CreateInventoryMutation as CreateInventoryMutationAction;
use App\Filament\Resources\InventoryMutations\InventoryMutationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInventoryMutation extends CreateRecord
{
    protected static string $resource = InventoryMutationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateInventoryMutationAction::class)->execute($data);
    }
}
