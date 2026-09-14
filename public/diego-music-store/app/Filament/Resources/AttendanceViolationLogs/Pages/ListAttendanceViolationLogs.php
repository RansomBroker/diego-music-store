<?php

namespace App\Filament\Resources\AttendanceViolationLogs\Pages;

use App\Filament\Resources\AttendanceViolationLogs\AttendanceViolationLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceViolationLogs extends ListRecords
{
    protected static string $resource = AttendanceViolationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
