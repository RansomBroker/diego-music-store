<?php

namespace App\Actions\CustomerDeposit;

use App\Models\CustomerDeposit;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancelCustomerDeposit
{
    /**
     * Cancel or delete a pending customer deposit.
     */
    public function execute(CustomerDeposit $deposit, ?string $reason = null, ?User $user = null): CustomerDeposit
    {
        return DB::transaction(function () use ($deposit, $reason, $user) {
            if (!$deposit->isPending()) {
                throw new InvalidArgumentException('Hanya deposit berstatus pending yang dapat dibatalkan.');
            }

            $customer = $deposit->customer;
            $depositAmount = floatval($deposit->deposit_amount);

            // Revert journal entry if present
            if ($deposit->deposit_journal_entry_id) {
                $journal = JournalEntry::find($deposit->deposit_journal_entry_id);
                if ($journal) {
                    $journal->items()->delete();
                    $journal->delete();
                }
                $deposit->deposit_journal_entry_id = null;
            }

            // Decrement customer deposit balance
            if ($depositAmount > 0 && \Illuminate\Support\Facades\Schema::hasColumn('customers', 'deposit_balance')) {
                $customer->decrement('deposit_balance', min($customer->deposit_balance, $depositAmount));
            }

            $noteSuffix = $reason ? " [Dibatalkan: {$reason}]" : " [Dibatalkan]";
            $deposit->update([
                'status' => 'cancelled',
                'notes' => trim(($deposit->notes ?? '') . $noteSuffix),
            ]);

            return $deposit;
        });
    }
}
