<?php

namespace App\Filament\Resources\AttendanceViolationRules\Pages;

use App\Filament\Resources\AttendanceViolationRules\AttendanceViolationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceViolationRules extends ListRecords
{
    protected static string $resource = AttendanceViolationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
