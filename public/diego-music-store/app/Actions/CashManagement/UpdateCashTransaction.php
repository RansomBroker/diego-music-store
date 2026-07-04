<?php

namespace App\Actions\CashManagement;

use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCashTransaction
{
    public function execute(CashTransaction $cashTransaction, array $data): CashTransaction
    {
        if ($cashTransaction->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Only draft transactions can be updated.',
            ]);
        }

        return DB::transaction(function () use ($cashTransaction, $data) {
            $cashTransaction->update([
                'branch_id' => $data['branch_id'] ?? $cashTransaction->branch_id,
                'type' => $data['type'] ?? $cashTransaction->type,
                'transaction_date' => $data['transaction_date'] ?? $cashTransaction->transaction_date,
                'source_account_id' => $data['source_account_id'] ?? $cashTransaction->source_account_id,
                'destination_account_id' => $data['destination_account_id'] ?? $cashTransaction->destination_account_id,
                'amount' => $data['amount'] ?? $cashTransaction->amount,
                'notes' => $data['notes'] ?? $cashTransaction->notes,
            ]);

            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostCashTransaction::class)->execute($cashTransaction);
            }

            return $cashTransaction;
        });
    }
}
