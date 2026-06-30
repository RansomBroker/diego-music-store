<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateJournalEntry
{
    /**
     * Update a Journal Entry.
     *
     * @param  JournalEntry  $entry
     * @param  array<string, mixed>  $data
     * @return JournalEntry
     */
    public function execute(JournalEntry $entry, array $data): JournalEntry
    {
        if ($entry->status === 'posted') {
            throw new \InvalidArgumentException("Jurnal yang sudah diposting tidak dapat diubah.");
        }

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

            // 2. Update entry details
            $entry->update([
                'branch_id' => $data['branch_id'],
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
            ]);

            // 3. Clear old items
            $entry->items()->delete();

            // 4. Create new items
            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'debit' => intval($item['debit'] ?? 0),
                    'credit' => intval($item['credit'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // 5. Post automatically if requested
            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostJournalEntry::class)->execute($entry);
            }

            return $entry;
        });
    }
}
