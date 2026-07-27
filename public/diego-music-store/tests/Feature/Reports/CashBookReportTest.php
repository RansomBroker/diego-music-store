<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateCashBookReport;
use App\Filament\Pages\Reports\CashBookReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashBookReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $revenueAccount;

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
            'name'      => 'Admin Kasir',
            'username'  => 'adminkasir',
            'email'     => 'kasir@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $this->cashAccount = Account::create([
            'code'           => '1-1001',
            'name'           => 'Kas Utama Toko',
            'classification' => 'asset',
            'is_header'      => false,
            'is_active'      => true,
        ]);

        $this->revenueAccount = Account::create([
            'code'           => '4-1000',
            'name'           => 'Pendapatan Penjualan Alat Musik',
            'classification' => 'revenue',
            'is_header'      => false,
            'is_active'      => true,
        ]);
    }

    /** @test */
    public function it_calculates_cash_book_initial_balance_inflows_and_running_balance_correctly()
    {
        // 1. Initial Balance Entry (Month Before): 5.000.000
        $oldEntry = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-OLD-001',
            'date'        => now()->subDays(40)->toDateString(),
            'description' => 'Saldo Awal Bulan Lalu',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $oldEntry->id,
            'account_id'       => $this->cashAccount->id,
            'debit'            => 5000000,
            'credit'           => 0,
        ]);

        // 2. Current Month Cash Inflow Entry: 2.000.000
        $currentEntry = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-CUR-001',
            'date'        => now()->startOfMonth()->toDateString(),
            'description' => 'Penerimaan Penjualan Tunai',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $currentEntry->id,
            'account_id'       => $this->cashAccount->id,
            'debit'            => 2000000,
            'credit'           => 0,
        ]);

        JournalItem::create([
            'journal_entry_id' => $currentEntry->id,
            'account_id'       => $this->revenueAccount->id,
            'debit'            => 0,
            'credit'           => 2000000,
        ]);

        $action = new GenerateCashBookReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id, $this->cashAccount->id);

        $this->assertEquals(5000000, $report['initial_balance']);
        $this->assertEquals(2000000, $report['total_inflow']);
        $this->assertEquals(0, $report['total_outflow']);
        $this->assertEquals(7000000, $report['ending_balance']);
        $this->assertEquals(1, count($report['rows']));
    }

    /** @test */
    public function it_can_render_cash_book_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(CashBookReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Kas & Bank (Cash Book)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(CashBookReport::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
