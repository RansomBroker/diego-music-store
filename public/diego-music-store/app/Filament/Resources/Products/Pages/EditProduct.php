<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record;
        
        $variantsCount = $product->variants()->count();
        $firstVariant = $product->variants()->first();
        
        if ($product->isPhysical() && $variantsCount > 1) {
            $data['has_variants'] = true;
            $data['variants'] = [];
            
            foreach ($product->variants as $variant) {
                $data['variants'][] = [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'price' => $variant->price,
                    'cost_price' => $variant->cost_price,
                    'hpp' => $variant->hpp,
                    'tier_prices' => $variant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray(),
                    'branch_prices' => $variant->branchPrices()->pluck('price', 'branch_id')->toArray(),
                    'branch_stocks' => $variant->branchStocks()->pluck('stock', 'branch_id')->toArray(),
                ];
            }
        } else {
            $data['has_variants'] = false;
            if ($firstVariant) {
                $data['sku'] = $firstVariant->sku;
                $data['barcode'] = $firstVariant->barcode;
                $data['price'] = $firstVariant->price;
                $data['cost_price'] = $firstVariant->cost_price;
                $data['hpp'] = $firstVariant->hpp;
                
                $data['tier_prices'] = $firstVariant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray();
                $data['branch_prices'] = $firstVariant->branchPrices()->pluck('price', 'branch_id')->toArray();
                $data['branch_stocks'] = $firstVariant->branchStocks()->pluck('stock', 'branch_id')->toArray();
                
                if ($product->isBundle()) {
                    $data['bundle_items'] = [];
                    foreach ($firstVariant->bundleItems as $item) {
                        $data['bundle_items'][] = [
                            'child_variant_id' => $item->child_variant_id,
                            'quantity' => $item->quantity,
                        ];
                    }
                }
            }
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            /** @var Product $record */
            $record->update([
                'name' => $data['name'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'image_path' => $data['image_path'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $hasVariants = filter_var($data['has_variants'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // If physical with variants enabled
            if ($record->isPhysical() && $hasVariants && !empty($data['variants'])) {
                $formVariantIds = array_filter(array_column($data['variants'], 'id'));

                // Delete variants not in the form
                $record->variants()->whereNotIn('id', $formVariantIds)->delete();

                foreach ($data['variants'] as $variantData) {
                    if (!empty($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update([
                            'sku' => $variantData['sku'] ?? null,
                            'barcode' => $variantData['barcode'] ?? null,
                            'name' => $variantData['name'],
                            'price' => $variantData['price'] ?? 0,
                            'cost_price' => $variantData['cost_price'] ?? 0,
                            'hpp' => $variantData['hpp'] ?? ($variantData['cost_price'] ?? 0),
                        ]);
                    } else {
                        $variant = ProductVariant::create([
                            'product_id' => $record->id,
                            'sku' => $variantData['sku'] ?? null,
                            'barcode' => $variantData['barcode'] ?? null,
                            'name' => $variantData['name'],
                            'price' => $variantData['price'] ?? 0,
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

                    // Sync Branch Stocks
                    ProductBranchStock::where('product_variant_id', $variant->id)->delete();
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
                // Single default variant or bundle/service
                // Delete all variants except the first one
                $variants = $record->variants;
                $variant = $variants->first();

                // Delete other variants if any
                if ($variants->count() > 1) {
                    $record->variants()->where('id', '!=', $variant->id)->delete();
                }

                if (!$variant) {
                    $variant = ProductVariant::create([
                        'product_id' => $record->id,
                        'sku' => $data['sku'] ?? null,
                        'barcode' => $data['barcode'] ?? null,
                        'name' => null,
                        'price' => $data['price'] ?? 0,
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
                        'cost_price' => $data['cost_price'] ?? 0,
                        'hpp' => $data['hpp'] ?? ($data['cost_price'] ?? 0),
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

                // Sync Branch Stocks (only if physical)
                ProductBranchStock::where('product_variant_id', $variant->id)->delete();
                if ($record->isPhysical() && !empty($data['branch_stocks'])) {
                    foreach ($data['branch_stocks'] as $branchId => $stock) {
                        ProductBranchStock::create([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                            'stock' => $stock ?? 0,
                        ]);
                    }
                }

                // Sync Bundle Components (only if bundle)
                ProductBundle::where('parent_variant_id', $variant->id)->delete();
                if ($record->isBundle() && !empty($data['bundle_items'])) {
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

            return $record;
        });
    }
}
