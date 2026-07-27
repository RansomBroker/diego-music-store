<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateBankLedgerReport;
use App\Filament\Pages\Reports\BankLedger;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BankLedgerReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'        => 'Cabang Utama',
            'store_name'  => 'Diego Music Store Main',
            'address'     => 'Jl. Musik No. 123',
            'phone'       => '081234567890',
            'is_active'   => true,
        ]);

        $this->user = User::factory()->create([
            'name'      => 'Admin Akuntansi',
            'username'  => 'adminfinance',
            'email'     => 'finance@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        Account::create(['code' => '1-1100', 'name' => 'Bank BCA Utama', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'revenue', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_bank_beginning_and_ending_balances_correctly()
    {
        $bank = Account::where('code', '1-1100')->first();
        $rev  = Account::where('code', '4-1000')->first();

        $priorDate  = now()->startOfMonth()->subDays(5)->toDateString();
        $periodDate = now()->toDateString();

        // 1. Prior Bank Transfer (Sets Beginning Balance = 10.000.000)
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-BANK-PRIOR',
            'date'        => $priorDate,
            'description' => 'Transfer Modal BCA Lalu',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $bank->id, 'debit' => 10000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 10000000]);

        // 2. Period Transfer In (+ 2.500.000)
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-BANK-IN',
            'date'        => $periodDate,
            'description' => 'Pelunasan Transfer BCA',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $bank->id, 'debit' => 2500000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 2500000]);

        $action = new GenerateBankLedgerReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $bank->id, $this->branch->id, 'all');

        $bankLedger = $report['ledgers'][0];

        // Verification:
        // Beginning Balance = 10M.
        // Total In = 2.5M. Total Out = 0.
        // Ending Balance = 12.5M.

        $this->assertEquals(10000000, $bankLedger['beginning_balance']);
        $this->assertEquals(2500000, $bankLedger['total_in']);
        $this->assertEquals(0, $bankLedger['total_out']);
        $this->assertEquals(12500000, $bankLedger['ending_balance']);
    }

    /** @test */
    public function it_can_render_bank_ledger_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(BankLedger::class)
            ->assertStatus(200)
            ->assertSee('Laporan Buku Bank (Bank Ledger)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(BankLedger::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
