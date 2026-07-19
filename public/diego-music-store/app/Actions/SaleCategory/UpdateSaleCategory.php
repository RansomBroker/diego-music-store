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
            $saleCategory->update($data);
            return $saleCategory;
        });
    }
}
