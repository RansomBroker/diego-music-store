<?php

namespace Tests\Feature\Actions\Sales;

use App\Actions\Sales\SettleCustomerReceivable;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SettleCustomerReceivableTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $bankAccount;
    protected Account $piutangAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Pelunasan', 'store_name' => 'Diego Piutang']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);

        $this->bankAccount = Account::find(AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'Asset'));
        $this->piutangAccount = Account::find(AccountHelper::resolveAccountId('111301001', 'PIUTANG DAGANG', 'Asset'));
    }

    public function test_it_settles_receivable_completely_and_posts_balanced_journal(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'outstanding_debt' => 2000000,
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-REC-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 2000000,
            'grand_total' => 2000000,
            'payment_method' => 'piutang',
            'status' => 'pending',
        ]);

        // Initial sale journal with Debit Piutang
        $saleJournal = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-INIT-001',
            'date' => now()->toDateString(),
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
            'status' => 'posted',
        ]);
        JournalItem::create([
            'journal_entry_id' => $saleJournal->id,
            'account_id' => $this->piutangAccount->id,
            'debit' => 2000000,
            'credit' => 0,
        ]);

        $result = app(SettleCustomerReceivable::class)->execute($sale, [
            'amount' => 2000000,
            'account_id' => $this->bankAccount->id,
            'payment_method' => 'transfer',
            'payment_reference' => 'TRF-123',
        ]);

        $this->assertTrue($result['is_fully_paid']);
        $this->assertEquals(2000000, $result['paid']);
        $this->assertEquals('completed', $result['sale']->status);

        $journal = $result['journal_entry'];
        $this->assertNotNull($journal);

        // Debit: Bank BCA = 2.000.000
        $debitBank = $journal->items()->where('account_id', $this->bankAccount->id)->where('debit', 2000000)->first();
        $this->assertNotNull($debitBank);

        // Credit: Piutang Dagang = 2.000.000
        $creditPiutang = $journal->items()->where('account_id', $this->piutangAccount->id)->where('credit', 2000000)->first();
        $this->assertNotNull($creditPiutang);

        // Balanced
        $this->assertEquals($journal->items()->sum('debit'), $journal->items()->sum('credit'));

        // Customer debt reduced to 0
        $customer->refresh();
        $this->assertEquals(0, (float) $customer->outstanding_debt);
    }

    public function test_it_throws_exception_if_receiving_account_not_found(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-FAIL-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1000000,
            'grand_total' => 1000000,
            'payment_method' => 'piutang',
            'status' => 'pending',
        ]);

        // Sale has piutang
        $saleJournal = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-FAIL-001',
            'date' => now()->toDateString(),
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
            'status' => 'posted',
        ]);
        JournalItem::create([
            'journal_entry_id' => $saleJournal->id,
            'account_id' => $this->piutangAccount->id,
            'debit' => 1000000,
            'credit' => 0,
        ]);

        // Invalid account_id 999999
        app(SettleCustomerReceivable::class)->execute($sale, [
            'amount' => 500000,
            'account_id' => 999999,
        ]);
    }
}
