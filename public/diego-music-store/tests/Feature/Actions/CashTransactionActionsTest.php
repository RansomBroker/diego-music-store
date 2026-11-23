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
        $this->user->branches()->attach($this->branch->id);

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

    public function test_it_can_delete_posted_cash_transaction_and_revert_journal_and_session_expected_cash(): void
    {
        $session = \App\Models\CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $tx = app(CreateCashTransaction::class)->execute([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'type' => 'out',
            'transaction_date' => now()->toDateString(),
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'amount' => 75000,
            'notes' => 'Beli galon',
            'status' => 'posted',
        ]);

        $journalId = $tx->journal_entry_id;
        $this->assertNotNull($journalId);
        $this->assertDatabaseHas('journal_entries', ['id' => $journalId]);
        $this->assertDatabaseHas('journal_items', ['journal_entry_id' => $journalId]);

        app(\App\Actions\CashManagement\DeleteCashTransaction::class)->execute($tx);

        $this->assertDatabaseMissing('cash_transactions', ['id' => $tx->id]);
        $this->assertDatabaseMissing('journal_entries', ['id' => $journalId]);
        $this->assertDatabaseMissing('journal_items', ['journal_entry_id' => $journalId]);
        $this->assertSame(500000, (int) $session->fresh()->expected_cash);
    }

    public function test_it_can_edit_posted_cash_transaction_and_updates_journal_entries_and_cash_session(): void
    {
        $secondExpense = Account::create([
            'code' => '6-2000',
            'name' => 'Beban Listrik',
            'classification' => 'expense',
            'is_active' => true,
        ]);

        $session = \App\Models\CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $tx = app(CreateCashTransaction::class)->execute([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'type' => 'out',
            'transaction_date' => '2026-11-22',
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'amount' => 50000,
            'notes' => 'Beli ATK awal',
            'status' => 'posted',
        ]);

        $originalJournalId = $tx->journal_entry_id;
        $originalEntryNo = JournalEntry::find($originalJournalId)->entry_no;

        // Edit transaksi yang sudah posted: ubah nominal, akun tujuan, tanggal, dan catatan
        $updated = app(UpdateCashTransaction::class)->execute($tx, [
            'transaction_date' => '2026-11-23',
            'destination_account_id' => $secondExpense->id,
            'amount' => 125000,
            'notes' => 'Bayar token listrik',
            'status' => 'posted',
        ]);

        $this->assertSame('posted', $updated->status);
        $this->assertSame(125000, (int) $updated->amount);
        $this->assertSame($originalJournalId, $updated->journal_entry_id);
        $this->assertSame(1, JournalEntry::where('reference_type', 'CashTransaction')->where('reference_id', $tx->id)->count());

        $journal = JournalEntry::with('items')->find($originalJournalId);
        $this->assertSame($originalEntryNo, $journal->entry_no);
        $this->assertSame('2026-11-23', $journal->date->format('Y-m-d'));

        // Akun beban lama sudah tidak ada di journal items; diganti akun beban baru dengan nominal 125.000
        $this->assertDatabaseMissing('journal_items', [
            'journal_entry_id' => $originalJournalId,
            'account_id' => $this->expenseAccount->id,
        ]);
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $originalJournalId,
            'account_id' => $secondExpense->id,
            'debit' => 125000,
            'credit' => 0,
            'notes' => 'Bayar token listrik',
        ]);
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $originalJournalId,
            'account_id' => $this->cashAccount->id,
            'debit' => 0,
            'credit' => 125000,
            'notes' => 'Bayar token listrik',
        ]);

        // Ekspektasi kas laci sesi ikut ter-update (500.000 - 125.000 = 375.000)
        $this->assertSame(375000, (int) $session->fresh()->expected_cash);
    }
}
