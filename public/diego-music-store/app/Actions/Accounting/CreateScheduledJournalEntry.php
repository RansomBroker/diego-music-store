<?php

namespace App\Actions\Accounting;

use App\Models\ScheduledJournalEntry;
use App\Models\ScheduledJournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateScheduledJournalEntry
{
    /**
     * Create a new Scheduled Journal Entry.
     *
     * @param  array<string, mixed>  $data
     * @return ScheduledJournalEntry
     */
    public function execute(array $data): ScheduledJournalEntry
    {
        return DB::transaction(function () use ($data) {
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

            // 2. Calculate end_date and next_run_at
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $durationMonths = isset($data['duration_months']) && $data['duration_months'] !== '' ? intval($data['duration_months']) : null;
            
            $endDate = null;
            if ($durationMonths !== null) {
                $endDate = $startDate->copy()->addMonths($durationMonths);
            }

            // Initial next_run_at is the start_date
            $nextRunAt = $startDate;

            // 3. Create entry
            $entry = ScheduledJournalEntry::create([
                'branch_id' => $data['branch_id'],
                'description' => $data['description'] ?? null,
                'start_date' => $startDate->format('Y-m-d'),
                'frequency' => $data['frequency'] ?? 'monthly',
                'interval' => intval($data['interval'] ?? 1),
                'duration_months' => $durationMonths,
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'status' => $data['status'] ?? 'active',
                'next_run_at' => $nextRunAt->format('Y-m-d'),
                'created_by' => Auth::id(),
            ]);

            // 4. Create items
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
