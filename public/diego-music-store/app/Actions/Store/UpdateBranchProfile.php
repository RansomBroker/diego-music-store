<?php

namespace App\Actions\Store;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class UpdateBranchProfile
{
    /**
     * Update or register store branch profile.
     *
     * @param Branch $branch
     * @param array $data
     * @return Branch
     */
    public function execute(Branch $branch, array $data): Branch
    {
        return DB::transaction(function () use ($branch, $data) {
            $branch->update([
                'name' => $data['name'] ?? $branch->name,
                'store_name' => $data['store_name'] ?? $branch->store_name,
                'address' => $data['address'] ?? $branch->address,
                'phone' => $data['phone'] ?? $branch->phone,
                'logo_path' => array_key_exists('logo_path', $data) ? $data['logo_path'] : $branch->logo_path,
                'is_active' => $data['is_active'] ?? $branch->is_active,
            ]);

            return $branch;
        });
    }
}
