<?php

namespace App\Filament\Resources\Units\Pages;

use App\Actions\Unit\CreateUnit;
use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('xl')
                ->using(fn (array $data): Model => app(CreateUnit::class)->execute($data)),
        ];
    }
}
