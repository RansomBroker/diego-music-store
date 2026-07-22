<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    /**
     * Execute the action to update a product.
     *
     * @param  Product  $product
     * @param  array<string, mixed>  $data
     * @return Product
     */
    public function execute(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'unit_id' => $data['unit_id'] ?? null,
                'category' => $data['category'] ?? null,
                'brand' => $data['brand'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'minimum_stock' => $data['minimum_stock'] ?? 0,
            ]);

            $hasVariants = filter_var($data['has_variants'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // If physical with variants enabled
            if ($product->isPhysical() && $hasVariants && !empty($data['variants'])) {
                $formVariantIds = array_filter(array_column($data['variants'], 'id'));

                // Delete variants not in the form safely
                $variantsToDelete = $product->variants()->whereNotIn('id', $formVariantIds)->get();
                foreach ($variantsToDelete as $variantToDelete) {
                    try {
                        $variantToDelete->delete();
                    } catch (\Illuminate\Database\QueryException $e) {
                        if ($e->getCode() == '23000') {
                            $variantToDelete->update(['is_active' => false]);
                        } else {
                            throw $e;
                        }
                    }
                }

                foreach ($data['variants'] as $variantData) {
                    if (!empty($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update([
                            'sku' => $variantData['sku'] ?? null,
                            'barcode' => $variantData['barcode'] ?? null,
                            'name' => $variantData['name'],
                            'price' => $variantData['price'] ?? 0,
                            'discount_value' => $variantData['discount_value'] ?? 0,
                            'discount_type' => $variantData['discount_type'] ?? 'fixed',
                            'tax_value' => $variantData['tax_value'] ?? 0,
                            'tax_type' => $variantData['tax_type'] ?? 'percent',
                            'cost_price' => $variantData['cost_price'] ?? 0,
                            'hpp' => $variantData['hpp'] ?? ($variantData['cost_price'] ?? 0),
                            'is_active' => true,
                        ]);
                    } else {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $variantData['sku'] ?? null,
                            'barcode' => $variantData['barcode'] ?? null,
                            'name' => $variantData['name'],
                            'price' => $variantData['price'] ?? 0,
                            'discount_value' => $variantData['discount_value'] ?? 0,
                            'discount_type' => $variantData['discount_type'] ?? 'fixed',
                            'tax_value' => $variantData['tax_value'] ?? 0,
                            'tax_type' => $variantData['tax_type'] ?? 'percent',
                            'cost_price' => $variantData['cost_price'] ?? 0,
                            'hpp' => $variantData['hpp'] ?? ($variantData['cost_price'] ?? 0),
                            'is_active' => true,
                        ]);
                    }

                    // Sync Tier Prices
                    ProductTierPrice::where('product_variant_id', $variant->id)->delete();
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

                    // Sync Branch Prices
                    ProductBranchPrice::where('product_variant_id', $variant->id)->delete();
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


                }
            } else {
                // Single default variant or bundle/service
                // Delete all variants except the first one
                $variants = $product->variants;
                $variant = $variants->first();

                // Delete other variants safely if any
                if ($variants->count() > 1) {
                    $variantsToDelete = $product->variants()->where('id', '!=', $variant->id)->get();
                    foreach ($variantsToDelete as $variantToDelete) {
                        try {
                            $variantToDelete->delete();
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() == '23000') {
                                $variantToDelete->update(['is_active' => false]);
                            } else {
                                throw $e;
                            }
                        }
                    }
                }

                if (!$variant) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $data['sku'] ?? null,
                        'barcode' => $data['barcode'] ?? null,
                        'name' => null,
                        'price' => $data['price'] ?? 0,
                        'discount_value' => $data['discount_value'] ?? 0,
                        'discount_type' => $data['discount_type'] ?? 'fixed',
                        'tax_value' => $data['tax_value'] ?? 0,
                        'tax_type' => $data['tax_type'] ?? 'percent',
                        'cost_price' => $data['cost_price'] ?? 0,
                        'hpp' => $data['hpp'] ?? ($data['cost_price'] ?? 0),
                        'is_active' => true,
                    ]);
                } else {
                    $variant->update([
                        'sku' => $data['sku'] ?? null,
                        'barcode' => $data['barcode'] ?? null,
                        'name' => null, // ensure it's default
                        'price' => $data['price'] ?? 0,
                        'discount_value' => $data['discount_value'] ?? 0,
                        'discount_type' => $data['discount_type'] ?? 'fixed',
                        'tax_value' => $data['tax_value'] ?? 0,
                        'tax_type' => $data['tax_type'] ?? 'percent',
                        'cost_price' => $data['cost_price'] ?? 0,
                        'hpp' => $data['hpp'] ?? ($data['cost_price'] ?? 0),
                        'is_active' => true,
                    ]);
                }

                // Sync Tier Prices
                ProductTierPrice::where('product_variant_id', $variant->id)->delete();
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

                // Sync Branch Prices
                ProductBranchPrice::where('product_variant_id', $variant->id)->delete();
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



                // Sync Bundle Components (only if bundle)
                ProductBundle::where('parent_variant_id', $variant->id)->delete();
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
