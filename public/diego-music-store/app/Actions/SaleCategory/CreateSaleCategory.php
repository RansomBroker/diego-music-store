<?php

namespace App\Actions\SaleCategory;

use App\Models\SaleCategory;
use Illuminate\Support\Facades\DB;

class CreateSaleCategory
{
    /**
     * Execute the action to create a sale category.
     *
     * @param  array<string, mixed>  $data
     * @return SaleCategory
     */
    public function execute(array $data): SaleCategory
    {
        return DB::transaction(function () use ($data) {
            $isDefault = (bool) ($data['is_default'] ?? false);
            $data['is_default'] = $isDefault;

            if ($isDefault) {
                SaleCategory::query()->where('is_default', true)->update(['is_default' => false]);
            }

            return SaleCategory::create($data);
        });
    }
}
