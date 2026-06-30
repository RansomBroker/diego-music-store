<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateJournalEntry
{
    /**
     * Create a new Journal Entry.
     *
     * @param  array<string, mixed>  $data
     * @return JournalEntry
     */
    public function execute(array $data): JournalEntry
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

            // 2. Generate Entry Number if not set
            $entryNo = $data['entry_no'] ?? null;
            if (empty($entryNo)) {
                $entryNo = JournalEntry::generateEntryNo();
            }

            // 3. Create entry
            $entry = JournalEntry::create([
                'branch_id' => $data['branch_id'],
                'entry_no' => $entryNo,
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'status' => 'draft', // always start as draft
                'created_by' => Auth::id(),
            ]);

            // 4. Create items
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
