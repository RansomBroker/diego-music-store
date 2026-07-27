<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Actions\Accounting\CreateAsset as CreateAssetAction;
use App\Filament\Resources\Assets\AssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->using(fn (array $data): Model => app(CreateAssetAction::class)->execute($data)),
        ];
    }
}
