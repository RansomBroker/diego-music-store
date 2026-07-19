<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SupplierPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosSupplierPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;
    protected Account $cashAccount;
    protected PurchaseTransaction $creditPurchase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 2. Create branch and assign user
        $this->branch = Branch::create([
            'name' => 'Cabang Test POS',
            'address' => 'Jl. Test POS',
            'phone' => '021-12345',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);

        // 3. Create Supplier
        $this->supplier = Supplier::create([
            'name' => 'Test Supplier POS',
            'phone' => '08123456789',
            'address' => 'Supplier Address',
            'outstanding_debt' => 0,
        ]);

        // 4. Create Account
        $this->cashAccount = Account::create([
            'code' => '1-1101',
            'name' => 'Kas POS Test',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        // 5. Create credit purchase
        $product = Product::create([
            'name' => 'Piano Test',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PNO-TST',
            'name' => 'Standard Piano',
            'price' => 10000000,
            'cost_price' => 8000000,
            'hpp' => 8000000,
            'is_active' => true,
        ]);

        $this->creditPurchase = PurchaseTransaction::create([
            'transaction_no' => 'PT-2026-0909',
            'transaction_date' => '2026-07-10',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'grand_total' => 8000000,
            'status' => 'posted',
            'posted_at' => now(),
            'due_date' => '2026-08-10',
        ]);

        PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $this->creditPurchase->id,
            'product_variant_id' => $variant->id,
            'qty_received' => 1,
            'price' => 8000000,
            'subtotal' => 8000000,
        ]);

        $this->supplier->increment('outstanding_debt', 8000000);
    }

    /** @test */
    public function it_can_render_the_pos_supplier_payments_page()
    {
        $response = $this->get(route('pos.supplier-payments'));
        $response->assertStatus(200);
        $response->assertSee('Pelunasan Hutang');
    }

    /** @test */
    public function it_loads_outstanding_transactions_when_supplier_is_selected()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->set('supplier_id', $this->supplier->id)
            ->assertSet('items.0.purchase_transaction_id', $this->creditPurchase->id)
            ->assertSet('items.0.transaction_no', $this->creditPurchase->transaction_no)
            ->assertSet('items.0.amount_due', 8000000)
            ->assertSet('items.0.amount_paid', 0);
    }

    /** @test */
    public function toggle_item_selection_sets_amount_paid_to_full()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->set('supplier_id', $this->supplier->id)
            // Select item
            ->set('items.0.is_selected', true)
            ->call('toggleItemSelection', 0)
            ->assertSet('items.0.amount_paid', 8000000)
            
            // Deselect item
            ->set('items.0.is_selected', false)
            ->call('toggleItemSelection', 0)
            ->assertSet('items.0.amount_paid', 0);
    }

    /** @test */
    public function it_can_create_a_draft_supplier_payment()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->call('openCreate')
            ->set('supplier_id', $this->supplier->id)
            ->set('account_id', $this->cashAccount->id)
            ->set('payment_method', 'Cash')
            ->set('payment_reference', 'REF-999')
            ->set('notes', 'Bayar sebagian')
            // Set payment amount manually
            ->set('items.0.amount_paid', 3000000)
            ->call('save', 'draft')
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id' => $this->supplier->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'payment_reference' => 'REF-999',
            'total_amount' => 3000000,
            'status' => 'draft',
        ]);

        // Supplier debt should still be unchanged (draft)
        $this->assertEquals(8000000, $this->supplier->fresh()->outstanding_debt);
    }

    /** @test */
    public function it_can_create_and_post_supplier_payment()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->call('openCreate')
            ->set('supplier_id', $this->supplier->id)
            ->set('account_id', $this->cashAccount->id)
            ->set('payment_method', 'Bank Transfer')
            ->set('items.0.amount_paid', 5000000)
            ->call('save', 'posted')
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id' => $this->supplier->id,
            'account_id' => $this->cashAccount->id,
            'total_amount' => 5000000,
            'status' => 'posted',
        ]);

        // Supplier debt should be reduced from 8,000,000 to 3,000,000
        $this->assertEquals(3000000, $this->supplier->fresh()->outstanding_debt);
    }

    /** @test */
    public function it_can_post_a_draft_payment()
    {
        // 1. Create a draft payment
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-001',
            'payment_date' => now(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 2000000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $payment->items()->create([
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 8000000,
            'amount_paid' => 2000000,
        ]);

        // 2. Post via livewire component
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->call('confirmPost', $payment->id)
            ->assertSet('showPostConfirmation', true)
            ->assertSet('paymentIdToPost', $payment->id)
            ->call('postPayment')
            ->assertSet('showPostConfirmation', false);

        $this->assertEquals('posted', $payment->fresh()->status);
        $this->assertEquals(6000000, $this->supplier->fresh()->outstanding_debt);
    }

    /** @test */
    public function it_can_delete_a_draft_payment()
    {
        // 1. Create a draft payment
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-002',
            'payment_date' => now(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 1000000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $item = $payment->items()->create([
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 8000000,
            'amount_paid' => 1000000,
        ]);

        // 2. Delete via Livewire
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->call('confirmDelete', $payment->id)
            ->assertSet('showDeleteConfirmation', true)
            ->assertSet('paymentIdToDelete', $payment->id)
            ->call('deletePayment')
            ->assertSet('showDeleteConfirmation', false);

        $this->assertDatabaseMissing('supplier_payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('supplier_payment_items', ['id' => $item->id]);
    }
}
