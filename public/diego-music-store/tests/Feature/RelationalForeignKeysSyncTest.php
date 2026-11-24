<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountClassification;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\JournalEntry;
use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\SaleCategory;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationalForeignKeysSyncTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Jl. Test',
            'phone' => '08123',
            'is_active' => true,
        ]);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch);
        $this->actingAs($this->user);
    }

    /** @test */
    public function sale_links_sale_category_id_and_reflects_category_rename(): void
    {
        $category = SaleCategory::firstOrCreate(
            ['name' => 'Store'],
            [
                'prefix' => 'INV-S',
                'start_alphabet' => 'A',
                'end_alphabet' => 'Z',
                'digit_length' => 4,
            ]
        );

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-S-A0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100000,
            'grand_total' => 100000,
            'payment_method' => 'Cash',
            'sale_category' => 'Store',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $this->assertSame($category->id, $sale->sale_category_id);
        $this->assertSame('Store', $sale->sale_category_display);

        // Rename master category -> display updates automatically without touching amounts
        $category->update(['name' => 'Toko Utama']);
        $sale = $sale->fresh(['saleCategory']);
        $this->assertSame('Toko Utama', $sale->sale_category_display);
        $this->assertSame(100000, (int) $sale->grand_total);
    }

    /** @test */
    public function account_links_account_classification_id_automatically(): void
    {
        $cls = AccountClassification::firstOrCreate(
            ['key' => 'asset'],
            ['name' => 'Aset / Aktiva']
        );

        $account = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111101099',
            'name' => 'KAS TEST',
            'classification' => 'Asset',
            'is_active' => true,
        ]);

        $this->assertSame($cls->id, $account->account_classification_id);
        $this->assertSame($cls->id, $account->accountClassification->id);
    }

    /** @test */
    public function customer_deposit_and_supplier_payment_link_payment_method_id_and_reflect_rename(): void
    {
        $cashAccount = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111101001',
            'name' => 'KAS',
            'classification' => 'Asset',
            'is_active' => true,
        ]);

        $transferPm = PaymentMethod::create([
            'name' => 'Transfer Bank',
            'code' => 'transfer-bank',
            'account_id' => $cashAccount->id,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Pelanggan A',
            'phone' => '081111',
        ]);

        $deposit = CustomerDeposit::create([
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'custom',
            'product_name' => 'Gitar Custom',
            'price' => 1000000,
            'qty' => 1,
            'total_amount' => 1000000,
            'deposit_amount' => 300000,
            'remaining_amount' => 700000,
            'account_id' => $cashAccount->id,
            'payment_method' => 'Transfer Bank',
            'status' => 'pending',
        ]);

        $supplier = Supplier::create([
            'name' => 'Supplier A',
            'phone' => '082222',
        ]);

        $sp = SupplierPayment::create([
            'payment_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $cashAccount->id,
            'payment_method' => 'Transfer Bank',
            'total_amount' => 500000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->assertSame($transferPm->id, $deposit->payment_method_id);
        $this->assertSame($transferPm->id, $sp->payment_method_id);

        $transferPm->update(['name' => 'Transfer BCA']);

        $this->assertSame('Transfer BCA', $deposit->fresh(['paymentMethodRelation'])->payment_method_display);
        $this->assertSame('Transfer BCA', $sp->fresh(['paymentMethodRelation'])->payment_method_display);
    }

    /** @test */
    public function journal_entry_relations_work_on_purchase_sales_invoice_supplier_payment_and_po(): void
    {
        $journal = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-REL-0001',
            'date' => now()->toDateString(),
            'description' => 'Test Journal Link',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $supplier = Supplier::create(['name' => 'Supplier B', 'phone' => '083333']);
        $customer = Customer::create(['name' => 'Customer B', 'phone' => '084444']);
        $acc = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111101001',
            'name' => 'KAS',
            'classification' => 'Asset',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->toDateString(),
            'status' => 'approved',
            'dp_journal_no' => $journal->entry_no,
            'dp_journal_entry_id' => $journal->id,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'invoice_number' => 'SUP-001',
            'invoice_date' => now()->toDateString(),
            'status' => 'posted',
            'journal_no' => $journal->entry_no,
            'journal_entry_id' => $journal->id,
            'created_by' => $this->user->id,
        ]);

        $si = SalesInvoice::create([
            'customer_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'invoice_number' => 'SINV-001',
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'Tunai',
            'status' => 'posted',
            'journal_no' => $journal->entry_no,
            'journal_entry_id' => $journal->id,
            'created_by' => $this->user->id,
        ]);

        $sp = SupplierPayment::create([
            'payment_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $acc->id,
            'payment_method' => 'Tunai',
            'total_amount' => 100000,
            'status' => 'posted',
            'journal_no' => $journal->entry_no,
            'journal_entry_id' => $journal->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertSame($journal->id, $po->dpJournalEntry->id);
        $this->assertSame($journal->id, $pt->journalEntry->id);
        $this->assertSame($journal->id, $si->journalEntry->id);
        $this->assertSame($journal->id, $sp->journalEntry->id);
    }
}
