<?php

namespace App\Actions\Accounting;

use App\Models\ScheduledJournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessScheduledJournalEntries
{
    /**
     * Process all pending scheduled journal entries or a specific one manually.
     *
     * @param  ScheduledJournalEntry|null  $specificEntry
     * @param  bool  $manualRun
     * @return int Number of journal entries created
     */
    public function execute(?ScheduledJournalEntry $specificEntry = null, bool $manualRun = false): int
    {
        $today = Carbon::today();
        
        if ($specificEntry) {
            $scheduledEntries = collect([$specificEntry]);
        } else {
            $scheduledEntries = ScheduledJournalEntry::where('status', 'active')
                ->whereNotNull('next_run_at')
                ->where('next_run_at', '<=', $today->format('Y-m-d'))
                ->with('items')
                ->get();
        }

        $createdCount = 0;

        foreach ($scheduledEntries as $entry) {
            // If not a manual run, check if it's active and due
            if (!$manualRun) {
                if ($entry->status !== 'active' || !$entry->next_run_at || $entry->next_run_at->greaterThan($today)) {
                    continue;
                }
            }

            DB::transaction(function () use ($entry, $today, $manualRun, &$createdCount) {
                $firstRun = true;

                while ($entry->next_run_at && ($firstRun || $entry->next_run_at->lte($today))) {
                    $firstRun = false;
                    $runDate = $entry->next_run_at;

                    // 1. Prepare data for CreateJournalEntry
                    $items = [];
                    foreach ($entry->items as $item) {
                        $items[] = [
                            'account_id' => $item->account_id,
                            'debit' => $item->debit,
                            'credit' => $item->credit,
                            'notes' => $item->notes,
                        ];
                    }

                    $journalData = [
                        'branch_id' => $entry->branch_id,
                        'date' => $runDate->format('Y-m-d'),
                        'description' => ($entry->description ?? 'Jurnal Terjadwal') . ' (Otomatis Terjadwal)',
                        'status' => 'posted', // Automatically post the created journal entry
                        'reference_type' => 'ScheduledJournalEntry',
                        'reference_id' => $entry->id,
                        'items' => $items,
                    ];

                    try {
                        // Create the actual Journal Entry
                        app(CreateJournalEntry::class)->execute($journalData);
                        $createdCount++;
                    } catch (\Exception $e) {
                        Log::error("Failed to generate journal entry for Scheduled Journal ID {$entry->id}: " . $e->getMessage());
                        // Break to avoid infinite loop on failure
                        break;
                    }

                    // 2. Calculate next run date
                    $nextRunAt = $runDate->copy();
                    $interval = $entry->interval ?? 1;

                    switch ($entry->frequency) {
                        case 'daily':
                            $nextRunAt->addDays($interval);
                            break;
                        case 'weekly':
                            $nextRunAt->addWeeks($interval);
                            break;
                        case 'yearly':
                            $nextRunAt->addYearsNoOverflow($interval);
                            break;
                        case 'monthly':
                        default:
                            $nextRunAt->addMonthsNoOverflow($interval);
                            break;
                    }

                    // 3. Update execution records
                    $entry->last_run_at = $runDate;

                    // Check if next run exceeds the end date
                    if ($entry->end_date && $nextRunAt->greaterThanOrEqualTo($entry->end_date)) {
                        $entry->status = 'completed';
                        $entry->next_run_at = null;
                    } else {
                        $entry->next_run_at = $nextRunAt;
                    }

                    $entry->save();

                    // If manual run, we only run once
                    if ($manualRun) {
                        break;
                    }
                }
            });
        }

        return $createdCount;
    }
}
