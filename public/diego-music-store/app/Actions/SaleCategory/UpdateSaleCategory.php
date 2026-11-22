<?php

namespace App\Actions\SaleCategory;

use App\Models\SaleCategory;
use Illuminate\Support\Facades\DB;

class UpdateSaleCategory
{
    /**
     * Execute the action to update a sale category.
     *
     * @param  SaleCategory  $saleCategory
     * @param  array<string, mixed>  $data
     * @return SaleCategory
     */
    public function execute(SaleCategory $saleCategory, array $data): SaleCategory
    {
        return DB::transaction(function () use ($saleCategory, $data) {
            $isDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : (bool) $saleCategory->is_default;

            if (array_key_exists('is_default', $data)) {
                $data['is_default'] = $isDefault;
            }

            if ($isDefault) {
                SaleCategory::query()
                    ->whereKeyNot($saleCategory->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $saleCategory->update($data);
            return $saleCategory;
        });
    }
}
