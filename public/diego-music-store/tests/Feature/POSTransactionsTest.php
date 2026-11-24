<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole($ownerRole);
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);
    }

    /** @test */
    public function it_can_render_the_pos_transactions_page()
    {
        $response = $this->get(route('pos.transactions'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Transaksi');
    }

    /** @test */
    public function it_can_filter_transactions_by_search_query()
    {
        // 1. Create two sales
        $sale1 = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-MATCH-01',
            'invoice_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        $sale2 = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-OTHER-02',
            'invoice_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 200000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 200000,
            'created_by' => $this->user->id,
        ]);

        // 2. Test livewire search filtering
        Livewire::test('App\Livewire\POSTransactions')
            ->set('search', 'MATCH')
            ->assertSee('INV-MATCH-01')
            ->assertDontSee('INV-OTHER-02');
    }

    /** @test */
    public function it_can_show_transaction_details_modal()
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-12345',
            'invoice_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 150000,
            'created_by' => $this->user->id,
        ]);

        Livewire::test('App\Livewire\POSTransactions')
            ->call('showDetails', $sale->id)
            ->assertSet('showDetailsModal', true)
            ->assertSet('selectedSale.id', $sale->id)
            ->assertSee('INV-12345')
            ->call('closeDetails')
            ->assertSet('showDetailsModal', false)
            ->assertSet('selectedSale', null);
    }

    /** @test */
    public function it_can_process_sales_return()
    {
        // 1. Seed accounts
        \App\Models\Account::firstOrCreate(['code' => '111101001'], ['name' => 'KAS', 'classification' => 'Asset', 'is_active' => true]);
        \App\Models\Account::firstOrCreate(['code' => '411201001'], ['name' => 'RETUR PENJUALAN', 'classification' => 'Revenue', 'is_active' => true]);

        // 2. Create active cash session
        \App\Models\CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        // 3. Create product and variant
        $product = \App\Models\Product::create([
            'name' => 'Gitar Fender',
            'type' => 'physical',
            'is_active' => true,
        ]);
        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'FND-ST',
            'name' => 'Sunburst',
            'price' => 5000000,
            'cost_price' => 3000000,
            'hpp' => 3000000,
            'is_active' => true,
        ]);
        \App\Models\ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'hpp' => 3000000,
        ]);

        // 4. Create Sale
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-RET-01',
            'invoice_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 5000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 5000000,
            'created_by' => $this->user->id,
        ]);

        $saleItem = \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 5000000,
            'discount_amount' => 0,
            'total_price' => 5000000,
        ]);

        // 5. Test Livewire Retur Penjualan Flow
        Livewire::test('App\Livewire\POSTransactions')
            ->call('startReturn', $sale->id)
            ->assertSet('showReturnModal', true)
            ->assertSet('returnSale.id', $sale->id)
            // Set return quantity for the item
            ->set('returnItems.' . $saleItem->id . '.qty', 1)
            ->set('returnReason', 'Barang cacat produksi')
            ->call('processReturn')
            ->assertSet('showReturnModal', false);

        // 6. Verify Return records exist
        $this->assertDatabaseHas('sales_returns', [
            'sale_id' => $sale->id,
            'total_refund' => 5000000,
            'reason' => 'Barang cacat produksi',
        ]);

        $this->assertDatabaseHas('sales_return_items', [
            'sale_item_id' => $saleItem->id,
            'quantity' => 1,
            'refund_amount' => 5000000,
        ]);

        // Verify stock returned (10 + 1 = 11)
        $this->assertEquals(11, \App\Models\ProductBranchStock::where('product_variant_id', $variant->id)->where('branch_id', $this->branch->id)->first()->stock);
    }

    /** @test */
    public function it_displays_cash_session_opener_as_kasir_and_sales_rep_in_separate_sales_column()
    {
        $sessionOpener = User::factory()->create(['name' => 'Kasir Pembuka Sesi']);
        $salesPerson = User::factory()->create(['name' => 'Sales Penjual']);

        $session = \App\Models\CashSession::create([
            'user_id' => $sessionOpener->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 250000,
            'expected_cash' => 250000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $salesPerson->id,
            'invoice_number' => 'INV-KASIR-SALES-01',
            'invoice_date' => now(),
            'payment_method' => 'Tunai',
            'status' => 'completed',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        Livewire::test('App\Livewire\POSTransactions')
            ->assertSee('Kasir')
            ->assertSee('Sales')
            ->assertSeeInOrder(['INV-KASIR-SALES-01', 'Kasir Pembuka Sesi', 'Sales Penjual'])
            ->call('showDetails', $sale->id)
            ->assertSee('Kasir Pembuka Sesi')
            ->assertSee('Sales Penjual');
    }

    /** @test */
    public function it_separates_parent_child_payment_method_filters_and_filters_accurately()
    {
        $cash = PaymentMethod::create(['name' => 'Cash', 'code' => 'cash', 'is_active' => true]);
        $transfer = PaymentMethod::create(['name' => 'Transfer', 'code' => 'transfer', 'is_active' => true]);
        $qris = PaymentMethod::create(['name' => 'QRIS', 'code' => 'qris', 'is_active' => true]);
        $debit = PaymentMethod::create(['name' => 'Debit Card', 'code' => 'debit_card', 'is_active' => true]);

        $transferBca = PaymentMethod::create(['name' => 'Transfer BCA', 'code' => 'transfer-bca', 'parent_id' => $transfer->id, 'is_active' => true]);
        $transferMandiri = PaymentMethod::create(['name' => 'Mandiri', 'code' => 'transfer-mandiri', 'parent_id' => $transfer->id, 'is_active' => true]);
        $qrisBca = PaymentMethod::create(['name' => 'BCA', 'code' => 'qris-bca', 'parent_id' => $qris->id, 'is_active' => true]);
        $debitBca = PaymentMethod::create(['name' => 'BCA', 'code' => 'debit-bca', 'parent_id' => $debit->id, 'is_active' => true]);

        $options = PaymentMethod::getFilterOptions();
        $labels = array_column($options, 'label');

        $this->assertEquals([
            'Cash',
            'Debit Card - BCA',
            'QRIS - BCA',
            'Transfer - BCA',
            'Transfer - Mandiri',
        ], $labels);

        $this->assertNotContains('Transfer', $labels);
        $this->assertNotContains('QRIS', $labels);
        $this->assertNotContains('Debit Card', $labels);
        $this->assertNotContains('BCA', $labels);

        // Relational transactions using sale_payments
        $saleTransferBca = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-TRF-BCA-01',
            'invoice_date' => now(),
            'payment_method' => $transferBca->formatCheckoutLabel(),
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);
        SalePayment::create(['sale_id' => $saleTransferBca->id, 'payment_method_id' => $transferBca->id, 'amount' => 100000]);

        $saleTransferMandiri = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-TRF-MDR-01',
            'invoice_date' => now(),
            'payment_method' => $transferMandiri->formatCheckoutLabel(),
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);
        SalePayment::create(['sale_id' => $saleTransferMandiri->id, 'payment_method_id' => $transferMandiri->id, 'amount' => 100000]);

        $saleQrisBca = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-QRIS-BCA-01',
            'invoice_date' => now(),
            'payment_method' => $qrisBca->formatCheckoutLabel(),
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);
        SalePayment::create(['sale_id' => $saleQrisBca->id, 'payment_method_id' => $qrisBca->id, 'amount' => 100000]);

        $saleDebitBca = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-DEBIT-BCA-01',
            'invoice_date' => now(),
            'payment_method' => $debitBca->formatCheckoutLabel(),
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);
        SalePayment::create(['sale_id' => $saleDebitBca->id, 'payment_method_id' => $debitBca->id, 'amount' => 100000]);

        $saleCash = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-CASH-01',
            'invoice_date' => now(),
            'payment_method' => 'Cash',
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);
        SalePayment::create(['sale_id' => $saleCash->id, 'payment_method_id' => $cash->id, 'amount' => 100000]);

        // Legacy text-only transactions (no sale_payments rows)
        Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-LEG-QRIS-BCA',
            'invoice_date' => now(),
            'payment_method' => 'QRIS (BCA)',
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-LEG-TRF-BCA',
            'invoice_date' => now(),
            'payment_method' => 'Transfer - BCA',
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-LEG-TUNAI',
            'invoice_date' => now(),
            'payment_method' => 'Tunai',
            'status' => 'completed',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        // 1. Selecting Transfer - BCA must ONLY match Transfer BCA transactions (NOT QRIS - BCA or Debit Card - BCA)
        Livewire::test('App\Livewire\POSTransactions')
            ->assertSee('Transfer - BCA')
            ->assertSee('QRIS - BCA')
            ->set('selectedPaymentMethod', [(string) $transferBca->id])
            ->assertSee('INV-TRF-BCA-01')
            ->assertSee('INV-LEG-TRF-BCA')
            ->assertDontSee('INV-TRF-MDR-01')
            ->assertDontSee('INV-QRIS-BCA-01')
            ->assertDontSee('INV-LEG-QRIS-BCA')
            ->assertDontSee('INV-DEBIT-BCA-01')
            ->assertDontSee('INV-CASH-01')
            ->assertDontSee('INV-LEG-TUNAI');

        // 2. Selecting QRIS - BCA must ONLY match QRIS BCA (including legacy 'QRIS (BCA)')
        Livewire::test('App\Livewire\POSTransactions')
            ->set('selectedPaymentMethod', [(string) $qrisBca->id])
            ->assertSee('INV-QRIS-BCA-01')
            ->assertSee('INV-LEG-QRIS-BCA')
            ->assertDontSee('INV-TRF-BCA-01')
            ->assertDontSee('INV-LEG-TRF-BCA')
            ->assertDontSee('INV-DEBIT-BCA-01')
            ->assertDontSee('INV-CASH-01');

        // 3. Multi-select Transfer - BCA + QRIS - BCA shows both sets cleanly
        Livewire::test('App\Livewire\POSTransactions')
            ->set('selectedPaymentMethod', [(string) $transferBca->id, (string) $qrisBca->id])
            ->assertSee('INV-TRF-BCA-01')
            ->assertSee('INV-LEG-TRF-BCA')
            ->assertSee('INV-QRIS-BCA-01')
            ->assertSee('INV-LEG-QRIS-BCA')
            ->assertDontSee('INV-DEBIT-BCA-01')
            ->assertDontSee('INV-CASH-01');

        // 4. Selecting Cash matches relational Cash and legacy 'Tunai'
        Livewire::test('App\Livewire\POSTransactions')
            ->set('selectedPaymentMethod', [(string) $cash->id])
            ->assertSee('INV-CASH-01')
            ->assertSee('INV-LEG-TUNAI')
            ->assertDontSee('INV-TRF-BCA-01')
            ->assertDontSee('INV-QRIS-BCA-01');
    }
}
