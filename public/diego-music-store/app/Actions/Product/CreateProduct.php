<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    /**
     * Execute the action to create a product.
     *
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function execute(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the Product
            $product = Product::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $hasVariants = filter_var($data['has_variants'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // 2. If it's physical with variants, save each variant
            if ($product->isPhysical() && $hasVariants && !empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'] ?? null,
                        'barcode' => $variantData['barcode'] ?? null,
                        'name' => $variantData['name'],
                        'price' => $variantData['price'] ?? 0,
                        'cost_price' => $variantData['cost_price'] ?? 0,
                        'hpp' => $variantData['hpp'] ?? ($variantData['cost_price'] ?? 0),
                        'is_active' => true,
                    ]);

                    // Save tier prices
                    if (!empty($variantData['tier_prices'])) {
                        foreach ($variantData['tier_prices'] as $tierId => $price) {
                            if ($price !== null && $price !== '') {
                                ProductTierPrice::create([
                                    'product_variant_id' => $variant->id,
                                    'pricing_tier_id' => $tierId,
                                    'price' => $price,
                                ]);
                            }
                        }
                    }

                    // Save branch prices
                    if (!empty($variantData['branch_prices'])) {
                        foreach ($variantData['branch_prices'] as $branchId => $price) {
                            if ($price !== null && $price !== '') {
                                ProductBranchPrice::create([
                                    'product_variant_id' => $variant->id,
                                    'branch_id' => $branchId,
                                    'price' => $price,
                                ]);
                            }
                        }
                    }

                    // Save branch stocks
                    if (!empty($variantData['branch_stocks'])) {
                        foreach ($variantData['branch_stocks'] as $branchId => $stock) {
                            ProductBranchStock::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'stock' => $stock ?? 0,
                            ]);
                        }
                    }
                }
            } else {
                // 3. Create a single default variant
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'name' => null, // Default variant
                    'price' => $data['price'] ?? 0,
                    'cost_price' => $data['cost_price'] ?? 0,
                    'hpp' => $data['hpp'] ?? ($data['cost_price'] ?? 0),
                    'is_active' => true,
                ]);

                // Save tier prices
                if (!empty($data['tier_prices'])) {
                    foreach ($data['tier_prices'] as $tierId => $price) {
                        if ($price !== null && $price !== '') {
                            ProductTierPrice::create([
                                'product_variant_id' => $variant->id,
                                'pricing_tier_id' => $tierId,
                                'price' => $price,
                            ]);
                        }
                    }
                }

                // Save branch prices
                if (!empty($data['branch_prices'])) {
                    foreach ($data['branch_prices'] as $branchId => $price) {
                        if ($price !== null && $price !== '') {
                            ProductBranchPrice::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'price' => $price,
                            ]);
                        }
                    }
                }

                // Save branch stocks (only if physical)
                if ($product->isPhysical() && !empty($data['branch_stocks'])) {
                    foreach ($data['branch_stocks'] as $branchId => $stock) {
                        ProductBranchStock::create([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                            'stock' => $stock ?? 0,
                        ]);
                    }
                }

                // Save bundle components (only if bundle)
                if ($product->isBundle() && !empty($data['bundle_items'])) {
                    foreach ($data['bundle_items'] as $item) {
                        if (!empty($item['child_variant_id'])) {
                            ProductBundle::create([
                                'parent_variant_id' => $variant->id,
                                'child_variant_id' => $item['child_variant_id'],
                                'quantity' => $item['quantity'] ?? 1,
                            ]);
                        }
                    }
                }
            }

            return $product;
        });
    }
}
