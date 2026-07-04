<?php

namespace App\Actions\CashManagement;

use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class PostCashTransaction
{
    public function execute(CashTransaction $cashTransaction): CashTransaction
    {
        return DB::transaction(function () use ($cashTransaction) {
            if ($cashTransaction->status !== 'draft') {
                throw new InvalidArgumentException('Hanya transaksi draf yang dapat diposting.');
            }

            // 1. Generate Journal Number
            $date = now()->format('Ymd');
            $prefix = 'JV-' . $date . '-';
            $lastJournal = JournalEntry::where('entry_no', 'like', $prefix . '%')
                ->orderBy('entry_no', 'desc')
                ->first();

            if ($lastJournal) {
                $lastNum = intval(substr($lastJournal->entry_no, strlen($prefix)));
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNum = '0001';
            }
            $journalNo = $prefix . $nextNum;

            // 2. Create the automatic Journal Entry
            $descriptionMap = [
                'in' => 'Kas Masuk otomatis',
                'out' => 'Kas Keluar otomatis',
                'transfer' => 'Transfer Kas otomatis',
            ];
            $typeDesc = $descriptionMap[$cashTransaction->type] ?? 'Transaksi Kas';

            $journalEntry = JournalEntry::create([
                'branch_id' => $cashTransaction->branch_id,
                'entry_no' => $journalNo,
                'date' => $cashTransaction->transaction_date,
                'description' => "{$typeDesc}: No. Transaksi {$cashTransaction->transaction_no}",
                'reference_type' => 'CashTransaction',
                'reference_id' => $cashTransaction->id,
                'status' => 'posted',
                'created_by' => Auth::id() ?? $cashTransaction->created_by,
                'posted_at' => now(),
                'posted_by' => Auth::id() ?? $cashTransaction->created_by,
            ]);

            // 3. Create Journal Items
            // Debit: destination_account_id
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashTransaction->destination_account_id,
                'debit' => $cashTransaction->amount,
                'credit' => 0,
                'notes' => $cashTransaction->notes ?? $typeDesc,
            ]);

            // Credit: source_account_id
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashTransaction->source_account_id,
                'debit' => 0,
                'credit' => $cashTransaction->amount,
                'notes' => $cashTransaction->notes ?? $typeDesc,
            ]);

            // 4. Update Cash Transaction header
            $cashTransaction->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id() ?? $cashTransaction->created_by,
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $cashTransaction;
        });
    }
}
