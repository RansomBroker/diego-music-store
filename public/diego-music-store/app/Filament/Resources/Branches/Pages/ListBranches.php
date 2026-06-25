<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->steps(\App\Filament\Resources\Branches\Schemas\BranchForm::getWizardSteps())
                ->using(fn (array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Branch\CreateBranch::class)->execute($data)),
        ];
    }
}
