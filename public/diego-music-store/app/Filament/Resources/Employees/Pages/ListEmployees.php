<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Actions\Employee\CreateEmployee;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn (): bool => EmployeeResource::canCreate())
                ->modalHeading('Import Data Karyawan & User')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalFooterActions([])
                ->modalContent(fn () => view('filament.components.employee-import-modal')),
            CreateAction::make()
                ->modalWidth('2xl')
                ->using(fn (array $data): Model => app(CreateEmployee::class)->execute($data)),
        ];
    }
}
