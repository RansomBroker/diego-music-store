<?php

namespace App\Actions\Accounting;

use App\Models\JournalEntry;
use App\Models\MonthlyClosing;
use Exception;
use Illuminate\Support\Facades\DB;

class ReopenMonthlyClosing
{
    /**
     * Execute Reopen Period (Buka Kembali Periode Keuangan).
     *
     * @param  MonthlyClosing  $closing
     * @param  int|null  $userId
     * @param  string|null  $notes
     * @return MonthlyClosing
     * @throws Exception
     */
    public function execute(MonthlyClosing $closing, ?int $userId = null, ?string $notes = null): MonthlyClosing
    {
        if ($closing->status === 'reopened') {
            throw new Exception("Periode keuangan {$closing->period_key} sudah dalam status TERBUKA (Reopened).");
        }

        return DB::transaction(function () use ($closing, $userId, $notes) {
            // Delete closing journal if exists
            if ($closing->closing_journal_id) {
                $journal = JournalEntry::find($closing->closing_journal_id);
                if ($journal) {
                    $journal->items()->delete();
                    $journal->delete();
                }
            }

            $closing->update([
                'status'             => 'reopened',
                'reopened_at'        => now(),
                'reopened_by'        => $userId,
                'closing_journal_id' => null,
                'notes'              => $notes ? ($closing->notes . "\n[Reopened]: " . $notes) : $closing->notes,
            ]);

            return $closing;
        });
    }
}
