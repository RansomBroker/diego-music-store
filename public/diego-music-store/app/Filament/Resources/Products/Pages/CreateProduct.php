<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Product\CreateProduct as CreateProductAction;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateProductAction::class)->execute($data);
    }
}
