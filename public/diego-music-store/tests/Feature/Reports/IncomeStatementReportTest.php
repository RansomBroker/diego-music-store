<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateIncomeStatementReport;
use App\Filament\Pages\Reports\IncomeStatement;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IncomeStatementReportTest extends TestCase
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

        // Seed basic accounts following standard COA numbering
        $rev = Account::create(['code' => '4-0000', 'name' => 'PENDAPATAN', 'classification' => 'revenue', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'revenue', 'is_header' => false, 'parent_id' => $rev->id, 'is_active' => true]);

        $cogs = Account::create(['code' => '5-0000', 'name' => 'HARGA POKOK PENJUALAN', 'classification' => 'expense', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '5-1000', 'name' => 'HPP Produk', 'classification' => 'expense', 'is_header' => false, 'parent_id' => $cogs->id, 'is_active' => true]);

        $exp = Account::create(['code' => '6-0000', 'name' => 'BEBAN OPERASIONAL', 'classification' => 'expense', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '6-1000', 'name' => 'Beban Operasional', 'classification' => 'expense', 'is_header' => false, 'parent_id' => $exp->id, 'is_active' => true]);

        $kas = Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_multi_step_income_statement_correctly()
    {
        $pendapatan = Account::where('code', '4-1000')->first();
        $hpp        = Account::where('code', '5-1000')->first();
        $beban      = Account::where('code', '6-1000')->first();
        $kas        = Account::where('code', '1-1000')->first();

        // Transaction 1: Penjualan Rp 10.000.000 (Debit Kas, Credit Pendapatan)
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-REV-001',
            'date'        => now()->toDateString(),
            'description' => 'Penjualan Barang',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 10000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $pendapatan->id, 'debit' => 0, 'credit' => 10000000]);

        // Transaction 2: HPP Rp 6.000.000
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-COGS-001',
            'date'        => now()->toDateString(),
            'description' => 'HPP Penjualan',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $hpp->id, 'debit' => 6000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $kas->id, 'debit' => 0, 'credit' => 6000000]);

        // Transaction 3: Beban Operasional Rp 1.500.000
        $entry3 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-EXP-001',
            'date'        => now()->toDateString(),
            'description' => 'Beban Operasional Gaji',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry3->id, 'account_id' => $beban->id, 'debit' => 1500000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry3->id, 'account_id' => $kas->id, 'debit' => 0, 'credit' => 1500000]);

        $action = new GenerateIncomeStatementReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id, 'detail', 'all');

        // Verification:
        // Revenue = 10M, COGS = 6M => Gross Profit = 4M.
        // Operating Expense = 1.5M => Operating Income = 2.5M.
        // Net Income = 2.5M. Is Profit = true.

        $this->assertEquals(10000000, $report['revenue']['total']);
        $this->assertEquals(6000000, $report['cogs']['total']);
        $this->assertEquals(4000000, $report['gross_profit']);
        $this->assertEquals(1500000, $report['operating_expenses']['total']);
        $this->assertEquals(2500000, $report['operating_income']);
        $this->assertEquals(2500000, $report['net_income']);
        $this->assertTrue($report['is_profit']);
    }

    /** @test */
    public function it_can_render_income_statement_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(IncomeStatement::class)
            ->assertStatus(200)
            ->assertSee('Laporan Laba Rugi (Income Statement)');
    }

    /** @test */
    public function it_can_open_drill_down_transaction_ledger_modal()
    {
        $pendapatan = Account::where('code', '4-1000')->first();
        $kas        = Account::where('code', '1-1000')->first();

        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-REV-DRILL',
            'date'        => now()->toDateString(),
            'description' => 'Penjualan Penjualan Drill',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 3000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $pendapatan->id, 'debit' => 0, 'credit' => 3000000]);

        Livewire::actingAs($this->user)
            ->test(IncomeStatement::class)
            ->call('openAccountLedgerModal', $pendapatan->id)
            ->assertSet('showLedgerModal', true)
            ->assertSet('selectedAccount.id', $pendapatan->id)
            ->assertSee('Penjualan Penjualan Drill')
            ->assertSee('3.000.000');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(IncomeStatement::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
