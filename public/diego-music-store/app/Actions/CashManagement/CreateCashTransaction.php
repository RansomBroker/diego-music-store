<?php

namespace App\Actions\CashManagement;

use App\Models\CashTransaction;
use App\Models\CashSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateCashTransaction
{
    public function execute(array $data): CashTransaction
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $branchId = (int) ($data['branch_id'] ?? 0);
        abort_unless(in_array($branchId, $allowedBranchIds, true), 403);

        if (!empty($data['cash_session_id'])) {
            abort_unless(CashSession::query()
                ->whereKey($data['cash_session_id'])
                ->where('branch_id', $branchId)
                ->exists(), 403);
        }

        return DB::transaction(function () use ($data) {
            $transactionNo = $data['transaction_no'] ?? null;
            if (empty($transactionNo)) {
                $transactionNo = CashTransaction::generateTransactionNo();
            }

            $tx = CashTransaction::create([
                'transaction_no' => $transactionNo,
                'branch_id' => $data['branch_id'],
                'cash_session_id' => $data['cash_session_id'] ?? null,
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
