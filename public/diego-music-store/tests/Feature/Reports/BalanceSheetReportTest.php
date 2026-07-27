<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateBalanceSheetReport;
use App\Filament\Pages\Reports\BalanceSheet;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BalanceSheetReportTest extends TestCase
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
        $aset = Account::create(['code' => '1-0000', 'name' => 'ASET', 'classification' => 'asset', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'asset', 'is_header' => false, 'parent_id' => $aset->id, 'is_active' => true]);
        Account::create(['code' => '1-1200', 'name' => 'Piutang Dagang', 'classification' => 'asset', 'is_header' => false, 'parent_id' => $aset->id, 'is_active' => true]);

        $liab = Account::create(['code' => '2-0000', 'name' => 'LIABILITAS', 'classification' => 'liability', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '2-1000', 'name' => 'Hutang Dagang', 'classification' => 'liability', 'is_header' => false, 'parent_id' => $liab->id, 'is_active' => true]);

        $eq = Account::create(['code' => '3-0000', 'name' => 'EKUITAS', 'classification' => 'equity', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '3-1000', 'name' => 'Modal Pemilik', 'classification' => 'equity', 'is_header' => false, 'parent_id' => $eq->id, 'is_active' => true]);

        $rev = Account::create(['code' => '4-0000', 'name' => 'PENDAPATAN', 'classification' => 'revenue', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'revenue', 'is_header' => false, 'parent_id' => $rev->id, 'is_active' => true]);

        $exp = Account::create(['code' => '5-0000', 'name' => 'BEBAN', 'classification' => 'expense', 'is_header' => true, 'is_active' => true]);
        Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'classification' => 'expense', 'is_header' => false, 'parent_id' => $exp->id, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_skontro_balance_sheet_correctly_with_sub_classifications()
    {
        $kas = Account::where('code', '1-1000')->first();
        $modal = Account::where('code', '3-1000')->first();
        $pendapatan = Account::where('code', '4-1000')->first();
        $beban = Account::where('code', '5-1000')->first();

        // Transaction 1: Modal Awal Kas Rp 10.000.000 (Debit Kas, Credit Modal)
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-TEST-001',
            'date'        => now()->toDateString(),
            'description' => 'Setor Modal Awal',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 10000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $modal->id, 'debit' => 0, 'credit' => 10000000]);

        // Action calculation in Detail mode
        $action = new GenerateBalanceSheetReport();
        $detailReport = $action->execute(now()->toDateString(), $this->branch->id, 'detail', 'all');

        $this->assertEquals(10000000, $detailReport['assets']['total_current_assets']);
        $this->assertEquals(10000000, $detailReport['total_assets']);
        $this->assertEquals(0, $detailReport['total_liabilities']);
        $this->assertEquals(10000000, $detailReport['total_equity']);
        $this->assertTrue($detailReport['is_balanced']);

        // Action calculation in Summary mode (only header accounts)
        $summaryReport = $action->execute(now()->toDateString(), $this->branch->id, 'summary', 'all');
        $this->assertCount(1, $summaryReport['assets']['items']); // Only root ASET header
        $this->assertTrue($summaryReport['assets']['items'][0]['is_header']);
    }

    /** @test */
    public function it_can_render_balance_sheet_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(BalanceSheet::class)
            ->assertStatus(200)
            ->assertSee('Laporan Balance Sheet (Neraca)')
            ->assertSee('NERACA SEIMBANG');
    }

    /** @test */
    public function it_can_open_drill_down_transaction_ledger_modal()
    {
        $kas = Account::where('code', '1-1000')->first();
        $modal = Account::where('code', '3-1000')->first();

        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-DRILL-001',
            'date'        => now()->toDateString(),
            'description' => 'Modal Kas Drill Down',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 2000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $modal->id, 'debit' => 0, 'credit' => 2000000]);

        Livewire::actingAs($this->user)
            ->test(BalanceSheet::class)
            ->call('openAccountLedgerModal', $kas->id)
            ->assertSet('showLedgerModal', true)
            ->assertSet('selectedAccount.id', $kas->id)
            ->assertSee('Modal Kas Drill Down')
            ->assertSee('2.000.000');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(BalanceSheet::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
