<?php

namespace App\Actions\CashManagement;

use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateCashTransaction
{
    public function execute(CashTransaction $cashTransaction, array $data): CashTransaction
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
        $targetBranchId = (int) ($data['branch_id'] ?? $cashTransaction->branch_id);
        abort_unless(in_array((int) $cashTransaction->branch_id, $allowedBranchIds, true)
            && in_array($targetBranchId, $allowedBranchIds, true), 403);

        if ($cashTransaction->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Only draft transactions can be updated.',
            ]);
        }

        return DB::transaction(function () use ($cashTransaction, $data, $targetBranchId) {
            $cashTransaction->update([
                'branch_id' => $targetBranchId,
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
