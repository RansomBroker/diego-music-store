<?php

namespace App\Filament\Resources\AttendanceRadiuses\Pages;

use App\Filament\Resources\AttendanceRadiuses\AttendanceRadiusResource;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceRadiuses extends ListRecords
{
    protected static string $resource = AttendanceRadiusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
