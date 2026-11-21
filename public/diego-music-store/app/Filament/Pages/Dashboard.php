<?php

namespace App\Filament\Pages;

use App\Helpers\PermissionHelper;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.dashboard.view');
    }
}

