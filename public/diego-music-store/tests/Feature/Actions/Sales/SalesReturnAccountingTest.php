<?php

namespace Tests\Feature\Actions\Sales;

use App\Actions\Sales\CreateSalesReturn;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $kasAccount;
    protected Account $piutangAccount;
    protected Account $returAccount;
    protected Account $cogsAccount;
    protected Account $inventoryAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Uji Retur', 'store_name' => 'Diego Retur']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);

        $this->kasAccount = Account::find(AccountHelper::resolveAccountId('111101001', 'KAS', 'Asset'));
        $this->piutangAccount = Account::find(AccountHelper::resolveAccountId('111301001', 'PIUTANG DAGANG', 'Asset'));
        $this->returAccount = Account::find(AccountHelper::resolveAccountId('411201001', 'RETUR PENJUALAN', 'Revenue'));
        $this->cogsAccount = Account::find(AccountHelper::resolveAccountId('511501001', 'HARGA POKOK PENJUALAN', 'Expense'));
        $this->inventoryAccount = Account::find(AccountHelper::resolveAccountId('111401001', 'PERSEDIAAN BARANG DAGANG', 'Asset'));
    }

    public function test_return_on_credit_sale_credits_piutang_and_decrements_customer_debt_without_cash_leakage(): void
    {
        $customer = Customer::create([
            'name' => 'Pelanggan Kredit',
            'outstanding_debt' => 3000000,
        ]);

        $product = Product::create(['name' => 'Gitar Akustik', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-TEST-01',
            'price' => 1000000,
            'cost_price' => 600000,
            'hpp' => 600000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 10,
            'hpp' => 600000,
        ]);

        // Create credit sale of 3 units = Rp 3.000.000
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-CR-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 3000000,
            'grand_total' => 3000000,
            'payment_method' => 'piutang',
            'status' => 'pending',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => 1000000,
            'total_price' => 3000000,
        ]);

        // Create initial sale journal entry with debit Piutang
        $saleJournal = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-SALE-001',
            'date' => now()->toDateString(),
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
            'status' => 'posted',
        ]);

        JournalItem::create([
            'journal_entry_id' => $saleJournal->id,
            'account_id' => $this->piutangAccount->id,
            'debit' => 3000000,
            'credit' => 0,
        ]);

        // Return 1 unit = Rp 1.000.000
        $salesReturn = app(CreateSalesReturn::class)->execute([
            'sale_id' => $sale->id,
            'reason' => 'Retur barang rusak',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ]
            ]
        ]);

        $this->assertEquals(1000000, $salesReturn->total_refund);

        // Find return journal
        $returnJournal = JournalEntry::where('reference_type', 'SalesReturn')
            ->where('reference_id', $salesReturn->id)
            ->first();

        $this->assertNotNull($returnJournal);

        // Assert Debit Retur Penjualan = 1.000.000
        $debitRetur = $returnJournal->items()
            ->where('account_id', $this->returAccount->id)
            ->where('debit', 1000000)
            ->first();
        $this->assertNotNull($debitRetur, 'Debit RETUR PENJUALAN harus Rp 1.000.000');

        // Assert Credit PIUTANG DAGANG = 1.000.000
        $creditPiutang = $returnJournal->items()
            ->where('account_id', $this->piutangAccount->id)
            ->where('credit', 1000000)
            ->first();
        $this->assertNotNull($creditPiutang, 'Kredit harus ke PIUTANG DAGANG Rp 1.000.000, bukan Kas!');

        // Assert NO cash was credited (no cash leakage)
        $creditKas = $returnJournal->items()
            ->where('account_id', $this->kasAccount->id)
            ->exists();
        $this->assertFalse($creditKas, 'Kas toko tidak boleh berkurang saat retur penjualan kredit!');

        // Assert customer outstanding debt decreased by 1.000.000 (3.000.000 - 1.000.000 = 2.000.000)
        $customer->refresh();
        $this->assertEquals(2000000, (float) $customer->outstanding_debt);
    }

    public function test_return_on_cash_sale_credits_kas(): void
    {
        $customer = Customer::create(['name' => 'Pelanggan Tunai']);
        $product = Product::create(['name' => 'Senar Gitar', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SNR-01',
            'price' => 100000,
            'cost_price' => 60000,
            'hpp' => 60000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 5,
            'hpp' => 60000,
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-CSH-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 200000,
            'grand_total' => 200000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'total_price' => 200000,
        ]);

        // Return 1 unit = Rp 100.000
        $salesReturn = app(CreateSalesReturn::class)->execute([
            'sale_id' => $sale->id,
            'reason' => 'Salah ukuran',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                ]
            ]
        ]);

        $returnJournal = JournalEntry::where('reference_type', 'SalesReturn')
            ->where('reference_id', $salesReturn->id)
            ->first();

        // For cash sale, Kas should be credited
        $creditKas = $returnJournal->items()
            ->where('account_id', $this->kasAccount->id)
            ->where('credit', 100000)
            ->first();
        $this->assertNotNull($creditKas, 'Kredit harus ke KAS Rp 100.000 untuk retur penjualan tunai');
    }
}
