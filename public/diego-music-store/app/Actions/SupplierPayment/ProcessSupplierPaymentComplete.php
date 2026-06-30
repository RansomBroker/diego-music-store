<?php

namespace App\Actions\SupplierPayment;

use App\Models\SupplierPayment;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ProcessSupplierPaymentComplete
{
    /**
     * Execute the transition to complete/post a Supplier Payment.
     * Updates supplier outstanding debt and records the journal entry.
     *
     * @param  SupplierPayment  $payment
     * @return void
     */
    public function execute(SupplierPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            // Guard clause: status must be draft
            if ($payment->status !== 'draft') {
                throw new InvalidArgumentException('Hanya pelunasan hutang dengan status draft yang dapat diposting.');
            }

            // Sum up total paid from items to verify it matches total_amount
            $totalPaid = $payment->items->sum('amount_paid');
            if ($totalPaid <= 0) {
                throw new InvalidArgumentException('Total pelunasan harus lebih besar dari 0.');
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

            // 2. Decrement supplier outstanding debt
            $supplier = $payment->supplier;
            $supplier->decrement('outstanding_debt', $totalPaid);

            // 3. Update status to posted
            $payment->update([
                'status' => 'posted',
                'posted_at' => now(),
                'journal_no' => $journalNo,
                'total_amount' => $totalPaid, // Sync total_amount in header
            ]);

            // 4. Create automatic journal entry
            $journalEntry = JournalEntry::create([
                'branch_id' => $payment->branch_id,
                'entry_no' => $journalNo,
                'date' => $payment->payment_date,
                'description' => "Pelunasan Hutang otomatis ke {$supplier->name}: No. Pembayaran {$payment->payment_no}",
                'reference_type' => 'SupplierPayment',
                'reference_id' => $payment->id,
                'status' => 'posted',
                'created_by' => Auth::id() ?? $payment->created_by,
                'posted_at' => now(),
                'posted_by' => Auth::id() ?? $payment->created_by,
            ]);

            // Resolve Account Helper
            $resolveAccount = function($code, $defaultName = 'Default Account') {
                return Account::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $defaultName,
                        'classification' => str_starts_with($code, '1') ? 'asset' : (str_starts_with($code, '2') ? 'liability' : 'expense'),
                        'is_active' => true,
                    ]
                )->id;
            };

            $apAccountId = $resolveAccount('2-1000', 'Hutang Dagang'); // Accounts Payable
            $cashBankAccountId = $payment->account_id; // Cash/Bank account chosen in the payment form

            // Debit: Hutang Dagang (reducing liability)
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $apAccountId,
                'debit' => $totalPaid,
                'credit' => 0,
                'notes' => "Pelunasan Hutang Dagang ke {$supplier->name}",
            ]);

            // Credit: Kas/Bank (reducing asset)
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashBankAccountId,
                'debit' => 0,
                'credit' => $totalPaid,
                'notes' => "Pengeluaran Kas/Bank via {$payment->payment_method}",
            ]);
        });
    }
}
