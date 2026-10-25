<?php

namespace Tests\Feature\Actions\Sales;

use App\Actions\Sales\CreatePOSSale;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTaxAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Pajak', 'store_name' => 'Diego Pajak']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);

        AccountHelper::resolveAccountId('111101001', 'KAS', 'Asset');
        AccountHelper::resolveAccountId('411101001', 'PENJUALAN', 'Revenue');
        AccountHelper::resolveAccountId('211201001', 'HUTANG PPN (PPN KELUARAN)', 'Liability');
        AccountHelper::resolveAccountId('511501001', 'HARGA POKOK PENJUALAN', 'Expense');
        AccountHelper::resolveAccountId('111401001', 'PERSEDIAAN BARANG DAGANG', 'Asset');
    }

    public function test_pos_checkout_with_tax_splits_revenue_and_vat_liability_correctly(): void
    {
        $customer = Customer::create(['name' => 'Pelanggan POS']);
        $product = Product::create(['name' => 'Keyboard Roland', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'KBD-001',
            'price' => 10000000,
            'cost_price' => 7000000,
            'hpp' => 7000000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 5,
            'hpp' => 7000000,
        ]);

        $subtotal = 10000000;
        $discount = 500000;
        $tax = 1045000; // 11% dari DPP (9.500.000)
        $grandTotal = $subtotal - $discount + $tax; // 10.045.000

        $data = [
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'payment_method' => 'cash',
            'payments' => [
                ['method' => 'cash', 'amount' => $grandTotal]
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'qty' => 1,
                    'price' => $subtotal,
                    'discount_amount' => 0,
                ]
            ],
        ];

        $sale = app(CreatePOSSale::class)->execute($data);

        $this->assertEquals($grandTotal, $sale->grand_total);

        $journal = JournalEntry::where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->first();

        $this->assertNotNull($journal);

        $kasAcc = AccountHelper::findByCode('111101001');
        $salesAcc = AccountHelper::findByCode('411101001');
        $taxAcc = AccountHelper::findByCode('211201001');

        // Total Debit Kas == grand_total
        $totalDebit = $journal->items()->sum('debit');
        $kasDebit = $journal->items()->where('account_id', $kasAcc->id)->sum('debit');
        $this->assertEquals($grandTotal, $kasDebit);

        // Net sales credit == 9.500.000 (Subtotal - Discount)
        $netSalesCredit = $journal->items()->where('account_id', $salesAcc->id)->sum('credit');
        $this->assertEquals(9500000, $netSalesCredit, 'Akun PENJUALAN harus dikredit senilai net sales (DPP), bukan grand total!');

        // VAT liability credit == 1.045.000
        $taxCredit = $journal->items()->where('account_id', $taxAcc->id)->sum('credit');
        $this->assertEquals($tax, $taxCredit, 'Akun HUTANG PPN harus dikredit senilai nominal pajak!');

        // Balanced: Net Sales (9.500.000) + PPN (1.045.000) == Kas (10.045.000)
        $this->assertEquals($grandTotal, $netSalesCredit + $taxCredit);
    }
}
