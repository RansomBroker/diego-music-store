<?php

namespace App\Filament\Resources\StockOpnamePresets\Pages;

use App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOpnamePreset extends CreateRecord
{
    protected static string $resource = StockOpnamePresetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
        if (!$user || !$user->hasRole($allowedRoles)) {
            abort(403);
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            $data['branch_id'] = $activeBranchId;
        }

        return $data;
    }
}
