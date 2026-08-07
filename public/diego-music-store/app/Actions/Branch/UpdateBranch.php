<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class UpdateBranch
{
    /**
     * Execute Branch profile & configuration update.
     *
     * @param Branch $branch
     * @param array $data
     * @return Branch
     */
    public static function execute(Branch $branch, array $data): Branch
    {
        return DB::transaction(function () use ($branch, $data) {
            $branch->update([
                'name'           => $data['name'] ?? $branch->name,
                'store_name'     => $data['store_name'] ?? $branch->store_name,
                'logo_path'      => array_key_exists('logo_path', $data) ? $data['logo_path'] : $branch->logo_path,
                'address'        => $data['address'] ?? $branch->address,
                'phone'          => $data['phone'] ?? $branch->phone,
                'email'          => $data['email'] ?? $branch->email,
                'city'           => $data['city'] ?? $branch->city,
                'province'       => $data['province'] ?? $branch->province,
                'postal_code'    => $data['postal_code'] ?? $branch->postal_code,
                'npwp'           => $data['npwp'] ?? $branch->npwp,
                'bank_info'      => $data['bank_info'] ?? $branch->bank_info,
                'receipt_header' => $data['receipt_header'] ?? $branch->receipt_header,
                'receipt_footer' => $data['receipt_footer'] ?? $branch->receipt_footer,
                'manager_id'     => $data['manager_id'] ?? $branch->manager_id,
                'is_active'      => $data['is_active'] ?? $branch->is_active,
            ]);

            $staffIds = null;
            if (isset($data['users']) && is_array($data['users'])) {
                $staffIds = $data['users'];
            } elseif (isset($data['user_ids']) && is_array($data['user_ids'])) {
                $staffIds = $data['user_ids'];
            }

            if (is_array($staffIds)) {
                $branch->users()->sync($staffIds);
            }

            return $branch->fresh();
        });
    }
}
