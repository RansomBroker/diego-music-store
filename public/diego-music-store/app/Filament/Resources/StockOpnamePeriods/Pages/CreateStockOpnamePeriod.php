<?php

namespace App\Filament\Resources\StockOpnamePeriods\Pages;

use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use App\Models\StockOpnamePeriod;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOpnamePeriod extends CreateRecord
{
    protected static string $resource = StockOpnamePeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            abort(403);
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            $data['branch_id'] = $activeBranchId;
        }

        $period = Carbon::parse($data['period'])->startOfMonth();
        $data['period'] = $period->toDateString();
        $data['name'] = trim($data['name'] ?? '') ?: 'SO ' . $period->translatedFormat('F Y');
        $data['status'] = StockOpnamePeriod::STATUS_DRAFT;
        $data['created_by'] = $user->id;

        return $data;
    }
}
