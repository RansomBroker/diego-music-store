<?php

namespace App\Helpers;

use App\Models\ProductVariant;
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
}
