<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PostJournalEntry
{
    /**
     * Post a Journal Entry.
     *
     * @param  JournalEntry  $entry
     * @return JournalEntry
     */
    public function execute(JournalEntry $entry): JournalEntry
    {
        if ($entry->status === 'posted') {
            throw new \InvalidArgumentException("Jurnal sudah diposting.");
        }

        // Validate balance before posting
        $debitTotal = $entry->items()->sum('debit');
        $creditTotal = $entry->items()->sum('credit');

        if ($debitTotal <= 0 || $debitTotal !== $creditTotal) {
            throw ValidationException::withMessages([
                'items' => "Jurnal tidak seimbang. Total Debit tidak sama dengan Total Kredit.",
            ]);
        }

        $entry->update([
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id(),
        ]);

        return $entry;
    }
}
