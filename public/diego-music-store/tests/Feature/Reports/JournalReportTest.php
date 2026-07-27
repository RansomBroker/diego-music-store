<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateJournalReport;
use App\Filament\Pages\Reports\JournalReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JournalReportTest extends TestCase
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

        Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'revenue', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_fetches_journal_entries_with_filters_correctly()
    {
        $kas = Account::where('code', '1-1000')->first();
        $rev = Account::where('code', '4-1000')->first();

        // 1. Posted Entry
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-TEST-POSTED',
            'date'        => now()->toDateString(),
            'description' => 'Penjualan Tunai Posted',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 1500000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 1500000]);

        // 2. Draft Entry
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-TEST-DRAFT',
            'date'        => now()->toDateString(),
            'description' => 'Penjualan Tunai Draft',
            'status'      => 'draft',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $kas->id, 'debit' => 500000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 500000]);

        $action = new GenerateJournalReport();

        // Test Filter Posted
        $postedReport = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), 'posted', $this->branch->id);
        $this->assertEquals(1, $postedReport['total_entries']);
        $this->assertEquals('JV-TEST-POSTED', $postedReport['entries'][0]['entry_no']);
        $this->assertEquals(1500000, $postedReport['grand_total_debit']);

        // Test Filter All
        $allReport = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), 'all', $this->branch->id);
        $this->assertEquals(2, $allReport['total_entries']);
        $this->assertEquals(2000000, $allReport['grand_total_debit']);
    }

    /** @test */
    public function it_can_render_journal_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(JournalReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Jurnal Umum (Journal Report)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(JournalReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
