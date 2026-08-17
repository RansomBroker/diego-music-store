<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Actions\Employee\CreateEmployee;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('2xl')
                ->using(fn (array $data): Model => app(CreateEmployee::class)->execute($data)),
        ];
    }
}
