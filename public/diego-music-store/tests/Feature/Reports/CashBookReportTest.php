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

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->user = User::factory()->create([
            'name'      => 'Admin Kasir',
            'username'  => 'adminkasir',
            'email'     => 'kasir@diegomusic.com',
            'is_active' => true,
        ]);
        $this->user->assignRole('owner');

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
        Livewire::actingAs($this->user)
            ->test(CashBookReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }

    /** @test */
    public function it_includes_nine_digit_cash_and_bank_accounts_handles_or_search_and_labels_internal_transfers()
    {
        $kasBesar = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111101001',
            'name' => 'KAS',
            'classification' => 'asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        $kasKecil = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111101002',
            'name' => 'KAS Kecil',
            'classification' => 'asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        $bankBca = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111201001',
            'name' => 'BANK BCA',
            'classification' => 'asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        $shopee = Account::create([
            'branch_id' => $this->branch->id,
            'code' => '111202007',
            'name' => 'Shopee',
            'classification' => 'asset',
            'is_header' => false,
            'is_active' => true,
        ]);

        $today = now()->toDateString();
        $priorDate = now()->startOfMonth()->subDay()->toDateString();

        // 1. Initial balance before period start: Bank BCA +5.000.000
        $initEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-INIT-001',
            'date' => $priorDate,
            'description' => 'Saldo Awal Bank BCA',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $initEntry->id, 'account_id' => $bankBca->id, 'debit' => 5000000, 'credit' => 0]);

        // 2. POS sale into Kas Kecil (+1.000.000) & Shopee (+2.500.000)
        $saleEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-SALE-001',
            'date' => $today,
            'description' => 'Penjualan Harian POS',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $saleEntry->id, 'account_id' => $kasKecil->id, 'debit' => 1000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $saleEntry->id, 'account_id' => $shopee->id, 'debit' => 2500000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $saleEntry->id, 'account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 3500000]);

        // 3. Internal transfer: Kas Kecil -> KAS (400.000)
        $transferEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-TRF-001',
            'date' => $today,
            'description' => 'Setor Kas Harian',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $transferEntry->id, 'account_id' => $kasBesar->id, 'debit' => 400000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $transferEntry->id, 'account_id' => $kasKecil->id, 'debit' => 0, 'credit' => 400000]);

        $fromDate = now()->startOfMonth()->toDateString();
        $toDate = $today;

        // Consolidated across all cash & bank accounts (9-digit + legacy)
        $consolidated = (new GenerateCashBookReport())->execute($fromDate, $toDate, $this->branch->id);
        $this->assertEquals(5000000, $consolidated['initial_balance']);
        $this->assertEquals(3900000, $consolidated['total_inflow']);
        $this->assertEquals(400000, $consolidated['total_outflow']);
        $this->assertEquals(8500000, $consolidated['ending_balance']);

        $opposingLabels = array_column($consolidated['rows'], 'opposing_account');
        $this->assertContains('Transfer: KAS Kecil', $opposingLabels);
        $this->assertContains('Transfer: KAS', $opposingLabels);
        $this->assertNotContains('Penerimaan Lainnya', $opposingLabels);

        // Single account report (Bank BCA only)
        $bcaOnly = (new GenerateCashBookReport())->execute($fromDate, $toDate, $this->branch->id, $bankBca->id);
        $this->assertEquals(5000000, $bcaOnly['initial_balance']);
        $this->assertEquals(5000000, $bcaOnly['ending_balance']);

        // Search must work with OR logic: matching entry_no alone OR description alone
        $byEntryNo = (new GenerateCashBookReport())->execute($fromDate, $toDate, $this->branch->id, null, 'all', 'running_balance', 'JV-TRF-001');
        $this->assertSame(2, $byEntryNo['transaction_count']);

        $byDescription = (new GenerateCashBookReport())->execute($fromDate, $toDate, $this->branch->id, null, 'all', 'running_balance', 'Penjualan Harian POS');
        $this->assertSame(2, $byDescription['transaction_count']);

        // Livewire Filament page must populate 9-digit accounts & non-zero balances
        Livewire::actingAs($this->user)
            ->test(CashBookReport::class)
            ->assertSee('111101002 - KAS Kecil')
            ->assertSee('111202007 - Shopee')
            ->assertSee('8.500.000');
    }
}
