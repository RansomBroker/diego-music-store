<?php

namespace Tests\Feature\Actions;

use App\Actions\Accounting\CreateScheduledJournalEntry;
use App\Actions\Accounting\UpdateScheduledJournalEntry;
use App\Actions\Accounting\ProcessScheduledJournalEntries;
use App\Models\Branch;
use App\Models\Account;
use App\Models\ScheduledJournalEntry;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Tests\TestCase;

class ScheduledJournalActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Account $cashAccount;
    private Account $expenseAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $this->cashAccount = Account::create(['code' => '1-1000', 'name' => 'Kas Utama', 'classification' => 'Asset']);
        $this->expenseAccount = Account::create(['code' => '6-1000', 'name' => 'Beban Operasional', 'classification' => 'Expense']);
    }

    public function test_it_can_create_and_update_scheduled_journal_entry(): void
    {
        $data = [
            'branch_id' => $this->branch->id,
            'start_date' => '2026-03-12',
            'frequency' => 'monthly',
            'interval' => 1,
            'duration_months' => 6,
            'description' => 'Penyusutan Aset Bulanan',
            'status' => 'active',
            'items' => [
                [
                    'account_id' => $this->expenseAccount->id,
                    'debit' => 150000,
                    'credit' => 0,
                    'notes' => 'Debit beban',
                ],
                [
                    'account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => 150000,
                    'notes' => 'Kredit kas',
                ]
            ]
        ];

        // 1. Test creation
        $entry = app(CreateScheduledJournalEntry::class)->execute($data);

        $this->assertInstanceOf(ScheduledJournalEntry::class, $entry);
        $this->assertEquals('active', $entry->status);
        $this->assertEquals('2026-03-12', $entry->start_date->format('Y-m-d'));
        $this->assertEquals('2026-09-12', $entry->end_date->format('Y-m-d'));
        $this->assertEquals('2026-03-12', $entry->next_run_at->format('Y-m-d'));
        $this->assertCount(2, $entry->items);

        // 2. Test balance validation
        $unbalancedData = $data;
        $unbalancedData['items'][1]['credit'] = 140000;

        $this->expectException(ValidationException::class);
        app(CreateScheduledJournalEntry::class)->execute($unbalancedData);
    }

    public function test_it_can_process_scheduled_journal_entries(): void
    {
        // Set fixed clock date to 2026-07-09
        Carbon::setTestNow(Carbon::parse('2026-07-09'));

        $data = [
            'branch_id' => $this->branch->id,
            'start_date' => '2026-06-12', // in the past relative to 2026-07-09
            'frequency' => 'monthly',
            'interval' => 1,
            'duration_months' => 3, // runs on 12 Jun, 12 Jul, 12 Aug (end_date: 12 Sep)
            'description' => 'Penyusutan Aset Bulanan',
            'status' => 'active',
            'items' => [
                [
                    'account_id' => $this->expenseAccount->id,
                    'debit' => 200000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => 200000,
                ]
            ]
        ];

        $entry = app(CreateScheduledJournalEntry::class)->execute($data);
        $this->assertEquals('2026-06-12', $entry->next_run_at->format('Y-m-d'));

        // Process scheduled journals
        $count = app(ProcessScheduledJournalEntries::class)->execute();

        // On 2026-07-09, the next_run_at is 2026-06-12 (which is <= 2026-07-09)
        // It should run and update next_run_at to 2026-07-12, which is in the future.
        // So it should generate exactly 1 journal entry.
        $this->assertEquals(1, $count);

        $entry->refresh();
        $this->assertEquals('2026-06-12', $entry->last_run_at->format('Y-m-d'));
        $this->assertEquals('2026-07-12', $entry->next_run_at->format('Y-m-d'));
        $this->assertEquals('active', $entry->status);

        // Verify JournalEntry is created in database
        $createdJournal = JournalEntry::where('reference_type', 'ScheduledJournalEntry')
            ->where('reference_id', $entry->id)
            ->first();
        
        $this->assertNotNull($createdJournal);
        $this->assertEquals('2026-06-12', $createdJournal->date->format('Y-m-d'));
        $this->assertEquals('posted', $createdJournal->status);
        $this->assertEquals(200000, $createdJournal->items()->sum('debit'));

        // Move clock forward to 2026-08-15 (covers both 12 Jul and 12 Aug runs)
        Carbon::setTestNow(Carbon::parse('2026-08-15'));
        
        $count2 = app(ProcessScheduledJournalEntries::class)->execute();
        
        // On 2026-08-15, it should run for:
        // - 12 Jul (moves to 12 Aug)
        // - 12 Aug (moves to 12 Sep)
        // Total 2 runs
        $this->assertEquals(2, $count2);

        $entry->refresh();
        // Since duration is 3 months and next run date becomes 12 Sep (equal to end_date: 12 Sep),
        // it should complete and clear next_run_at.
        $this->assertEquals('2026-08-12', $entry->last_run_at->format('Y-m-d'));
        $this->assertEquals('completed', $entry->status);
        $this->assertNull($entry->next_run_at);

        Carbon::setTestNow(); // Reset clock
    }

    public function test_it_can_run_manually(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09'));

        $data = [
            'branch_id' => $this->branch->id,
            'start_date' => '2026-08-12', // Future date
            'frequency' => 'monthly',
            'interval' => 1,
            'duration_months' => 6,
            'description' => 'Manual Run Test',
            'status' => 'active',
            'items' => [
                [
                    'account_id' => $this->expenseAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                ],
                [
                    'account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => 500000,
                ]
            ]
        ];

        $entry = app(CreateScheduledJournalEntry::class)->execute($data);
        $this->assertEquals('2026-08-12', $entry->next_run_at->format('Y-m-d'));

        // Manually trigger run
        $count = app(ProcessScheduledJournalEntries::class)->execute($entry, true);
        
        $this->assertEquals(1, $count);
        
        $entry->refresh();
        $this->assertEquals('2026-08-12', $entry->last_run_at->format('Y-m-d'));
        $this->assertEquals('2026-09-12', $entry->next_run_at->format('Y-m-d'));

        Carbon::setTestNow(); // Reset clock
    }
}
