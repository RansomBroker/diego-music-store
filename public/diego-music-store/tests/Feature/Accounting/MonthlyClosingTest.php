<?php

namespace Tests\Feature\Accounting;

use App\Actions\Accounting\ExecuteMonthlyClosing;
use App\Actions\Accounting\ReopenMonthlyClosing;
use App\Filament\Pages\Accounting\MonthlyClosingPage;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\MonthlyClosing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MonthlyClosingTest extends TestCase
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

        Account::create(['code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'classification' => 'revenue', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '5-1000', 'name' => 'HPP Produk', 'classification' => 'expense', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '3-2000', 'name' => 'Laba Ditahan', 'classification' => 'equity', 'is_header' => false, 'is_active' => true]);
        Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]);
    }

    /** @test */
    public function it_executes_monthly_closing_and_generates_closing_journal_entry()
    {
        $rev      = Account::where('code', '4-1000')->first();
        $hpp      = Account::where('code', '5-1000')->first();
        $retained = Account::where('code', '3-2000')->first();
        $kas      = Account::where('code', '1-1000')->first();

        // 1. Transaction: Revenue Rp 10.000.000
        $entry1 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-REV-CLOSE',
            'date'        => now()->startOfMonth()->toDateString(),
            'description' => 'Penjualan Penjualan',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $kas->id, 'debit' => 10000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry1->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 10000000]);

        // 2. Transaction: HPP Rp 6.000.000
        $entry2 = JournalEntry::create([
            'branch_id'   => $this->branch->id,
            'entry_no'    => 'JV-HPP-CLOSE',
            'date'        => now()->startOfMonth()->toDateString(),
            'description' => 'HPP Produk',
            'status'      => 'posted',
            'created_by'  => $this->user->id,
        ]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $hpp->id, 'debit' => 6000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry2->id, 'account_id' => $kas->id, 'debit' => 0, 'credit' => 6000000]);

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('m');

        $action = new ExecuteMonthlyClosing();
        $closing = $action->execute($year, $month, $this->branch->id, $this->user->id);

        // Verification:
        // MonthlyClosing status = closed
        // Total Revenue = 10M, Total Expense = 6M, Net Income = 4M.
        // Closing Journal Entry created and credited 4M to Laba Ditahan (3-2000).

        $this->assertEquals('closed', $closing->status);
        $this->assertEquals(10000000, $closing->total_revenue);
        $this->assertEquals(6000000, $closing->total_expense);
        $this->assertEquals(4000000, $closing->net_income);
        $this->assertNotNull($closing->closing_journal_id);

        $this->assertTrue(MonthlyClosing::isPeriodClosed(now()->toDateString(), $this->branch->id));
    }

    /** @test */
    public function it_can_reopen_closed_monthly_period()
    {
        $year  = (int) now()->format('Y');
        $month = (int) now()->format('m');

        $executeAction = new ExecuteMonthlyClosing();
        $closing = $executeAction->execute($year, $month, $this->branch->id, $this->user->id);

        $reopenAction = new ReopenMonthlyClosing();
        $reopenedClosing = $reopenAction->execute($closing, $this->user->id);

        $this->assertEquals('reopened', $reopenedClosing->status);
        $this->assertNull($reopenedClosing->closing_journal_id);
        $this->assertFalse(MonthlyClosing::isPeriodClosed(now()->toDateString(), $this->branch->id));
    }

    /** @test */
    public function it_can_render_monthly_closing_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(MonthlyClosingPage::class)
            ->assertStatus(200)
            ->assertSee('Tutup Buku Bulanan (Monthly Closing)');
    }
}
