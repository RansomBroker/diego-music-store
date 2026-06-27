<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('xl')
                ->using(fn (array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Unit\CreateUnit::class)->execute($data)),
        ];
    }
}
