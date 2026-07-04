<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\PricingTier;
use App\Models\ProductTierPrice;
use App\Models\CashSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $retailTier;
    protected $grosirTier;
    protected $variant;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic accounts needed
        Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1200'], ['name' => 'Piutang Dagang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1110'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1300'], ['name' => 'Persediaan Barang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '4-1000'], ['name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);
        Account::firstOrCreate(['code' => '5-1000'], ['name' => 'Harga Pokok Penjualan', 'classification' => 'Expense', 'is_active' => true]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);

        // Create pricing tiers
        $this->retailTier = PricingTier::create([
            'name' => 'Umum / Retail',
            'description' => 'Retail tier price',
            'price_follows_hpp' => false,
        ]);

        $this->grosirTier = PricingTier::create([
            'name' => 'Reseller / Grosir',
            'description' => 'Grosir tier price',
            'price_follows_hpp' => false,
        ]);

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-AK',
            'name' => 'Natural',
            'price' => 2000000, // Base / Retail price
            'cost_price' => 1200000,
            'hpp' => 1250000,
            'is_active' => true,
        ]);

        // Setup grosir tier price override: 1,800,000
        ProductTierPrice::create([
            'product_variant_id' => $this->variant->id,
            'pricing_tier_id' => $this->grosirTier->id,
            'price' => 1800000,
        ]);

        // Seed stock of 5
        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
            'hpp' => 1250000,
        ]);

        // Create active cash session
        CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'actual_cash' => 0,
            'difference' => 0,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    /** @test */
    public function it_loads_default_pricing_tier_on_mount()
    {
        Livewire::test('App\Livewire\POS')
            ->assertSet('selectedPricingTierId', $this->retailTier->id)
            ->assertCount('pricingTiers', 2);
    }

    /** @test */
    public function it_adds_item_to_cart_with_correct_pricing_tier()
    {
        // 1. Test adding with default retail pricing tier
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 2000000);
    }

    /** @test */
    public function it_recalculates_cart_prices_when_pricing_tier_changes()
    {
        // 1. Mount POS, add product (starts at retail tier: 2,000,000)
        // 2. Set pricing tier to grosir (should update to 1,800,000)
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 2000000)
            ->call('setPricingTier', $this->grosirTier->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 1800000);
    }

    /** @test */
    public function it_automatically_selects_customer_pricing_tier_on_select_customer()
    {
        $customer = \App\Models\Customer::create([
            'name' => 'Siti Rahmawati',
            'phone' => '089678123456',
            'pricing_tier_id' => $this->grosirTier->id,
        ]);

        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 2000000)
            ->call('selectCustomer', $customer->id, $customer->name, false)
            ->assertSet('selectedPricingTierId', $this->grosirTier->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 1800000);
    }

    /** @test */
    public function it_calculates_tax_based_on_enable_tax_and_custom_tax_percent()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id) // 2,000,000 subtotal
            ->assertSet('enableTax', true)
            ->assertSet('taxPercent', 11)
            ->assertSet('taxAmount', 220000) // 11% of 2,000,000
            ->assertSet('grandTotal', 2220000) // 2,000,000 + 220,000
            ->set('taxPercent', 10)
            ->assertSet('taxAmount', 200000) // 10% of 2,000,000
            ->assertSet('grandTotal', 2200000)
            ->set('enableTax', false)
            ->assertSet('taxAmount', 0)
            ->assertSet('grandTotal', 2000000);
    }
}
