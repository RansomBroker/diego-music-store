<?php

namespace Tests\Feature;

use App\Helpers\PosNavbarHelper;
use App\Livewire\POS;
use App\Livewire\Pos\ProductSearchModal;
use App\Livewire\Pos\SalesQueueWidget;
use App\Models\Account;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\PosHeldTransaction;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use App\Models\SaleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class POSQueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Branch $otherBranch;
    protected PricingTier $retailTier;

    protected function setUp(): void
    {
        parent::setUp();

        Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1200'], ['name' => 'Piutang Dagang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1110'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1300'], ['name' => 'Persediaan Barang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '4-1000'], ['name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);
        Account::firstOrCreate(['code' => '5-1000'], ['name' => 'Harga Pokok Penjualan', 'classification' => 'Expense', 'is_active' => true]);

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('karyawan');
        $this->actingAs($this->user);

        $this->branch = Branch::create(['name' => 'Cabang Utama', 'is_active' => true]);
        $this->otherBranch = Branch::create(['name' => 'Cabang Lain', 'is_active' => true]);
        $this->user->branches()->attach($this->branch->id);
        session(['pos_active_branch_id' => $this->branch->id]);

        $this->retailTier = PricingTier::create([
            'name' => 'Umum / Retail',
            'description' => 'Retail',
            'price_follows_hpp' => false,
        ]);

        SaleCategory::firstOrCreate(['name' => 'Store'], ['prefix' => 'STR', 'start_alphabet' => 'A', 'end_alphabet' => 'Z', 'digit_length' => 4]);

        CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    /** @test */
    public function it_eager_loads_bundle_child_products_and_scopes_branch_stocks_in_pos_search(): void
    {
        $childProduct = Product::create(['name' => 'Senar Gitar', 'type' => 'physical', 'category' => 'SENAR', 'is_active' => true]);
        $childVariant = ProductVariant::create([
            'product_id' => $childProduct->id,
            'sku' => 'SNR-01',
            'name' => 'Default',
            'price' => 50000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $childVariant->id, 'branch_id' => $this->branch->id, 'stock' => 10]);
        ProductBranchStock::create(['product_variant_id' => $childVariant->id, 'branch_id' => $this->otherBranch->id, 'stock' => 99]);

        $bundleProduct = Product::create(['name' => 'Paket Pemula', 'type' => 'bundle', 'category' => 'PAKET', 'is_active' => true]);
        $bundleVariant = ProductVariant::create([
            'product_id' => $bundleProduct->id,
            'sku' => 'PKT-01',
            'name' => 'Set',
            'price' => 450000,
            'is_active' => true,
        ]);
        ProductBundle::create([
            'parent_variant_id' => $bundleVariant->id,
            'child_variant_id' => $childVariant->id,
            'quantity' => 2,
        ]);

        $component = Livewire::test(POS::class)->set('showProductSearchModal', true);
        $products = $component->instance()->products;

        $loadedBundle = $products->firstWhere('id', $bundleVariant->id);
        $this->assertNotNull($loadedBundle);
        $this->assertTrue($loadedBundle->bundleItems->first()->childVariant->relationLoaded('product'));

        // Accessing stockForBranch must fire ZERO queries
        DB::flushQueryLog();
        DB::enableQueryLog();
        $stock = $loadedBundle->stockForBranch($this->branch->id);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertSame(5, $stock);
        $this->assertCount(0, $queries);
    }

    /** @test */
    public function it_skips_cart_variant_query_when_tier_prices_are_empty_and_loaded(): void
    {
        $product = Product::create(['name' => 'Pick Gitar', 'type' => 'physical', 'category' => 'AKSESORIS', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PCK-01',
            'barcode' => 'BC-PCK-01',
            'name' => 'Medium',
            'price' => 10000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $variant->id, 'branch_id' => $this->branch->id, 'stock' => 20]);

        $component = Livewire::test(POS::class);

        // Scan barcode should only query product_variants once (not twice)
        DB::flushQueryLog();
        DB::enableQueryLog();
        $component->call('scanBarcode', 'BC-PCK-01');
        $scanQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'from `product_variants`') || str_contains($q['query'], 'from "product_variants"'))
            ->count();
        DB::disableQueryLog();

        $this->assertSame(1, $scanQueries);

        // Accessing cartVariants after item with empty tier_prices is in cart must execute ZERO queries
        DB::flushQueryLog();
        DB::enableQueryLog();
        $cartVariants = $component->instance()->cartVariants;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertTrue($cartVariants->isEmpty());
        $this->assertCount(0, $queries);
    }

    /** @test */
    public function it_fetches_sales_queue_widget_in_a_single_queue_query(): void
    {
        $salesUser = User::factory()->create(['name' => 'Sales Satu', 'is_active' => true]);
        $salesUser->assignRole('sales');
        $salesUser->branches()->attach($this->branch->id);

        $widget = Livewire::test(SalesQueueWidget::class, ['branchId' => $this->branch->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $widget->call('$refresh');
        $queueTableQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'sales_queue_members'))
            ->count();
        DB::disableQueryLog();

        $this->assertSame(1, $queueTableQueries);
    }

    /** @test */
    public function it_renders_sales_queue_widget_with_instant_alpine_tabs_and_responsive_layout(): void
    {
        $salesUser = User::factory()->create(['name' => 'Sales Responsif', 'is_active' => true]);
        $salesUser->assignRole('sales');
        $salesUser->branches()->attach($this->branch->id);

        Livewire::test(SalesQueueWidget::class, ['branchId' => $this->branch->id])
            ->assertSee("openTab('queue')", false)
            ->assertSee("openTab('on_duty')", false)
            ->assertSee('wire:loading.attr="disabled"', false)
            ->assertSee('max-w-[calc(100vw-1.5rem)]', false)
            ->assertSee('grid-cols-2 sm:flex', false)
            ->assertDontSee('wire:click="openWithTab', false);
    }

    /** @test */
    public function it_avoids_duplicate_off_day_queries_in_pos_navbar_helper(): void
    {
        $employee = Employee::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'nik' => 'EMP-001',
            'name' => $this->user->name,
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'branch_id' => $this->branch->id,
            'date' => now()->toDateString(),
            'status' => 'hadir',
            'clock_in' => now(),
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $context = PosNavbarHelper::getContext(null, '/pos/front-office');
        $offDayQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'employee_attendances') && in_array('off_day', $q['bindings'] ?? [], true))
            ->count();
        DB::disableQueryLog();

        $this->assertSame(1, $offDayQueries);
        $this->assertFalse($context['isOverQuota']);
    }

    /** @test */
    public function it_applies_ui_optimizations_on_pos_page_and_cart_rows(): void
    {
        $product = Product::create(['name' => 'Gitar Listrik', 'type' => 'physical', 'category' => 'GITAR', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-01',
            'name' => 'Sunburst',
            'price' => 1500000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $variant->id, 'branch_id' => $this->branch->id, 'stock' => 5]);

        // Full page /pos should render navbar in layout, self-host Phosphor icons, skip Leaflet CDN, and use relaxed polling
        $response = $this->get('/pos');
        $response->assertStatus(200);
        $response->assertSee('Point of Sale');
        $response->assertDontSee('leaflet@1.9.4');
        $response->assertDontSee('unpkg.com/@phosphor-icons/web');
        $response->assertSee('wire:poll.30s.visible', false);
        $response->assertSee('wire:poll.60s.visible', false);
        $response->assertDontSee('animate-ping');

        // POS component alone (as rendered on Livewire AJAX updates) must NOT re-render the navbar header
        Livewire::test(POS::class)
            ->call('addToCart', $variant->id)
            ->assertDontSee('<header', false)
            ->assertSee('saveNotes()', false)
            ->assertSee('saveDiscount()', false)
            ->assertSee('saveTax()', false);
    }

    /** @test */
    public function it_runs_product_search_modal_independently_with_numbered_pagination(): void
    {
        $p1 = Product::create(['name' => 'Fender Stratocaster', 'type' => 'physical', 'category' => 'GITAR ELECTRIC', 'is_active' => true]);
        $v1 = ProductVariant::create([
            'product_id' => $p1->id,
            'sku' => 'FND-01',
            'name' => 'Sunburst',
            'price' => 12000000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $v1->id, 'branch_id' => $this->branch->id, 'stock' => 4]);

        $p2 = Product::create(['name' => 'Senar Elixir 09', 'type' => 'physical', 'category' => 'SENAR GITAR', 'is_active' => true]);
        $v2 = ProductVariant::create([
            'product_id' => $p2->id,
            'sku' => 'ELX-09',
            'name' => 'Light',
            'price' => 250000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $v2->id, 'branch_id' => $this->branch->id, 'stock' => 20]);

        $modal = Livewire::test(ProductSearchModal::class, ['branchId' => $this->branch->id]);

        $modal->call('openModal', $this->retailTier->id);
        $products = $modal->instance()->products;

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $products);
        $this->assertSame(2, $products->total());
        $this->assertSame(1, $products->lastPage());
        $this->assertCount(2, $products);

        // Search filter
        $modal->set('search', 'Fender');
        $filtered = $modal->instance()->products;
        $this->assertCount(1, $filtered);
        $this->assertSame($v1->id, $filtered->first()->id);

        // Category filter
        $modal->set('search', '')->call('setCategory', 'SENAR GITAR');
        $byCat = $modal->instance()->products;
        $this->assertCount(1, $byCat);
        $this->assertSame($v2->id, $byCat->first()->id);

        // Closing clears search and returns empty collection without querying
        $modal->call('closeModal')
            ->assertSet('show', false)
            ->assertSet('search', '');
        $this->assertTrue($modal->instance()->products->isEmpty());
    }

    /** @test */
    public function it_syncs_cart_quantities_and_category_counts_when_adding_from_modal(): void
    {
        $product = Product::create(['name' => 'Cajon Akustik', 'type' => 'physical', 'category' => 'PERKUSI', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CJN-01',
            'name' => 'Natural',
            'price' => 850000,
            'is_active' => true,
        ]);
        ProductBranchStock::create(['product_variant_id' => $variant->id, 'branch_id' => $this->branch->id, 'stock' => 10]);

        Livewire::test(POS::class)
            ->call('handleAddToCartFromModal', $variant->id)
            ->assertSet("cart.{$variant->id}.qty", 1)
            ->assertDispatched('pos-cart-synced', quantities: [(string) $variant->id => 1], categoryCounts: ['Semua' => 1, 'PERKUSI' => 1])
            ->call('handleSetCartQtyFromModal', $variant->id, 3)
            ->assertSet("cart.{$variant->id}.qty", 3)
            ->assertDispatched('pos-cart-synced', quantities: [(string) $variant->id => 3], categoryCounts: ['Semua' => 3, 'PERKUSI' => 3]);
    }

    /** @test */
    public function it_caches_and_invalidates_held_transactions_count(): void
    {
        $cacheKey = PosHeldTransaction::countCacheKey($this->user->id, $this->branch->id);
        cache()->forget($cacheKey);

        $component = Livewire::test(POS::class);
        $this->assertSame(0, $component->instance()->heldTransactionsCount);
        $this->assertSame(0, cache()->get($cacheKey));

        $held = PosHeldTransaction::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'customer_name' => 'Walk-in',
            'cart_data' => [1 => ['qty' => 1, 'price' => 10000]],
        ]);

        // Model saved observer invalidates Laravel cache; a fresh request sees 1 and caches it
        $this->assertFalse(cache()->has($cacheKey));
        $freshAfterCreate = Livewire::test(POS::class);
        $this->assertSame(1, $freshAfterCreate->instance()->heldTransactionsCount);
        $this->assertSame(1, cache()->get($cacheKey));

        // Model deleted observer invalidates Laravel cache; a fresh request sees 0
        $held->delete();
        $this->assertFalse(cache()->has($cacheKey));
        $freshAfterDelete = Livewire::test(POS::class);
        $this->assertSame(0, $freshAfterDelete->instance()->heldTransactionsCount);
    }

    /** @test */
    public function it_supports_controller_based_pos_web_routes(): void
    {
        $this->get('/pos/reports')
            ->assertRedirect(route('pos.reports.sales'));

        $this->get('/admin/users?tab=1')
            ->assertRedirect('/backoffice/users?tab=1');

        $this->post('/pos/logout')
            ->assertRedirect(route('pos.login'));
    }
}
