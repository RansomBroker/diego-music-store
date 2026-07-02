<?php

namespace App\Helpers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Str;

class ProductHelper
{
    public static function generateUniqueSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(8));
        } while (ProductVariant::where('sku', $sku)->exists());
        
        return $sku;
    }

    public static function generateUniqueBarcode(): string
    {
        do {
            $barcode = '899' . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$barcode[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            $barcode .= $checkDigit;
        } while (ProductVariant::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Retrieve stock card data for a product.
     *
     * @param  Product  $product
     * @return array<string, mixed>
     */
    public static function getStockCardData(Product $product): array
    {
        $branches = Branch::where('is_active', true)->get();
        
        $bundleItems = collect();
        $childMovements = [];
        $physicalMovements = [];

        if ($product->isBundle()) {
            $defaultVariant = $product->variants->first();
            if ($defaultVariant) {
                $bundleItems = $defaultVariant->bundleItems()
                    ->with(['childVariant.product', 'childVariant.branchStocks'])
                    ->get();

                foreach ($bundleItems as $item) {
                    $childVariant = $item->childVariant;
                    if ($childVariant) {
                        $childMovements[$childVariant->id] = StockMovement::where('product_variant_id', $childVariant->id)
                            ->with('branch')
                            ->orderBy('created_at', 'desc')
                            ->take(50)
                            ->get();
                    }
                }
            }
        } else {
            foreach ($product->variants as $variant) {
                $physicalMovements[$variant->id] = StockMovement::where('product_variant_id', $variant->id)
                    ->with('branch')
                    ->orderBy('created_at', 'desc')
                    ->take(50)
                    ->get();
            }
        }

        return [
            'branches' => $branches,
            'bundleItems' => $bundleItems,
            'childMovements' => $childMovements,
            'physicalMovements' => $physicalMovements,
        ];
    }
}
