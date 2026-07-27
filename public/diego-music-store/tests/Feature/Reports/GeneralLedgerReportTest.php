<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateGeneralLedgerReport;
use App\Filament\Pages\Reports\GeneralLedger;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralLedgerReportTest extends TestCase
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

        // Seed basic accounts
        Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '3-1000', 'name' => 'Modal Pemilik', 'classification' => 'equity', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_beginning_balance_and_running_balance_correctly()
    {
        $kas   = Account::where('code', '1-1000')->first();
        $modal = Account::where('code', '3-1000')->first();

        $priorDate  = now()->startOfMonth()->subDays(5)->toDateString();
        $periodDate = now()->toDateString();

        // 1. Transaction BEFORE period (Sets Beginning Balance = 5.000.000 for Kas)
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-PRIOR-001',
            'date'        => $priorDate,
            'description' => 'Setor Modal Awal Lalu',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 5000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $modal->id, 'debit' => 0, 'credit' => 5000000]);

        // 2. Transaction WITHIN period (Adds 2.000.000 debit to Kas)
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-PERIOD-001',
            'date'        => $periodDate,
            'description' => 'Terima Kas Periode Ini',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $kas->id, 'debit' => 2000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $modal->id, 'debit' => 0, 'credit' => 2000000]);

        $action = new GenerateGeneralLedgerReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $kas->id, $this->branch->id);

        $kasLedger = $report['ledgers'][0];

        // Verification:
        // Beginning Balance = 5M.
        // Period Debit = 2M.
        // Ending Balance = 7M.

        $this->assertEquals(5000000, $kasLedger['beginning_balance']);
        $this->assertEquals(2000000, $kasLedger['total_debit']);
        $this->assertEquals(7000000, $kasLedger['ending_balance']);
        $this->assertCount(1, $kasLedger['transactions']);
        $this->assertEquals('JV-PERIOD-001', $kasLedger['transactions'][0]['entry_no']);
    }

    /** @test */
    public function it_can_render_general_ledger_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(GeneralLedger::class)
            ->assertStatus(200)
            ->assertSee('Laporan Buku Besar (General Ledger)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(GeneralLedger::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
