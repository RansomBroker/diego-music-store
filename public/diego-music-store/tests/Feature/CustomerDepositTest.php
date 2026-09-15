<?php

namespace Tests\Feature;

use App\Actions\CustomerDeposit\CancelCustomerDeposit;
use App\Actions\CustomerDeposit\CreateCustomerDeposit;
use App\Actions\CustomerDeposit\SettleCustomerDeposit;
use App\Actions\CustomerDeposit\UpdateCustomerDeposit;
use App\Livewire\PosCustomerDeposits;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerDepositTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Customer $customer;
    protected Account $cashAccount;
    protected Account $penitipanDanaAccount;
    protected Account $salesAccount;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama Test',
            'address' => 'Jl. Test No. 1',
            'phone' => '0812345678',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);

        $this->customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081122334455',
            'address' => 'Jl. Mawar No. 10',
            'deposit_balance' => 0,
        ]);

        // COA Setup
        $this->cashAccount = Account::firstOrCreate(['code' => '1-1000'], [
            'name' => 'Kas Utama',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $this->penitipanDanaAccount = Account::firstOrCreate(['code' => '2-1200'], [
            'name' => 'Penitipan Dana',
            'classification' => 'liability',
            'is_active' => true,
            'is_header' => false,
        ]);

        $this->salesAccount = Account::firstOrCreate(['code' => '4-1000'], [
            'name' => 'Pendapatan Penjualan',
            'classification' => 'revenue',
            'is_active' => true,
            'is_header' => false,
        ]);

        // Product Setup
        $this->product = Product::create([
            'name' => 'Gitar Fender Stratocaster',
            'type' => 'physical',
            'is_active' => true,
            'sales_account_id' => $this->salesAccount->id,
        ]);

        ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'FND-STRAT-01',
            'name' => 'Standard Sunburst',
            'price' => 15000000,
            'is_active' => true,
        ]);
    }

    public function test_can_create_customer_deposit_with_existing_product(): void
    {
        $action = new CreateCustomerDeposit();

        $deposit = $action->execute([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'existing',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'price' => 15000000,
            'qty' => 1,
            'deposit_amount' => 5000000,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Transfer BCA',
            'notes' => 'Inden warna sunburst',
        ], $this->user);

        $this->assertNotNull($deposit);
        $this->assertEquals('pending', $deposit->status);
        $this->assertEquals(15000000, $deposit->total_amount);
        $this->assertEquals(5000000, $deposit->deposit_amount);
        $this->assertEquals(10000000, $deposit->remaining_amount);
        $this->assertNotNull($deposit->deposit_journal_entry_id);

        // Assert Accounting Journal Entries
        $journal = $deposit->depositJournalEntry;
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Debit: Kas (5,000,000)
        $debitItem = $journal->items()->where('account_id', $this->cashAccount->id)->first();
        $this->assertNotNull($debitItem);
        $this->assertEquals(5000000, $debitItem->debit);
        $this->assertEquals(0, $debitItem->credit);

        // Kredit: Penitipan Dana (5,000,000)
        $creditItem = $journal->items()->where('account_id', $this->penitipanDanaAccount->id)->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals(0, $creditItem->debit);
        $this->assertEquals(5000000, $creditItem->credit);

        // Customer deposit balance should increase
        $this->assertEquals(5000000, $this->customer->fresh()->deposit_balance);
    }

    public function test_can_create_customer_deposit_with_manual_po_product(): void
    {
        $action = new CreateCustomerDeposit();

        $deposit = $action->execute([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'manual',
            'product_name' => 'Custom Gibson Les Paul 1959 Reissue (PO)',
            'price' => 25000000,
            'qty' => 2,
            'deposit_amount' => 20000000,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Tunai',
        ], $this->user);

        $this->assertNotNull($deposit);
        $this->assertEquals('manual', $deposit->product_type);
        $this->assertNull($deposit->product_id);
        $this->assertEquals(50000000, $deposit->total_amount); // 25m * 2
        $this->assertEquals(20000000, $deposit->deposit_amount);
        $this->assertEquals(30000000, $deposit->remaining_amount);
    }

    public function test_can_update_pending_customer_deposit(): void
    {
        $createAction = new CreateCustomerDeposit();
        $deposit = $createAction->execute([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'manual',
            'product_name' => 'Gitar Akustik Taylor (PO)',
            'price' => 10000000,
            'qty' => 1,
            'deposit_amount' => 3000000,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Tunai',
        ], $this->user);

        $updateAction = new UpdateCustomerDeposit();
        $updated = $updateAction->execute($deposit, [
            'price' => 12000000,
            'qty' => 1,
            'deposit_amount' => 4000000,
            'notes' => 'Upgrade tipe ke seri 314ce',
        ], $this->user);

        $this->assertEquals(12000000, $updated->total_amount);
        $this->assertEquals(4000000, $updated->deposit_amount);
        $this->assertEquals(8000000, $updated->remaining_amount);
        $this->assertEquals(4000000, $this->customer->fresh()->deposit_balance);
    }

    public function test_settlement_moves_funds_from_penitipan_dana_to_pendapatan(): void
    {
        $createAction = new CreateCustomerDeposit();
        $deposit = $createAction->execute([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'existing',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'price' => 15000000,
            'qty' => 1,
            'deposit_amount' => 5000000,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Transfer BCA',
        ], $this->user);

        $settleAction = new SettleCustomerDeposit();
        $settled = $settleAction->execute($deposit, [
            'settlement_account_id' => $this->cashAccount->id,
            'settlement_payment_method' => 'Tunai',
            'settlement_reference' => 'LUNAS-001',
            'settlement_notes' => 'Barang diserahkan lengkap',
        ], $this->user);

        $this->assertEquals('settled', $settled->status);
        $this->assertNotNull($settled->settled_at);
        $this->assertNotNull($settled->settlement_journal_entry_id);

        // Verify Settlement Journal Entry
        $settleJournal = $settled->settlementJournalEntry;
        $this->assertNotNull($settleJournal);

        // 1. Debit Penitipan Dana = 5,000,000
        $penitipanDebit = $settleJournal->items()->where('account_id', $this->penitipanDanaAccount->id)->first();
        $this->assertNotNull($penitipanDebit);
        $this->assertEquals(5000000, $penitipanDebit->debit);
        $this->assertEquals(0, $penitipanDebit->credit);

        // 2. Debit Kas Penerimaan Sisa = 10,000,000
        $kasDebit = $settleJournal->items()->where('account_id', $this->cashAccount->id)->first();
        $this->assertNotNull($kasDebit);
        $this->assertEquals(10000000, $kasDebit->debit);
        $this->assertEquals(0, $kasDebit->credit);

        // 3. Kredit Pendapatan Penjualan = 15,000,000
        $salesCredit = $settleJournal->items()->where('account_id', $this->salesAccount->id)->first();
        $this->assertNotNull($salesCredit);
        $this->assertEquals(0, $salesCredit->debit);
        $this->assertEquals(15000000, $salesCredit->credit);

        // Customer deposit balance decremented back to 0
        $this->assertEquals(0, $this->customer->fresh()->deposit_balance);
    }

    public function test_can_cancel_customer_deposit(): void
    {
        $createAction = new CreateCustomerDeposit();
        $deposit = $createAction->execute([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'deposit_date' => now()->toDateString(),
            'product_type' => 'manual',
            'product_name' => 'Barang Batal (PO)',
            'price' => 5000000,
            'qty' => 1,
            'deposit_amount' => 2000000,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Tunai',
        ], $this->user);

        $cancelAction = new CancelCustomerDeposit();
        $cancelled = $cancelAction->execute($deposit, 'Pelanggan membatalkan pesanan', $this->user);

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertNull($cancelled->deposit_journal_entry_id);
        $this->assertEquals(0, $this->customer->fresh()->deposit_balance);
    }

    public function test_livewire_pos_customer_deposits_renders_and_functions(): void
    {
        Livewire::test(PosCustomerDeposits::class)
            ->assertStatus(200)
            ->assertSee('Deposit & Titipan Dana Pelanggan', escape: false)
            ->call('openCreateModal')
            ->assertSet('showFormModal', true)
            ->set('customer_id', $this->customer->id)
            ->set('product_type', 'manual')
            ->set('product_name', 'Livewire PO Drum Set')
            ->set('price', 8000000)
            ->set('qty', 1)
            ->set('deposit_amount', 3000000)
            ->set('account_id', $this->cashAccount->id)
            ->set('payment_method', 'Tunai')
            ->call('saveDeposit')
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('customer_deposits', [
            'customer_id' => $this->customer->id,
            'product_name' => 'Livewire PO Drum Set',
            'deposit_amount' => 3000000,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_save_deposit_exceeding_total_amount(): void
    {
        Livewire::test(PosCustomerDeposits::class)
            ->call('openCreateModal')
            ->set('customer_id', $this->customer->id)
            ->set('product_type', 'manual')
            ->set('product_name', 'Keyboard Roland')
            ->set('price', 5000000)
            ->set('qty', 1)
            ->set('deposit_amount', 6000000) // Exceeds total 5,000,000
            ->assertHasErrors(['deposit_amount'])
            ->assertSet('deposit_amount', 6000000) // Retains user input, not overwritten!
            ->call('saveDeposit')
            ->assertSet('showFormModal', true) // Modal stays open due to error
            ->assertHasErrors(['deposit_amount']);
    }
}
