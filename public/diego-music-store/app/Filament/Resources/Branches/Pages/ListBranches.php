<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Actions\Branch\CreateBranch;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->steps(BranchForm::getWizardSteps())
                ->using(fn (array $data): Model => app(CreateBranch::class)->execute($data)),
        ];
    }
}
