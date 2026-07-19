<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
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

        $this->user = User::factory()->create();
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
        \App\Models\Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        \App\Models\Account::firstOrCreate(['code' => '4-1100'], ['name' => 'Retur & Potongan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);

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
}
