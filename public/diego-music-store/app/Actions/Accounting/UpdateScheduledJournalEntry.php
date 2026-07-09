<?php

namespace App\Actions\Accounting;

use App\Models\ScheduledJournalEntry;
use App\Models\ScheduledJournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateScheduledJournalEntry
{
    /**
     * Update an existing Scheduled Journal Entry.
     *
     * @param  ScheduledJournalEntry  $entry
     * @param  array<string, mixed>  $data
     * @return ScheduledJournalEntry
     */
    public function execute(ScheduledJournalEntry $entry, array $data): ScheduledJournalEntry
    {
        return DB::transaction(function () use ($entry, $data) {
            $items = $data['items'] ?? [];

            // 1. Validate Debit & Credit balance
            $debitTotal = 0;
            $creditTotal = 0;
            foreach ($items as $item) {
                $debitTotal += intval($item['debit'] ?? 0);
                $creditTotal += intval($item['credit'] ?? 0);
            }

            if ($debitTotal <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Total Debit / Kredit harus lebih besar dari 0.',
                ]);
            }

            if ($debitTotal !== $creditTotal) {
                throw ValidationException::withMessages([
                    'items' => "Jurnal tidak seimbang. Total Debit (Rp " . number_format($debitTotal) . ") tidak sama dengan Total Kredit (Rp " . number_format($creditTotal) . ").",
                ]);
            }

            // 2. Calculate end_date and next_run_at if schedule fields changed
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $durationMonths = isset($data['duration_months']) && $data['duration_months'] !== '' ? intval($data['duration_months']) : null;
            
            $endDate = null;
            if ($durationMonths !== null) {
                $endDate = $startDate->copy()->addMonths($durationMonths);
            }

            // If start_date has changed and it hasn't run yet, update next_run_at
            $nextRunAt = $entry->next_run_at;
            if ($entry->last_run_at === null || $entry->start_date->format('Y-m-d') !== $startDate->format('Y-m-d')) {
                if ($entry->last_run_at === null) {
                    $nextRunAt = $startDate;
                }
            }

            // Update entry
            $entry->update([
                'branch_id' => $data['branch_id'],
                'description' => $data['description'] ?? null,
                'start_date' => $startDate->format('Y-m-d'),
                'frequency' => $data['frequency'] ?? 'monthly',
                'interval' => intval($data['interval'] ?? 1),
                'duration_months' => $durationMonths,
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'status' => $data['status'] ?? 'active',
                'next_run_at' => $nextRunAt ? $nextRunAt->format('Y-m-d') : null,
            ]);

            // Sync items
            $entry->items()->delete();
            foreach ($items as $item) {
                ScheduledJournalItem::create([
                    'scheduled_journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'debit' => intval($item['debit'] ?? 0),
                    'credit' => intval($item['credit'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $entry;
        });
    }
}
