<?php

namespace App\Filament\Resources\CommissionSchemes\Pages;

use App\Filament\Resources\CommissionSchemes\CommissionSchemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommissionSchemes extends ListRecords
{
    protected static string $resource = CommissionSchemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
