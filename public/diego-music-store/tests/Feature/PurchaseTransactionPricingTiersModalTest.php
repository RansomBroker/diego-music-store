<?php

namespace Tests\Feature;

use App\Actions\Product\UpdateProductVariantTierPrices;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductTierPrice;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTransactionPricingTiersModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_non_censored_pricing_tiers_and_ignores_censored_tiers(): void
    {
        $product = Product::create([
            'name' => 'Gitar Elektrik Test',
            'category' => 'Gitar',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hitam',
            'sku' => 'GTR-TIER-01',
            'price' => 2000000,
            'cost_price' => 1400000,
            'hpp' => 1400000,
            'is_active' => true,
        ]);

        $retailTier = PricingTier::create([
            'name' => 'Harga Retail',
            'price_follows_hpp' => false,
            'censor_prices' => false,
        ]);

        $resellerTier = PricingTier::create([
            'name' => 'Harga Reseller',
            'price_follows_hpp' => false,
            'censor_prices' => false,
        ]);

        $censoredTier = PricingTier::create([
            'name' => 'Harga Modal Rahasia',
            'price_follows_hpp' => true,
            'censor_prices' => true,
        ]);

        ProductTierPrice::create([
            'product_variant_id' => $variant->id,
            'pricing_tier_id' => $retailTier->id,
            'price' => 2000000,
        ]);

        ProductTierPrice::create([
            'product_variant_id' => $variant->id,
            'pricing_tier_id' => $censoredTier->id,
            'price' => 1400000,
        ]);

        app(UpdateProductVariantTierPrices::class)->execute($variant->id, [
            ['pricing_tier_id' => $retailTier->id, 'new_price' => 2250000],
            ['pricing_tier_id' => $resellerTier->id, 'new_price' => 1850000],
            ['pricing_tier_id' => $censoredTier->id, 'new_price' => 9999999],
        ]);

        $this->assertEquals(2250000, $variant->fresh()->priceForTier($retailTier->id));
        $this->assertEquals(1850000, $variant->fresh()->priceForTier($resellerTier->id));
        // Censored tier must remain untouched
        $this->assertEquals(1400000, $variant->fresh()->priceForTier($censoredTier->id));
    }

    public function test_pricing_tiers_comparison_view_renders_variant_summary(): void
    {
        $product = Product::create([
            'name' => 'Keyboard Digital Test',
            'category' => 'Keyboard',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '61 Key',
            'sku' => 'KBD-TIER-01',
            'price' => 3500000,
            'cost_price' => 2500000,
            'hpp' => 2500000,
            'is_active' => true,
        ]);

        $html = view('backoffice.purchase-transactions.pricing-tiers-comparison', [
            'variantId' => $variant->id,
            'newPrice' => 2700000,
        ])->render();

        $this->assertStringContainsString('KBD-TIER-01', $html);
        $this->assertStringContainsString('Keyboard Digital Test', $html);
        $this->assertStringContainsString('2.500.000', $html);
        $this->assertStringContainsString('2.700.000', $html);
    }
}

