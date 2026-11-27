<?php

namespace App\Actions\Product;

use App\Models\PricingTier;
use App\Models\ProductTierPrice;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateProductVariantTierPrices
{
    /**
     * Perbarui harga jual Pricing Tiers untuk satu varian produk.
     * Pricing tier dengan flag censor_prices = true secara ketat diabaikan/tidak dapat diubah dari aksi ini.
     *
     * @param  int  $productVariantId
     * @param  array<int, array{pricing_tier_id?: int|string, new_price?: int|float|string}>  $tierPrices
     */
    public function execute(int $productVariantId, array $tierPrices): ProductVariant
    {
        $variant = ProductVariant::find($productVariantId);
        if (! $variant) {
            throw new InvalidArgumentException('Varian produk tidak ditemukan.');
        }

        $allowedTierIds = PricingTier::query()
            ->where('censor_prices', false)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowedLookup = array_flip($allowedTierIds);

        return DB::transaction(function () use ($variant, $tierPrices, $allowedLookup): ProductVariant {
            foreach ($tierPrices as $row) {
                $tierId = isset($row['pricing_tier_id']) ? (int) $row['pricing_tier_id'] : 0;
                if ($tierId <= 0 || ! isset($allowedLookup[$tierId])) {
                    continue;
                }

                $newPrice = max(0, (int) round((float) ($row['new_price'] ?? 0)));

                ProductTierPrice::updateOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                        'pricing_tier_id' => $tierId,
                    ],
                    [
                        'price' => $newPrice,
                    ]
                );
            }

            return $variant->fresh(['tierPrices']);
        });
    }
}

