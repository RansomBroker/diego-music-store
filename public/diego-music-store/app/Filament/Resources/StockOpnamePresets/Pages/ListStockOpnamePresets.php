<?php

namespace App\Filament\Resources\StockOpnamePresets\Pages;

use App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource;
use App\Helpers\BranchHelper;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnamePresets extends ListRecords
{
    protected static string $resource = StockOpnamePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('lg')
                ->mutateFormDataUsing(function (array $data): array {
                    $user = auth()->user();
                    $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                    if (!$user || !$user->hasRole($allowedRoles)) {
                        abort(403);
                    }

                    $activeBranchId = BranchHelper::getActiveBranchId();
                    if ($activeBranchId && empty($data['branch_id'])) {
                        $data['branch_id'] = $activeBranchId;
                    }

                    return $data;
                }),
        ];
    }
}
