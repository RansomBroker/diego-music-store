<?php

namespace Tests\Feature\Actions;

use App\Actions\CashManagement\CreateCashTransaction;
use App\Actions\CashManagement\UpdateCashTransaction;
use App\Actions\CashManagement\PostCashTransaction;
use App\Models\Branch;
use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashTransactionActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Account $cashAccount;
    private Account $expenseAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Kuta',
            'address' => 'Kuta',
            'phone' => '0361-99999',
            'is_active' => true,
        ]);

        $this->cashAccount = Account::create([
            'code' => '1-1010',
            'name' => 'Kas Kecil',
            'classification' => 'asset',
            'is_active' => true,
        ]);

        $this->expenseAccount = Account::create([
            'code' => '6-1000',
            'name' => 'Beban ATK',
            'classification' => 'expense',
            'is_active' => true,
        ]);
    }

    public function test_it_can_create_update_and_post_cash_transaction(): void
    {
        // 1. Create Cash Out (Kas Keluar)
        $data = [
            'branch_id' => $this->branch->id,
            'type' => 'out',
            'transaction_date' => '2026-07-04',
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'amount' => 50000,
            'notes' => 'Beli lakban packing',
        ];

        $tx = app(CreateCashTransaction::class)->execute($data);

        $this->assertInstanceOf(CashTransaction::class, $tx);
        $this->assertEquals('draft', $tx->status);
        $this->assertEquals(50000, $tx->amount);
        $this->assertStringStartsWith('CSH-', $tx->transaction_no);

        // 2. Update Transaction
        $updateData = [
            'amount' => 60000,
            'notes' => 'Beli lakban packing premium',
        ];

        $updated = app(UpdateCashTransaction::class)->execute($tx, $updateData);
        $this->assertEquals(60000, $updated->amount);
        $this->assertEquals('Beli lakban packing premium', $updated->notes);

        // 3. Post Transaction
        $posted = app(PostCashTransaction::class)->execute($updated);
        $this->assertEquals('posted', $posted->status);
        $this->assertNotNull($posted->posted_at);

        // 4. Assert Journal Entry is created and balances
        $journal = JournalEntry::where('reference_type', 'CashTransaction')
            ->where('reference_id', $posted->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals($this->branch->id, $journal->branch_id);

        $debitTotal = $journal->items()->sum('debit');
        $creditTotal = $journal->items()->sum('credit');
        $this->assertEquals(60000, $debitTotal);
        $this->assertEquals($debitTotal, $creditTotal);

        // Debit: Beban ATK (destination_account_id)
        $debitItem = $journal->items()->where('account_id', $this->expenseAccount->id)->first();
        $this->assertEquals(60000, $debitItem->debit);
        $this->assertEquals(0, $debitItem->credit);

        // Credit: Kas Kecil (source_account_id)
        $creditItem = $journal->items()->where('account_id', $this->cashAccount->id)->first();
        $this->assertEquals(60000, $creditItem->credit);
        $this->assertEquals(0, $creditItem->debit);
    }
}
