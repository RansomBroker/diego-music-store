<?php

namespace Tests\Feature\Actions\Procurement;

use App\Actions\Branch\CreateBranch;
use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\Purchases\CreatePurchaseReturn;
use App\Actions\SupplierPayment\ProcessSupplierPaymentComplete;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsignmentPurchaseAndReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_consignment_purchase_has_no_due_date_posts_hutang_konsinyasi_and_supports_partial_payment_and_return(): void
    {
        $branch = CreateBranch::execute([
            'name' => 'Cabang Konsinyasi Test',
            'store_name' => 'Diego Musik Konsinyasi',
        ]);
        session(['pos_active_branch_id' => $branch->id]);

        $supplier = Supplier::create([
            'name' => 'PT Titip Gitar',
            'status' => Supplier::STATUS_SUPPLIER,
            'outstanding_debt' => 0,
        ]);

        $product = Product::create([
            'name' => 'Gitar Konsinyasi',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'KNS-001',
            'price' => 2_500_000,
            'cost_price' => 2_000_000,
            'hpp' => 2_000_000,
        ]);

        // 1. Create & post Konsinyasi purchase (even if due_date passed, it must be forced null)
        $pt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-11-26',
            'due_date' => '2026-12-26',
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KONSINYASI,
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'qty_received' => 3,
                    'price' => 2_000_000,
                ],
            ],
        ]);

        $this->assertNull($pt->due_date);
        $this->assertTrue($pt->isConsignment());

        $pt = app(PostPurchaseTransaction::class)->execute($pt);
        $supplier->refresh();

        $this->assertEquals(6_000_000, (int) $supplier->outstanding_debt);
        $this->assertSame(6_000_000, $pt->getRemainingUnpaidAmount());

        $stock = ProductBranchStock::where('branch_id', $branch->id)
            ->where('product_variant_id', $variant->id)
            ->firstOrFail();
        $this->assertEquals(3, $stock->stock);

        // Verify credit journal item goes to 211101002 HUTANG KONSINYASI
        $hutangKonsinyasi = Account::where('branch_id', $branch->id)->where('code', '211101002')->firstOrFail();
        $creditItem = JournalItem::where('journal_entry_id', $pt->journal_entry_id)
            ->where('account_id', $hutangKonsinyasi->id)
            ->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals(6_000_000, (int) $creditItem->credit);

        // 2. Partial payment for 1 sold unit (Rp 2.000.000) debits HUTANG KONSINYASI
        $cashAccount = Account::where('branch_id', $branch->id)->where('code', '111101001')->firstOrFail();
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-KNS-001',
            'payment_date' => '2026-11-26',
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'account_id' => $cashAccount->id,
            'payment_method' => 'Bank Transfer',
            'total_amount' => 2_000_000,
            'status' => 'draft',
        ]);
        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $pt->id,
            'amount_paid' => 2_000_000,
        ]);

        app(ProcessSupplierPaymentComplete::class)->execute($payment);
        $payment->refresh();
        $supplier->refresh();

        $this->assertEquals(4_000_000, (int) $supplier->outstanding_debt);
        $this->assertSame(4_000_000, $pt->getRemainingUnpaidAmount());

        $paymentDebitItem = JournalItem::where('journal_entry_id', $payment->journal_entry_id)
            ->where('account_id', $hutangKonsinyasi->id)
            ->first();
        $this->assertNotNull($paymentDebitItem);
        $this->assertEquals(2_000_000, (int) $paymentDebitItem->debit);

        // 3. Supplier pulls back 2 unsold units via PurchaseReturn (return_type = consignment)
        $detail = $pt->details()->firstOrFail();
        $return = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'return_type' => 'consignment',
            'status' => 'posted',
            'reason' => 'Supplier menarik kembali sisa barang konsinyasi',
            'items' => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $this->assertSame('consignment', $return->return_type);
        $this->assertSame('posted', $return->status);

        $stock->refresh();
        $supplier->refresh();

        $this->assertEquals(1, $stock->stock);
        $this->assertEquals(0, (int) $supplier->outstanding_debt);
        $this->assertSame(0, $pt->getRemainingUnpaidAmount());

        // Verify return journal reverses HUTANG KONSINYASI (Debit) and PERSEDIAAN (Credit)
        $returnJournal = JournalEntry::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $return->id)
            ->firstOrFail();
        $returnDebit = JournalItem::where('journal_entry_id', $returnJournal->id)
            ->where('account_id', $hutangKonsinyasi->id)
            ->first();
        $this->assertNotNull($returnDebit);
        $this->assertEquals(4_000_000, (int) $returnDebit->debit);
    }

    public function test_consignment_return_rejects_non_consignment_purchase_transaction(): void
    {
        $branch = CreateBranch::execute([
            'name' => 'Cabang Retur Guard',
            'store_name' => 'Diego Musik Guard',
        ]);
        session(['pos_active_branch_id' => $branch->id]);

        $supplier = Supplier::create([
            'name' => 'PT Beli Putus',
            'status' => Supplier::STATUS_SUPPLIER,
        ]);

        $product = Product::create(['name' => 'Senar Gitar', 'type' => 'physical', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => '010',
            'sku' => 'SNR-010',
            'price' => 100_000,
            'cost_price' => 60_000,
            'hpp' => 60_000,
        ]);

        $pt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-11-26',
            'due_date' => '2026-12-26',
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'qty_received' => 5,
                    'price' => 60_000,
                ],
            ],
        ]);
        $pt = app(PostPurchaseTransaction::class)->execute($pt);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Metode Retur Konsinyasi hanya berlaku untuk transaksi pembelian berjenis Konsinyasi.');

        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'return_type' => 'consignment',
            'status' => 'posted',
            'reason' => 'Test salah jenis',
            'items' => [
                [
                    'purchase_transaction_detail_id' => $pt->details()->firstOrFail()->id,
                    'quantity' => 1,
                ],
            ],
        ]);
    }
}

