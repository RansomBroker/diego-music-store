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
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PosPerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected PricingTier $retailTier;
    protected PricingTier $grosirTier;

    protected function setUp(): void
    {
        parent::setUp();

        Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1200'], ['name' => 'Piutang Dagang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1110'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1300'], ['name' => 'Persediaan Barang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '4-1000'], ['name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);
        Account::firstOrCreate(['code' => '5-1000'], ['name' => 'Harga Pokok Penjualan', 'classification' => 'Expense', 'is_active' => true]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);

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

        CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    /** @test */
    public function it_does_not_fire_queries_for_stock_and_tier_price_when_relations_are_eager_loaded(): void
    {
        // Create 10 products with variants, tier prices, and stocks
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'name' => "Product {$i}",
                'type' => 'physical',
                'category' => 'GITAR',
                'is_active' => true,
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => "SKU-TEST-{$i}",
                'name' => "Variant {$i}",
                'price' => 100000 * $i,
                'cost_price' => 50000 * $i,
                'hpp' => 50000 * $i,
                'is_active' => true,
            ]);

            ProductTierPrice::create([
                'product_variant_id' => $variant->id,
                'pricing_tier_id' => $this->grosirTier->id,
                'price' => 90000 * $i,
            ]);

            ProductBranchStock::create([
                'product_variant_id' => $variant->id,
                'branch_id' => $this->branch->id,
                'stock' => 10 + $i,
                'hpp' => 50000 * $i,
            ]);
        }

        // Eager load all variants with relations
        $variants = ProductVariant::with(['product', 'branchStocks', 'tierPrices'])->get();
        $this->assertCount(10, $variants);

        // Start listening to DB queries
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Loop through all variants and access stock and tier prices
        foreach ($variants as $v) {
            $stock = $v->stockForBranch($this->branch->id);
            $this->assertGreaterThan(0, $stock);

            $retailPrice = $v->priceForTier($this->retailTier->id);
            $this->assertGreaterThan(0, $retailPrice);

            $grosirPrice = $v->priceForTier($this->grosirTier->id);
            $this->assertGreaterThan(0, $grosirPrice);
        }

        // Verify that ZERO SQL queries were executed during the loop (no N+1 problem)
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(0, $queries, 'stockForBranch and priceForTier executed unexpected database queries despite eager loading!');
    }

    /** @test */
    public function it_eager_loads_relations_in_pos_component(): void
    {
        $product = Product::create([
            'name' => 'Yamaha Pacifica',
            'type' => 'physical',
            'category' => 'GITAR ELECTRIC',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-PAC-01',
            'name' => 'Black',
            'price' => 2500000,
            'cost_price' => 1800000,
            'hpp' => 1800000,
            'is_active' => true,
        ]);

        ProductTierPrice::create([
            'product_variant_id' => $variant->id,
            'pricing_tier_id' => $this->grosirTier->id,
            'price' => 2300000,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
            'hpp' => 1800000,
        ]);

        $component = Livewire::test(\App\Livewire\POS::class)
            ->set('showProductSearchModal', true);

        // Access products computed property
        $products = $component->instance()->products;
        $this->assertTrue($products->isNotEmpty());

        $firstVariant = $products->first();
        $this->assertTrue($firstVariant->relationLoaded('product'), 'Product relationship was not eager-loaded.');
        $this->assertTrue($firstVariant->relationLoaded('branchStocks'), 'branchStocks relationship was not eager-loaded.');
        $this->assertTrue($firstVariant->relationLoaded('tierPrices'), 'tierPrices relationship was not eager-loaded.');
    }
}
