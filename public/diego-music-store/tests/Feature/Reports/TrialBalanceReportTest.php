<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateTrialBalanceReport;
use App\Filament\Pages\Reports\TrialBalance;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrialBalanceReportTest extends TestCase
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
            'name'      => 'Admin Neraca Saldo',
            'username'  => 'adminneraca',
            'email'     => 'neraca@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'Asset', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_6_column_trial_balance_correctly()
    {
        $kas = Account::where('code', '1-1000')->first();
        $rev = Account::where('code', '4-1000')->first();

        $priorDate  = now()->startOfMonth()->subDays(5)->toDateString();
        $periodDate = now()->toDateString();

        // 1. Transaction BEFORE period (Sets Beginning Balance = 1.000.000)
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-TB-001',
            'date'        => $priorDate,
            'description' => 'Penjualan Lalu',
            'status'      => 'posted',
        ]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 1000000, 'credit' => 0]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 1000000]);

        // 2. Transaction WITHIN period (Mutasi = 500.000)
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-TB-002',
            'date'        => $periodDate,
            'description' => 'Penjualan Periode Ini',
            'status'      => 'posted',
        ]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $kas->id, 'debit' => 500000, 'credit' => 0]);
        \App\Models\JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 500000]);

        $action = new GenerateTrialBalanceReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id, false, 'all');

        $this->assertEquals(1000000, $report['total_beginning_debit']);
        $this->assertEquals(1000000, $report['total_beginning_credit']);
        $this->assertEquals(500000, $report['total_period_debit']);
        $this->assertEquals(500000, $report['total_period_credit']);
        $this->assertEquals(1500000, $report['total_ending_debit']);
        $this->assertEquals(1500000, $report['total_ending_credit']);
        $this->assertTrue($report['is_balanced']);
    }

    /** @test */
    public function it_can_render_trial_balance_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(TrialBalance::class)
            ->assertStatus(200)
            ->assertSee('Laporan Neraca Saldo (Trial Balance 6-Kolom)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(TrialBalance::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
