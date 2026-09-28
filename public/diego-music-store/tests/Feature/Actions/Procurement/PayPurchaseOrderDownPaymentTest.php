<?php

namespace Tests\Feature\Actions\Procurement;

use App\Actions\Procurement\PayPurchaseOrderDownPayment;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayPurchaseOrderDownPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;
    protected Account $bankAccount;
    protected Account $advanceAccount;
    protected Account $apAccount;
    protected Account $inventoryAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->branch = Branch::firstOrCreate(
            ['name' => 'Cabang Pontianak'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $this->supplier = Supplier::create([
            'name' => 'PT Yamaha Musik Indonesia',
            'outstanding_debt' => 0,
        ]);

        $this->bankAccount = Account::find(AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'asset'));
        $this->advanceAccount = Account::find(AccountHelper::resolveAccountId('111201006', 'Uang Muka Pembelian', 'asset'));
        $this->apAccount = Account::find(AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability'));
        $this->inventoryAccount = Account::find(AccountHelper::resolveAccountId('111401001', 'PERSEDIAAN BARANG DAGANG', 'asset'));
    }

    public function test_can_pay_po_down_payment_and_creates_correct_accounting_journal(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260927-0001',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'approved',
            'grand_total' => 10000000,
            'total_amount' => 10000000,
        ]);

        $action = app(PayPurchaseOrderDownPayment::class);
        $updatedPo = $action->execute($po, [
            'dp_amount' => 3000000,
            'dp_account_id' => $this->bankAccount->id,
            'dp_paid_at' => now()->format('Y-m-d'),
            'dp_reference_no' => 'TRF-BCA-98765',
            'dp_notes' => 'DP 30% pemesanan gitar',
        ], $this->user->id);

        $this->assertEquals(3000000, $updatedPo->dp_amount);
        $this->assertEquals($this->bankAccount->id, $updatedPo->dp_account_id);
        $this->assertNotEmpty($updatedPo->dp_journal_no);

        // Verify Journal Entry
        $journal = JournalEntry::where('reference_type', 'PurchaseOrderDP')
            ->where('reference_id', $po->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(2, $journal->items()->count());

        // Debit: Uang Muka Pembelian
        $debitItem = $journal->items()->where('debit', '>', 0)->first();
        $this->assertNotNull($debitItem);
        $this->assertEquals(3000000, $debitItem->debit);
        $this->assertEquals($this->advanceAccount->id, $debitItem->account_id);

        // Credit: Bank BCA
        $creditItem = $journal->items()->where('credit', '>', 0)->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals(3000000, $creditItem->credit);
        $this->assertEquals($this->bankAccount->id, $creditItem->account_id);
    }

    public function test_purchase_transaction_offsets_dp_and_creates_net_debt(): void
    {
        $unit = Unit::firstOrCreate(['name' => 'Pcs'], ['code' => 'PCS']);
        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'unit_id' => $unit->id,
            'inventory_account_id' => $this->inventoryAccount->id,
            'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-YMH-01',
            'price' => 1200000,
            'cost_price' => 1000000,
            'hpp' => 1000000,
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260927-0002',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'approved',
            'grand_total' => 10000000,
            'total_amount' => 10000000,
            'dp_amount' => 3000000,
            'dp_account_id' => $this->bankAccount->id,
            'dp_paid_at' => now()->format('Y-m-d'),
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260927-0001',
            'transaction_date' => now()->format('Y-m-d'),
            'po_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'invoice_number' => 'INV-SUPP-12345',
            'subtotal' => 10000000,
            'grand_total' => 10000000,
            'down_payment_amount' => 3000000,
            'status' => 'draft',
        ]);

        PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 10,
            'qty_received' => 10,
            'unit_id' => $unit->id,
            'price' => 1000000,
            'subtotal' => 10000000,
        ]);

        // Post the transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        // Verify supplier outstanding debt is only the NET debt (10jt - 3jt = 7jt)
        $this->supplier->refresh();
        $this->assertEquals(7000000, (int) $this->supplier->outstanding_debt);

        // Verify getRemainingUnpaidAmount() on PT is 7,000,000
        $this->assertEquals(7000000, $pt->getRemainingUnpaidAmount());

        // Verify Journal Entry
        $journal = JournalEntry::where('reference_type', 'Purchase')
            ->where('reference_id', $pt->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Debit: Persediaan = 10,000,000
        $invItem = $journal->items()->where('account_id', $this->inventoryAccount->id)->first();
        $this->assertNotNull($invItem);
        $this->assertEquals(10000000, $invItem->debit);

        // Credit 1: Uang Muka Pembelian = 3,000,000
        $dpItem = $journal->items()->where('account_id', $this->advanceAccount->id)->first();
        $this->assertNotNull($dpItem);
        $this->assertEquals(3000000, $dpItem->credit);

        // Credit 2: Hutang Dagang = 7,000,000
        $apItem = $journal->items()->where('account_id', $this->apAccount->id)->first();
        $this->assertNotNull($apItem);
        $this->assertEquals(7000000, $apItem->credit);
    }
}
