<?php

namespace App\Actions\CashManagement;

use App\Helpers\BranchHelper;
use App\Helpers\CashSessionHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\SaleHelper;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteCashTransaction
{
    /**
     * Delete a cash transaction (draft or posted) and revert its journal entries
     * so account balances and cash session totals return to their prior state.
     */
    public function execute(CashTransaction $cashTransaction): void
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'bo.cash_transactions.delete');
        abort_unless(Auth::check(), 403);

        $allowedBranchIds = BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        abort_unless(in_array((int) $cashTransaction->branch_id, $allowedBranchIds, true), 403);

        DB::transaction(function () use ($cashTransaction): void {
            $sessionId = $cashTransaction->cash_session_id;
            $journalEntryId = $cashTransaction->journal_entry_id;

            // Collect all linked journal entries (by FK and by reference)
            $journalEntries = JournalEntry::query()
                ->where(function ($q) use ($cashTransaction, $journalEntryId) {
                    $q->where(function ($sub) use ($cashTransaction) {
                        $sub->where('reference_type', 'CashTransaction')
                            ->where('reference_id', $cashTransaction->id);
                    });
                    if ($journalEntryId) {
                        $q->orWhere('id', $journalEntryId);
                    }
                })
                ->get();

            // Detach FK before deleting journal entries
            if ($journalEntryId) {
                $cashTransaction->update(['journal_entry_id' => null]);
            }

            foreach ($journalEntries as $je) {
                $je->items()->delete();
                $je->delete();
            }

            $cashTransaction->delete();

            // Recompute expected_cash & difference if linked to a cash session
            if ($sessionId) {
                $session = CashSession::find($sessionId);
                if ($session) {
                    $cashSales = SaleHelper::getSessionCashSalesSum($session);
                    $cashTotals = CashSessionHelper::getSessionCashTotals($session);
                    $expectedCash = (int) $session->opening_cash + $cashSales + $cashTotals['cash_in'] - $cashTotals['total_out'];

                    $updateData = ['expected_cash' => $expectedCash];
                    if ($session->status === 'closed' && $session->actual_cash !== null) {
                        $updateData['difference'] = (int) $session->actual_cash - $expectedCash;
                    }
                    $session->update($updateData);
                }
            }
        });
    }
}

