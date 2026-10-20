<?php

namespace App\Filament\Resources\StockOpnamePeriods\Pages;

use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use App\Helpers\BranchHelper;
use App\Models\StockOpnamePeriod;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnamePeriods extends ListRecords
{
    protected static string $resource = StockOpnamePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
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

                    $period = Carbon::parse($data['period'])->startOfMonth();
                    $data['period'] = $period->toDateString();
                    $data['name'] = trim($data['name'] ?? '') ?: 'SO ' . $period->translatedFormat('F Y');
                    $data['status'] = StockOpnamePeriod::STATUS_DRAFT;
                    $data['created_by'] = $user->id;

                    return $data;
                }),
        ];
    }
}
