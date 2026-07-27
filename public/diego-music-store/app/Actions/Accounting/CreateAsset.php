<?php

namespace App\Actions\Accounting;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class CreateAsset
{
    /**
     * Execute Asset creation action.
     *
     * @param  array<string, mixed>  $data
     * @return Asset
     */
    public function execute(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            return Asset::create([
                'asset_code'               => $data['asset_code'] ?? Asset::generateAssetCode(),
                'name'                     => $data['name'],
                'category'                 => $data['category'],
                'branch_id'                => $data['branch_id'] ?? null,
                'purchase_date'            => $data['purchase_date'],
                'purchase_cost'            => (float) $data['purchase_cost'],
                'salvage_value'            => (float) ($data['salvage_value'] ?? 0),
                'useful_life_years'        => (int) ($data['useful_life_years'] ?? 5),
                'accumulated_depreciation' => (float) ($data['accumulated_depreciation'] ?? 0),
                'status'                   => 'active',
                'notes'                    => $data['notes'] ?? null,
            ]);
        });
    }
}
