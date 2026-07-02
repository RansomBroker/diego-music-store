<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchPrice;
use App\Models\ProductBundle;
use App\Helpers\ProductHelper;
use Illuminate\Support\Facades\DB;

class DuplicateProduct
{
    /**
     * Duplicate a product, its variants, and all related pricing details.
     *
     * @param  Product  $product
     * @return Product
     */
    public function execute(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            // 1. Replicate parent product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' - Copy';
            $newProduct->save();

            // 2. Replicate variants and their relations
            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = ProductHelper::generateUniqueSku();
                $newVariant->barcode = ProductHelper::generateUniqueBarcode();
                $newVariant->save();

                // Replicate tier prices
                foreach ($variant->tierPrices as $tierPrice) {
                    $newTierPrice = $tierPrice->replicate();
                    $newTierPrice->product_variant_id = $newVariant->id;
                    $newTierPrice->save();
                }

                // Replicate branch prices
                foreach ($variant->branchPrices as $branchPrice) {
                    $newBranchPrice = $branchPrice->replicate();
                    $newBranchPrice->product_variant_id = $newVariant->id;
                    $newBranchPrice->save();
                }

                // Replicate bundle components if parent variant
                foreach (ProductBundle::where('parent_variant_id', $variant->id)->get() as $bundleItem) {
                    $newBundleItem = $bundleItem->replicate();
                    $newBundleItem->parent_variant_id = $newVariant->id;
                    $newBundleItem->save();
                }
            }

            return $newProduct;
        });
    }
}
