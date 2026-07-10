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
            ->set('enableTax', true)
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

    /** @test */
    public function it_calculates_item_discounts_supporting_both_fixed_and_percentage_types()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->call('updateItemDiscountValue', $this->variant->id, 50000)
            ->assertSet('cart.' . $this->variant->id . '.discount_amount', 50000)
            ->assertSet('subtotal', 1950000)
            ->call('updateItemDiscountValue', $this->variant->id, 10)
            ->call('toggleItemDiscountType', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.discount_amount', 200000)
            ->assertSet('subtotal', 1800000)
            ->call('updateQty', $this->variant->id, 1)
            ->assertSet('cart.' . $this->variant->id . '.discount_amount', 400000)
            ->assertSet('subtotal', 3600000);
    }

    /** @test */
    public function it_updates_global_pricing_tier_when_item_tier_changes_and_vice_versa()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('selectedPricingTierId', $this->retailTier->id)
            ->assertSet('cart.' . $this->variant->id . '.pricing_tier_id', $this->retailTier->id)
            
            // 1. Changing an item's pricing tier should set the global tier to 'custom'
            ->call('updateItemPricingTier', $this->variant->id, $this->grosirTier->id)
            ->assertSet('selectedPricingTierId', 'custom')
            ->assertSet('cart.' . $this->variant->id . '.pricing_tier_id', $this->grosirTier->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 1800000)
            
            // 2. Setting the global pricing tier should sync all items in the cart
            ->call('setPricingTier', $this->retailTier->id)
            ->assertSet('selectedPricingTierId', $this->retailTier->id)
            ->assertSet('cart.' . $this->variant->id . '.pricing_tier_id', $this->retailTier->id)
            ->assertSet('cart.' . $this->variant->id . '.price', 2000000);
    }

    /** @test */
    public function it_can_quickly_register_and_select_a_new_customer()
    {
        Livewire::test('App\Livewire\POS')
            ->set('customerSearch', 'Joko Susilo')
            ->call('openCreateCustomerModal')
            ->assertSet('showCreateCustomerModal', true)
            ->assertSet('newCustomerName', 'Joko Susilo')
            ->set('newCustomerPhone', '0855667788')
            ->set('newCustomerPricingTierId', $this->grosirTier->id)
            ->set('newCustomerIsLoyaltyMember', true)
            ->call('createCustomer')
            ->assertSet('showCreateCustomerModal', false)
            ->assertSet('selectedCustomerName', 'Joko Susilo')
            ->assertSet('selectedPricingTierId', $this->grosirTier->id);

        $this->assertDatabaseHas('customers', [
            'name' => 'Joko Susilo',
            'phone' => '0855667788',
            'pricing_tier_id' => $this->grosirTier->id,
            'is_loyalty_member' => true,
        ]);
    }

    /** @test */
    public function it_can_use_loyalty_points_for_discount_and_deducts_them_on_checkout()
    {
        // 1. Create a customer with 50 loyalty points (which is equivalent to 50 * 1000 = Rp 50.000 discount)
        $customer = \App\Models\Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'loyalty_points' => 50,
        ]);

        // 2. Test POS Livewire
        Livewire::test('App\Livewire\POS')
            ->set('enableTax', true)
            ->call('addToCart', $this->variant->id) // Adds 1 variant of retail price 2.000.000
            // Tax: 11% -> 220.000, Total: 2.220.000
            ->assertSet('grandTotal', 2220000)
            // Select customer
            ->call('selectCustomer', $customer->id, $customer->name, false)
            ->assertSet('customerPoints', 50)
            ->assertSet('usePoints', false)
            // Toggle points usage
            ->set('usePoints', true)
            // Point discount should be: 50 * 1000 = Rp 50.000
            ->assertSet('pointDiscountAmount', 50000)
            // New grandTotal: 2.220.000 - 50.000 = 2.170.000
            ->assertSet('grandTotal', 2170000)
            // Perform checkout
            ->call('openPayment')
            ->set('amountCash', 2170000)
            ->call('checkout');

        // 3. Verify points were deducted in DB (50 - 50 = 0 points left)
        $customer->refresh();
        $this->assertEquals(0, $customer->loyalty_points);
    }

    /** @test */
    public function it_returns_default_customers_when_search_query_is_empty()
    {
        // Clear any existing customers
        \App\Models\Customer::query()->delete();

        // Create 2 customers
        \App\Models\Customer::create(['name' => 'Ahmad', 'phone' => '081234']);
        \App\Models\Customer::create(['name' => 'Budi', 'phone' => '085678']);

        Livewire::test('App\Livewire\POS')
            ->set('customerSearch', '')
            ->assertCount('customers', 2);
    }

    /** @test */
    public function it_can_checkout_with_split_payments()
    {
        $product = \App\Models\Product::create([
            'name' => 'Produk Split Test',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SPL-TEST',
            'name' => 'Standard',
            'price' => 100000,
            'cost_price' => 50000,
            'hpp' => 50000,
            'is_active' => true,
        ]);

        \App\Models\ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'hpp' => 50000,
        ]);

        $cart = [
            $variant->id => [
                'variant_id' => $variant->id,
                'name' => $product->name,
                'qty' => 1,
                'price' => 100000,
                'type' => 'physical',
                'emoji' => '🎸',
                'notes' => '',
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'pricing_tier_id' => $this->retailTier->id,
            ]
        ];

        Livewire::test('App\Livewire\POS')
            ->set('selectedBranchId', $this->branch->id)
            ->set('cart', $cart)
            ->assertSet('grandTotal', 100000)
            ->call('openPayment')
            ->set('selectedPaymentMethods', ['cash', 'debit'])
            ->set('amountCash', 40000)
            ->set('amountDebit', 60000)
            ->set('debitRef', 'TRF-123')
            ->call('checkout');

        // Verify sale was created
        $sale = \App\Models\Sale::latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals('Tunai & Debit BCA', $sale->payment_method);
        $this->assertEquals(100000, $sale->grand_total);

        // Verify Journal items for Split Payment
        $journalEntry = \App\Models\JournalEntry::where('reference_id', $sale->id)->first();
        $this->assertNotNull($journalEntry);

        // Kas Utama should be debited 40000
        $kasItem = \App\Models\JournalItem::where('journal_entry_id', $journalEntry->id)
            ->whereHas('account', fn($q) => $q->where('code', '1-1000'))
            ->first();
        $this->assertNotNull($kasItem);
        $this->assertEquals(40000, $kasItem->debit);

        // Bank BCA should be debited 60000
        $bankItem = \App\Models\JournalItem::where('journal_entry_id', $journalEntry->id)
            ->whereHas('account', fn($q) => $q->where('code', '1-1110'))
            ->first();
        $this->assertNotNull($bankItem);
        $this->assertEquals(60000, $bankItem->debit);
    }

    /** @test */
    public function it_can_hold_restore_and_delete_transactions_in_database()
    {
        // 1. Setup cart
        $cart = [
            $this->variant->id => [
                'variant_id' => $this->variant->id,
                'product_id' => $this->variant->product_id,
                'name' => $this->variant->name,
                'sku' => $this->variant->sku,
                'price' => 2000000,
                'qty' => 1,
                'emoji' => '🎹',
                'discount_amount' => 0,
                'discount_type' => 'fixed',
                'pricing_tier_id' => $this->retailTier->id,
            ]
        ];

        // 2. Create customer
        $customer = \App\Models\Customer::create([
            'name' => 'Test Customer',
            'phone' => '0812345678',
        ]);

        // 3. Perform hold
        $component = Livewire::test('App\Livewire\POS')
            ->set('selectedBranchId', $this->branch->id)
            ->set('cart', $cart)
            ->set('selectedCustomerId', $customer->id)
            ->set('selectedCustomerName', $customer->name)
            ->set('discountValue', 10000)
            ->set('discountType', 'fixed')
            ->call('holdTransaction');

        // Verify it was cleared from session state
        $component->assertSet('cart', []);
        $component->assertSet('selectedCustomerId', null);

        // Verify it was stored in database
        $held = \App\Models\PosHeldTransaction::latest()->first();
        $this->assertNotNull($held);
        $this->assertEquals('Test Customer', $held->customer_name);
        $this->assertEquals(10000, $held->discount_value);
        $this->assertCount(1, $held->cart_data);

        // 4. Restore transaction
        Livewire::test('App\Livewire\POS')
            ->set('selectedBranchId', $this->branch->id)
            ->call('restoreHeldTransaction', $held->id)
            ->assertSet('selectedCustomerId', $customer->id)
            ->assertSet('selectedCustomerName', 'Test Customer')
            ->assertSet('discountValue', 10000);

        // Verify it was deleted from database on restore
        $this->assertNull(\App\Models\PosHeldTransaction::find($held->id));

        // 4. Test delete transaction
        $held2 = \App\Models\PosHeldTransaction::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'branch_id' => $this->branch->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'cart_data' => $cart,
            'customer_name' => 'To Be Deleted',
        ]);

        Livewire::test('App\Livewire\POS')
            ->set('selectedBranchId', $this->branch->id)
            ->call('deleteHeldTransaction', $held2->id);

        $this->assertNull(\App\Models\PosHeldTransaction::find($held2->id));
    }
}
