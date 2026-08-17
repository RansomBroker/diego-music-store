<?php

namespace App\Actions\SupplierPayment;

use App\Models\SupplierPayment;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancelSupplierPayment
{
    /**
     * Execute cancelling a Supplier Payment transaction.
     * Restores supplier outstanding debt if it was posted and voids associated journal entry.
     * Updates status to 'cancelled'.
     *
     * @param SupplierPayment $payment
     * @return void
     */
    public function execute(SupplierPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status === 'cancelled') {
                throw new InvalidArgumentException('Pembayaran supplier ini sudah dibatalkan.');
            }

            if ($payment->status === 'posted') {
                $totalPaid = $payment->items->sum('amount_paid');
                if ($totalPaid <= 0) {
                    $totalPaid = floatval($payment->total_amount);
                }

                // Restore supplier outstanding debt
                if ($totalPaid > 0 && $payment->supplier) {
                    $payment->supplier->increment('outstanding_debt', $totalPaid);
                }

                // Void or cancel associated journal entry
                $journalEntry = JournalEntry::where('reference_type', 'SupplierPayment')
                    ->where('reference_id', $payment->id)
                    ->first();

                if ($journalEntry) {
                    $journalEntry->update(['status' => 'cancelled']);
                }
            }

            // Update status to cancelled
            $payment->update([
                'status' => 'cancelled',
            ]);
        });
    }
}
