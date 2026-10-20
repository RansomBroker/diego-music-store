<?php

namespace App\Actions\CashSession;

use App\Models\CashSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CloseCashSession
{
    /**
     * Execute the action to close an active Cash Session.
     * Calculates dynamic expected cash, physical difference, and locks the session.
     *
     * @param  CashSession  $session
     * @param  array<string, mixed>  $data
     * @return CashSession
     */
    public function execute(CashSession $session, array $data): CashSession
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_if($user->hasRole(['sales', 'Sales']), 403);
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
        abort_unless(in_array((int) $session->branch_id, $allowedBranchIds, true), 403);
        $isManagement = $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']);
        abort_unless($isManagement || (int) $session->user_id === (int) $user->id, 403);

        if ($session->status !== 'open') {
            throw new InvalidArgumentException('Hanya sesi aktif (open) yang dapat ditutup.');
        }

        return DB::transaction(function () use ($session, $data) {
            $actualCash = intval($data['actual_cash'] ?? 0);
            $notes = $data['notes'] ?? null;
            // Do not trust a caller-supplied user ID for the audit field.
            $closedBy = Auth::id();

            if (!$closedBy) {
                throw new InvalidArgumentException('User ID yang melakukan penutupan wajib ditentukan.');
            }

            $cashSales = \App\Helpers\SaleHelper::getSessionCashSalesSum($session);
            $cashIn = $session->cashTransactions()
                ->where('type', 'in')
                ->where('status', 'posted')
                ->sum('amount');
            $cashOut = $session->cashTransactions()
                ->where('type', 'out')
                ->where('status', 'posted')
                ->sum('amount');

            $expectedCash = $session->opening_cash + $cashSales + $cashIn - $cashOut;
            $difference = $actualCash - $expectedCash;

            $session->update([
                'closed_at' => now(),
                'actual_cash' => $actualCash,
                'expected_cash' => $expectedCash,
                'difference' => $difference,
                'status' => 'closed',
                'closed_by_user_id' => $closedBy,
                'notes' => $notes,
            ]);

            return $session;
        });
    }
}
