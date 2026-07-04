<?php

namespace App\Actions\CashManagement;

use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateCashTransaction
{
    public function execute(array $data): CashTransaction
    {
        return DB::transaction(function () use ($data) {
            $transactionNo = $data['transaction_no'] ?? null;
            if (empty($transactionNo)) {
                $transactionNo = CashTransaction::generateTransactionNo();
            }

            $tx = CashTransaction::create([
                'transaction_no' => $transactionNo,
                'branch_id' => $data['branch_id'],
                'type' => $data['type'],
                'transaction_date' => $data['transaction_date'],
                'source_account_id' => $data['source_account_id'],
                'destination_account_id' => $data['destination_account_id'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostCashTransaction::class)->execute($tx);
            }

            return $tx;
        });
    }
}
