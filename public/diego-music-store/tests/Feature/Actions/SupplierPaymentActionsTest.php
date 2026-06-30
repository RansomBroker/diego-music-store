<?php

namespace Tests\Feature\Actions;

use App\Actions\SupplierPayment\CreateSupplierPayment;
use App\Actions\SupplierPayment\UpdateSupplierPayment;
use App\Actions\SupplierPayment\ProcessSupplierPaymentComplete;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPaymentActionsTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;
    private Branch $branch;
    private ProductVariant $variant;
    private Account $cashAccount;
    private Account $apAccount;
    private PurchaseTransaction $creditPurchase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create([
            'name' => 'Yamaha Music Supplier',
            'phone' => '08123456780',
            'address' => 'Jakarta',
            'outstanding_debt' => 0,
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Yamaha Guitar C40',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-C40-BLK',
            'barcode' => '888123456',
            'name' => 'Hitam',
            'price' => 1500000,
            'cost_price' => 1000000,
            'hpp' => 1000000,
            'is_active' => true,
        ]);

        // Create Cash/Bank account
        $this->cashAccount = Account::create([
            'code' => '1-1000',
            'name' => 'Kas Utama',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        // Create Accounts Payable account
        $this->apAccount = Account::create([
            'code' => '2-1000',
            'name' => 'Hutang Dagang',
            'classification' => 'liability',
            'is_active' => true,
            'is_header' => false,
        ]);

        // Create a posted credit purchase transaction with outstanding debt
        $this->creditPurchase = PurchaseTransaction::create([
            'transaction_no' => 'PT-TEST-001',
            'transaction_date' => '2026-06-28',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'grand_total' => 10000000, // 10 Juta
            'status' => 'posted',
            'posted_at' => now(),
            'due_date' => '2026-07-28',
        ]);

        PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $this->creditPurchase->id,
            'product_variant_id' => $this->variant->id,
            'qty_received' => 10,
            'price' => 1000000,
            'subtotal' => 10000000,
        ]);

        // Simulate the debt that PostPurchaseTransaction would have created
        $this->supplier->increment('outstanding_debt', 10000000);
    }

    public function test_it_can_create_supplier_payment_as_draft(): void
    {
        $data = [
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Bank Transfer',
            'payment_reference' => 'TRF-12345',
            'notes' => 'Pelunasan parsial pertama',
            'status' => 'draft',
            'items' => [
                [
                    'purchase_transaction_id' => $this->creditPurchase->id,
                    'amount_due' => 10000000,
                    'amount_paid' => 5000000, // Bayar separuh
                ],
            ],
        ];

        $payment = app(CreateSupplierPayment::class)->execute($data);

        $this->assertInstanceOf(SupplierPayment::class, $payment);
        $this->assertEquals('draft', $payment->status);
        $this->assertEquals(5000000, $payment->total_amount);
        $this->assertCount(1, $payment->items);
        $this->assertEquals(5000000, $payment->items->first()->amount_paid);

        // Supplier debt should NOT change yet (draft)
        $this->supplier->refresh();
        $this->assertEquals(10000000, $this->supplier->outstanding_debt);
    }

    public function test_it_can_update_supplier_payment(): void
    {
        // Create initial draft
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-001',
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 3000000,
            'status' => 'draft',
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 10000000,
            'amount_paid' => 3000000,
        ]);

        // Update the payment amount
        $updateData = [
            'payment_date' => '2026-07-02',
            'payment_method' => 'Bank Transfer',
            'payment_reference' => 'UPDATED-REF',
            'items' => [
                [
                    'purchase_transaction_id' => $this->creditPurchase->id,
                    'amount_due' => 10000000,
                    'amount_paid' => 7000000, // Increase payment
                ],
            ],
        ];

        $updated = app(UpdateSupplierPayment::class)->execute($payment, $updateData);
        $updated->refresh();

        $this->assertEquals('Bank Transfer', $updated->payment_method);
        $this->assertEquals(7000000, $updated->total_amount);
        $this->assertCount(1, $updated->items);
        $this->assertEquals(7000000, $updated->items->first()->amount_paid);
    }

    public function test_it_cannot_update_posted_payment(): void
    {
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-002',
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 5000000,
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Pelunasan hutang yang sudah diposting tidak dapat diubah.');

        app(UpdateSupplierPayment::class)->execute($payment, [
            'payment_method' => 'Bank Transfer',
        ]);
    }

    public function test_it_can_post_payment_and_reduce_supplier_debt(): void
    {
        // Create draft payment
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-003',
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Bank Transfer',
            'total_amount' => 10000000,
            'status' => 'draft',
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 10000000,
            'amount_paid' => 10000000, // Full payment
        ]);

        // Post the payment
        app(ProcessSupplierPaymentComplete::class)->execute($payment);

        $payment->refresh();
        $this->assertEquals('posted', $payment->status);
        $this->assertNotNull($payment->posted_at);
        $this->assertNotNull($payment->journal_no);

        // Supplier debt should be reduced to 0
        $this->supplier->refresh();
        $this->assertEquals(0, $this->supplier->outstanding_debt);
    }

    public function test_posted_payment_creates_journal_entry(): void
    {
        // Create draft payment
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-004',
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 5000000,
            'status' => 'draft',
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 10000000,
            'amount_paid' => 5000000,
        ]);

        // Post
        app(ProcessSupplierPaymentComplete::class)->execute($payment);

        // Verify journal entry was created
        $journal = JournalEntry::where('reference_type', 'SupplierPayment')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertStringContainsString('Pelunasan Hutang', $journal->description);

        // Verify journal items (Debit AP, Credit Cash)
        $journalItems = JournalItem::where('journal_entry_id', $journal->id)->get();
        $this->assertCount(2, $journalItems);

        $debitItem = $journalItems->where('debit', '>', 0)->first();
        $creditItem = $journalItems->where('credit', '>', 0)->first();

        $this->assertNotNull($debitItem);
        $this->assertNotNull($creditItem);

        // Debit Hutang Dagang (AP account)
        $this->assertEquals($this->apAccount->id, $debitItem->account_id);
        $this->assertEquals(5000000, $debitItem->debit);

        // Credit Kas/Bank
        $this->assertEquals($this->cashAccount->id, $creditItem->account_id);
        $this->assertEquals(5000000, $creditItem->credit);
    }

    public function test_partial_payment_leaves_remaining_debt(): void
    {
        // Create and post a partial payment (5M of 10M)
        $data = [
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Bank Transfer',
            'status' => 'posted',
            'items' => [
                [
                    'purchase_transaction_id' => $this->creditPurchase->id,
                    'amount_due' => 10000000,
                    'amount_paid' => 4000000, // Partial payment
                ],
            ],
        ];

        $payment = app(CreateSupplierPayment::class)->execute($data);

        $payment->refresh();
        $this->assertEquals('posted', $payment->status);

        // Supplier outstanding debt: 10M - 4M = 6M
        $this->supplier->refresh();
        $this->assertEquals(6000000, $this->supplier->outstanding_debt);

        // Purchase transaction should still have 6M unpaid
        $this->creditPurchase->refresh();
        $remaining = $this->creditPurchase->getRemainingUnpaidAmount();
        $this->assertEquals(6000000, $remaining);
    }

    public function test_cannot_post_already_posted_payment(): void
    {
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-005',
            'payment_date' => '2026-07-01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Cash',
            'total_amount' => 5000000,
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $this->creditPurchase->id,
            'amount_due' => 10000000,
            'amount_paid' => 5000000,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Hanya pelunasan hutang dengan status draft yang dapat diposting.');

        app(ProcessSupplierPaymentComplete::class)->execute($payment);
    }
}
