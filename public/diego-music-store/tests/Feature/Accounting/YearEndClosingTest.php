<?php

namespace Tests\Feature\Accounting;

use App\Actions\Accounting\ExecuteYearEndClosing;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class YearEndClosingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $currentYearAcc;
    protected Account $retainedAcc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'       => 'Cabang Utama',
            'store_name' => 'Diego Music Store Main',
            'is_active'  => true,
        ]);

        $this->user = User::factory()->create([
            'name'      => 'Admin Finance',
            'username'  => 'adminfinance',
            'email'     => 'finance@diegomusic.com',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);

        // Setup Equity Accounts
        $this->currentYearAcc = Account::create([
            'code'           => '311301001',
            'name'           => 'LABA TAHUN BERJALAN',
            'classification' => 'equity',
            'normal_balance' => 'credit',
            'is_header'      => false,
            'is_active'      => true,
        ]);

        $this->retainedAcc = Account::create([
            'code'           => '311201001',
            'name'           => 'LABA DITAHAN',
            'classification' => 'equity',
            'normal_balance' => 'credit',
            'is_header'      => false,
            'is_active'      => true,
        ]);
    }

    /** @test */
    public function it_transfers_profit_from_laba_tahun_berjalan_to_laba_ditahan_at_year_end()
    {
        $year = 2026;

        // Simulate cumulative net profit of 25.000.000 accumulated in Laba Tahun Berjalan during 2026
        $mockEntry = JournalEntry::create([
            'branch_id'      => $this->branch->id,
            'entry_no'       => 'JV-MOCK-MONTHLY',
            'date'           => "{$year}-06-30",
            'description'    => 'Akumulasi Laba Bulanan 2026',
            'status'         => 'posted',
            'created_by'     => $this->user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $mockEntry->id,
            'account_id'       => $this->currentYearAcc->id,
            'debit'            => 0,
            'credit'           => 25000000,
            'notes'            => 'Laba Bersih Semester 1',
        ]);

        // Execute Year-End Closing
        $action = new ExecuteYearEndClosing();
        $closingJournal = $action->execute($year, $this->branch->id, $this->user->id);

        $this->assertNotNull($closingJournal);
        $this->assertEquals("JV-YEAREND-{$year}-B{$this->branch->id}", $closingJournal->entry_no);
        $this->assertEquals("{$year}-12-31", $closingJournal->date->format('Y-m-d'));
        $this->assertEquals('YearEndClosing', $closingJournal->reference_type);

        // Verification of Journal Items:
        // 1. Debit Laba Tahun Berjalan 25.000.000 (Menolkan saldo tahun berjalan)
        $debitItem = $closingJournal->items()->where('account_id', $this->currentYearAcc->id)->first();
        $this->assertNotNull($debitItem);
        $this->assertEquals(25000000, $debitItem->debit);
        $this->assertEquals(0, $debitItem->credit);

        // 2. Credit Laba Ditahan 25.000.000 (Menambah akumulasi Laba Ditahan)
        $creditItem = $closingJournal->items()->where('account_id', $this->retainedAcc->id)->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals(0, $creditItem->debit);
        $this->assertEquals(25000000, $creditItem->credit);
    }

    /** @test */
    public function it_transfers_net_loss_correctly_at_year_end()
    {
        $year = 2026;

        // Simulate net loss of 10.000.000 in Laba Tahun Berjalan (Debit balance)
        $mockEntry = JournalEntry::create([
            'branch_id'      => $this->branch->id,
            'entry_no'       => 'JV-MOCK-LOSS',
            'date'           => "{$year}-11-30",
            'description'    => 'Akumulasi Rugi 2026',
            'status'         => 'posted',
            'created_by'     => $this->user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $mockEntry->id,
            'account_id'       => $this->currentYearAcc->id,
            'debit'            => 10000000,
            'credit'           => 0,
            'notes'            => 'Rugi Bersih Operasional',
        ]);

        $action = new ExecuteYearEndClosing();
        $closingJournal = $action->execute($year, $this->branch->id, $this->user->id);

        $this->assertNotNull($closingJournal);

        // Debit Laba Ditahan (mengurangi saldo ditahan)
        $retainedItem = $closingJournal->items()->where('account_id', $this->retainedAcc->id)->first();
        $this->assertNotNull($retainedItem);
        $this->assertEquals(10000000, $retainedItem->debit);

        // Credit Laba Tahun Berjalan (menolkan saldo debit)
        $currentYearItem = $closingJournal->items()->where('account_id', $this->currentYearAcc->id)->first();
        $this->assertNotNull($currentYearItem);
        $this->assertEquals(10000000, $currentYearItem->credit);
    }

    /** @test */
    public function it_prevents_duplicate_year_end_closing()
    {
        $year = 2026;

        $mockEntry = JournalEntry::create([
            'branch_id'      => $this->branch->id,
            'entry_no'       => 'JV-MOCK-DUP',
            'date'           => "{$year}-05-01",
            'description'    => 'Laba',
            'status'         => 'posted',
            'created_by'     => $this->user->id,
        ]);
        JournalItem::create([
            'journal_entry_id' => $mockEntry->id,
            'account_id'       => $this->currentYearAcc->id,
            'debit'            => 0,
            'credit'           => 5000000,
        ]);

        $action = new ExecuteYearEndClosing();
        $action->execute($year, $this->branch->id, $this->user->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Tutup buku akhir tahun {$year} sudah pernah dijalankan");

        $action->execute($year, $this->branch->id, $this->user->id);
    }

    /** @test */
    public function it_can_be_executed_via_artisan_command()
    {
        $year = 2026;

        $mockEntry = JournalEntry::create([
            'branch_id'      => null,
            'entry_no'       => 'JV-MOCK-ARTISAN',
            'date'           => "{$year}-08-15",
            'description'    => 'Laba Konsolidasi',
            'status'         => 'posted',
            'created_by'     => $this->user->id,
        ]);
        JournalItem::create([
            'journal_entry_id' => $mockEntry->id,
            'account_id'       => $this->currentYearAcc->id,
            'debit'            => 0,
            'credit'           => 12000000,
        ]);

        $exitCode = Artisan::call('app:year-end-closing', [
            '--year' => $year,
        ]);

        $this->assertEquals(0, $exitCode);

        $closing = JournalEntry::where('entry_no', "JV-YEAREND-{$year}")->first();
        $this->assertNotNull($closing);
        $this->assertEquals('YearEndClosing', $closing->reference_type);
    }
}
