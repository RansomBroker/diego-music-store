<?php

namespace Tests\Feature\Actions;

use App\Actions\Accounting\CreateJournalEntry;
use App\Actions\Accounting\UpdateJournalEntry;
use App\Actions\Accounting\PostJournalEntry;
use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\StockOpname\CreateStockOpname;
use App\Actions\StockOpname\ProcessStockOpnameComplete;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountingActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Supplier $supplier;
    private ProductVariant $variant;
    private Account $cashAccount;
    private Account $payableAccount;
    private Account $inventoryAccount;
    private Account $hppAccount;
    private Account $expenseAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier Test',
            'phone' => '0812345678',
            'address' => 'Denpasar',
            'outstanding_debt' => 0,
        ]);

        // Seed basic accounts
        $this->cashAccount = Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'Asset']);
        $this->payableAccount = Account::create(['code' => '2-1000', 'name' => 'Hutang Dagang', 'classification' => 'Liability']);
        $this->inventoryAccount = Account::create(['code' => '1-1300', 'name' => 'Persediaan', 'classification' => 'Asset']);
        $this->hppAccount = Account::create(['code' => '5-1000', 'name' => 'HPP', 'classification' => 'Expense']);
        $this->expenseAccount = Account::create(['code' => '6-1000', 'name' => 'Beban Operasional', 'classification' => 'Expense']);

        $product = Product::create([
            'name' => 'Yamaha Guitar F310',
            'type' => 'physical',
            'is_active' => true,
            'inventory_account_id' => $this->inventoryAccount->id,
            'sales_account_id' => $this->cashAccount->id,
            'cogs_account_id' => $this->hppAccount->id,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-F310-NAT',
            'barcode' => '999123456',
            'name' => 'Natural',
            'price' => 1200000,
            'cost_price' => 800000,
            'hpp' => 800000,
            'is_active' => true,
        ]);
    }

    public function test_it_can_create_and_update_journal_entry_manually(): void
    {
        // 1. Create balanced journal entry
        $data = [
            'branch_id' => $this->branch->id,
            'date' => '2026-06-30',
            'description' => 'Jurnal Penyesuaian Manual',
            'items' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                    'notes' => 'Debit Kas',
                ],
                [
                    'account_id' => $this->payableAccount->id,
                    'debit' => 0,
                    'credit' => 500000,
                    'notes' => 'Kredit Hutang',
                ]
            ]
        ];

        $entry = app(CreateJournalEntry::class)->execute($data);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals('draft', $entry->status);
        $this->assertCount(2, $entry->items);
        $this->assertEquals(500000, $entry->items->sum('debit'));
        $this->assertEquals(500000, $entry->items->sum('credit'));

        // 2. Validate unbalanced throws exception
        $unbalancedData = $data;
        $unbalancedData['items'][1]['credit'] = 400000; // unbalanced

        $this->expectException(ValidationException::class);
        app(CreateJournalEntry::class)->execute($unbalancedData);
    }

    public function test_it_can_post_journal_entry(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'date' => '2026-06-30',
            'description' => 'Jurnal Penyesuaian Manual',
            'items' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $this->payableAccount->id,
                    'debit' => 0,
                    'credit' => 500000,
                ]
            ]
        ];

        $entry = app(CreateJournalEntry::class)->execute($data);
        $this->assertEquals('draft', $entry->status);

        $postedEntry = app(PostJournalEntry::class)->execute($entry);
        $this->assertEquals('posted', $postedEntry->status);
        $this->assertNotNull($postedEntry->posted_at);
        $this->assertEquals($this->user->id, $postedEntry->posted_by);
    }

    public function test_posting_purchase_transaction_creates_journal_entry(): void
    {
        // 1. Create Purchase Transaction
        $ptData = [
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'transaction_no' => 'TX-PURCHASE-001',
            'transaction_date' => '2026-06-30',
            'purchase_type' => 'Kredit',
            'discount' => 50000,
            'shipping_cost' => 20000,
            'other_cost' => 10000,
            'tax_rate' => 11,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 5,
                    'price' => 800000,
                    'discount' => 0,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($ptData);

        // 2. Post Purchase Transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        // 3. Assert Journal Entry is created and balances
        $journal = JournalEntry::where('reference_type', 'Purchase')
            ->where('reference_id', $pt->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        
        $debitTotal = $journal->items()->sum('debit');
        $creditTotal = $journal->items()->sum('credit');

        $this->assertTrue($debitTotal > 0);
        $this->assertEquals($debitTotal, $creditTotal);
        $this->assertEquals($pt->grand_total, $journal->items()->where('account_id', $this->payableAccount->id)->first()->credit);
    }

    public function test_completing_stock_opname_creates_journal_entry(): void
    {
        // 1. Create Stock Opname with deficit (-2 items)
        $opname = StockOpname::create([
            'branch_id' => $this->branch->id,
            'opname_number' => 'SO-001',
            'opname_date' => '2026-06-30',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_variant_id' => $this->variant->id,
            'system_qty' => 10,
            'physical_qty' => 8, // difference: -2
            'difference' => -2,
            'cost_price' => 800000,
        ]);

        // 2. Complete Stock Opname
        app(ProcessStockOpnameComplete::class)->execute($opname);

        // 3. Assert Journal Entry is created and balances
        $journal = JournalEntry::where('reference_type', 'Opname')
            ->where('reference_id', $opname->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        $debitTotal = $journal->items()->sum('debit');
        $creditTotal = $journal->items()->sum('credit');

        $this->assertEquals(1600000, $debitTotal); // 2 * 800,000
        $this->assertEquals($debitTotal, $creditTotal);

        // HPP should be debited
        $this->assertEquals(1600000, $journal->items()->where('account_id', $this->hppAccount->id)->first()->debit);
        // Persediaan should be credited
        $this->assertEquals(1600000, $journal->items()->where('account_id', $this->inventoryAccount->id)->first()->credit);
    }
}
