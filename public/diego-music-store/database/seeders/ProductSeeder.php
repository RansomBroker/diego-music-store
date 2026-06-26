<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure we have Branches
        $branchPusat = Branch::where('name', 'Cabang Pusat (Back Office)')->first();
        if (!$branchPusat) {
            $branchPusat = Branch::create([
                'name' => 'Cabang Pusat (Back Office)',
                'address' => 'Jl. Bypass Ngurah Rai No. 123, Denpasar, Bali',
                'phone' => '081234567890',
                'is_active' => true,
            ]);
        }

        $branchKuta = Branch::where('name', 'Cabang Kuta')->first();
        if (!$branchKuta) {
            $branchKuta = Branch::create([
                'name' => 'Cabang Kuta',
                'address' => 'Jl. Raya Kuta No. 45, Badung, Bali',
                'phone' => '081234567891',
                'is_active' => true,
            ]);
        }

        // 2. Ensure we have Pricing Tiers
        $tierRetail = PricingTier::where('name', 'Umum / Retail')->first();
        if (!$tierRetail) {
            $tierRetail = PricingTier::create([
                'name' => 'Umum / Retail',
                'description' => 'Harga retail standar',
            ]);
        }

        $tierGrosir = PricingTier::where('name', 'Reseller / Grosir')->first();
        if (!$tierGrosir) {
            $tierGrosir = PricingTier::create([
                'name' => 'Reseller / Grosir',
                'description' => 'Harga grosir untuk reseller',
            ]);
        }

        // 3. Create 1 Physical Product with 2 Variants
        $physicalVariants = [];
        if (!Product::where('name', 'Gitar Akustik Yamaha FS800')->exists()) {
            $physicalProduct = Product::create([
                'name' => 'Gitar Akustik Yamaha FS800',
                'type' => 'physical',
                'description' => 'Gitar akustik berkualitas tinggi dengan solid spruce top.',
                'image_path' => null,
                'is_active' => true,
            ]);

            $variantsData = [
                [
                    'name' => 'Natural',
                    'sku' => 'SKU-YMHFSNAT',
                    'barcode' => '8991234567891',
                    'price' => 3200000,
                    'cost_price' => 2000000,
                    'hpp' => 2100000, // Cost + estimated shipping (100k)
                ],
                [
                    'name' => 'Sunburst',
                    'sku' => 'SKU-YMHFSBST',
                    'barcode' => '8991234567892',
                    'price' => 3300000,
                    'cost_price' => 2100000,
                    'hpp' => 2200000, // Cost + estimated shipping (100k)
                ]
            ];

            foreach ($variantsData as $vd) {
                $variant = ProductVariant::create([
                    'product_id' => $physicalProduct->id,
                    'sku' => $vd['sku'],
                    'barcode' => $vd['barcode'],
                    'name' => $vd['name'],
                    'price' => $vd['price'],
                    'cost_price' => $vd['cost_price'],
                    'hpp' => $vd['hpp'],
                    'is_active' => true,
                ]);

                $physicalVariants[] = $variant;

                // Seed tier prices
                ProductTierPrice::create([
                    'product_variant_id' => $variant->id,
                    'pricing_tier_id' => $tierGrosir->id,
                    'price' => $vd['price'] - 200000, // discount 200k for grosir
                ]);

                // Seed branch stocks
                ProductBranchStock::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $branchPusat->id,
                    'stock' => 10,
                ]);
                ProductBranchStock::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $branchKuta->id,
                    'stock' => 5,
                ]);
            }
        } else {
            // Retrieve existing variants for the bundle relation if they already exist
            $physicalProduct = Product::where('name', 'Gitar Akustik Yamaha FS800')->first();
            $physicalVariants = $physicalProduct->variants()->get()->all();
        }

        // 4. Create 1 Service Product
        $serviceVariant = null;
        if (!Product::where('name', 'Setup & Stem Gitar')->exists()) {
            $serviceProduct = Product::create([
                'name' => 'Setup & Stem Gitar',
                'type' => 'service',
                'description' => 'Jasa kalibrasi truss rod, saddle, nut, dan tuning senar.',
                'image_path' => null,
                'is_active' => true,
            ]);

            $serviceVariant = ProductVariant::create([
                'product_id' => $serviceProduct->id,
                'sku' => 'SKU-JSASTEMP',
                'barcode' => '8992345678901',
                'name' => null, // default variant for service
                'price' => 150000,
                'cost_price' => 0,
                'hpp' => 0,
                'is_active' => true,
            ]);

            // Seed tier prices for service
            ProductTierPrice::create([
                'product_variant_id' => $serviceVariant->id,
                'pricing_tier_id' => $tierGrosir->id,
                'price' => 120000,
            ]);
        } else {
            $serviceProduct = Product::where('name', 'Setup & Stem Gitar')->first();
            $serviceVariant = $serviceProduct->variants()->first();
        }

        // 5. Create 1 Bundle Product (1x Natural Guitar + 1x Setup Service)
        if (!Product::where('name', 'Paket Siap Konser Yamaha FS800')->exists() && count($physicalVariants) > 0 && $serviceVariant) {
            $bundleProduct = Product::create([
                'name' => 'Paket Siap Konser Yamaha FS800',
                'type' => 'bundle',
                'description' => 'Paket bundling Gitar Yamaha FS800 Natural + Jasa Setup profesional.',
                'image_path' => null,
                'is_active' => true,
            ]);

            $bundleVariant = ProductVariant::create([
                'product_id' => $bundleProduct->id,
                'sku' => 'SKU-BNDYMHFS',
                'barcode' => '8993456789012',
                'name' => null, // default variant for bundle
                'price' => 3250000, // Bundled discounted price (Guitar 3.2M + Setup 150K -> Bundle 3.25M)
                'cost_price' => 2000000,
                'hpp' => 2100000,
                'is_active' => true,
            ]);

            // Seed bundle components
            ProductBundle::create([
                'parent_variant_id' => $bundleVariant->id,
                'child_variant_id' => $physicalVariants[0]->id, // Yamaha FS800 Natural
                'quantity' => 1,
            ]);

            ProductBundle::create([
                'parent_variant_id' => $bundleVariant->id,
                'child_variant_id' => $serviceVariant->id, // Setup & Stem
                'quantity' => 1,
            ]);

            // Seed tier prices for bundle
            ProductTierPrice::create([
                'product_variant_id' => $bundleVariant->id,
                'pricing_tier_id' => $tierGrosir->id,
                'price' => 3100000,
            ]);
        }
    }
}
