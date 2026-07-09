<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SupplierPayments\Pages\CreateSupplierPayment;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierPaymentFormTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Branch $branch;
    private Account $cashAccount;
    private PurchaseTransaction $creditPurchase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 2. Create master data
        $this->supplier = Supplier::create([
            'name' => 'Test Supplier Co',
            'phone' => '08123456789',
            'address' => 'Test Address',
            'outstanding_debt' => 0,
        ]);

        $this->branch = Branch::create([
            'name' => 'Denpasar Store',
            'address' => 'Jl. Bypass',
            'phone' => '0361-9999',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Guitar Test',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-TST',
            'name' => 'Standard',
            'price' => 5000000,
            'cost_price' => 4000000,
            'hpp' => 4000000,
            'is_active' => true,
        ]);

        // 3. Create cash/bank account with code matching 1-1%
        $this->cashAccount = Account::create([
            'code' => '1-1100',
            'name' => 'Kas Toko',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        // 4. Create an outstanding posted credit purchase
        $this->creditPurchase = PurchaseTransaction::create([
            'transaction_no' => 'PT-2026-0001',
            'transaction_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'grand_total' => 4000000,
            'status' => 'posted',
            'posted_at' => now(),
            'due_date' => '2026-08-01',
        ]);

        PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $this->creditPurchase->id,
            'product_variant_id' => $variant->id,
            'qty_received' => 1,
            'price' => 4000000,
            'subtotal' => 4000000,
        ]);

        $this->supplier->increment('outstanding_debt', 4000000);
    }

    /** @test */
    public function it_loads_outstanding_transactions_when_supplier_is_selected()
    {
        Livewire::test(CreateSupplierPayment::class)
            ->set('data.supplier_id', $this->supplier->id)
            ->assertSet('data.items.0.purchase_transaction_id', $this->creditPurchase->id)
            ->assertSet('data.items.0.transaction_no', $this->creditPurchase->transaction_no)
            ->assertSet('data.items.0.amount_due', 4000000)
            ->assertSet('data.items.0.amount_paid', 0)
            ->assertSet('data.items.0.is_selected', false);
    }

    /** @test */
    public function checking_invoice_sets_payment_to_amount_due()
    {
        Livewire::test(CreateSupplierPayment::class)
            ->set('data.supplier_id', $this->supplier->id)
            // Check the checkbox
            ->set('data.items.0.is_selected', true)
            // Payment amount should equal outstanding amount
            ->assertSet('data.items.0.amount_paid', 4000000)
            
            // Uncheck the checkbox
            ->set('data.items.0.is_selected', false)
            // Payment amount should reset to 0
            ->assertSet('data.items.0.amount_paid', 0);
    }

    /** @test */
    public function manually_setting_payment_amount_synchronizes_checkbox()
    {
        Livewire::test(CreateSupplierPayment::class)
            ->set('data.supplier_id', $this->supplier->id)
            // Set payment to 2,000,000 (partial payment)
            ->set('data.items.0.amount_paid', 2000000)
            // Checkbox should automatically turn on
            ->assertSet('data.items.0.is_selected', true)

            // Set payment back to 0
            ->set('data.items.0.amount_paid', 0)
            // Checkbox should automatically turn off
            ->assertSet('data.items.0.is_selected', false);
    }
}
