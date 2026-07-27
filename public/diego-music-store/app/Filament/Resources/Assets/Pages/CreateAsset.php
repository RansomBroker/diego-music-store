<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Actions\Accounting\CreateAsset as CreateAssetAction;
use App\Filament\Resources\Assets\AssetResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateAssetAction::class)->execute($data);
    }
}
