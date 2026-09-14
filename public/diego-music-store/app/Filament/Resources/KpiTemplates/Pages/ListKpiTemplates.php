<?php

namespace App\Filament\Resources\KpiTemplates\Pages;

use App\Filament\Resources\KpiTemplates\KpiTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiTemplates extends ListRecords
{
    protected static string $resource = KpiTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Template KPI'),
        ];
    }
}
