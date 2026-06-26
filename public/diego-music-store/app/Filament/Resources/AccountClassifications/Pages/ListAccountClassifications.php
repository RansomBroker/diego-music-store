<?php

namespace App\Filament\Resources\AccountClassifications\Pages;

use App\Filament\Resources\AccountClassifications\AccountClassificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountClassifications extends ListRecords
{
    protected static string $resource = AccountClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('md'),
        ];
    }
}
