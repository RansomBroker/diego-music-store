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
        if ($session->status !== 'open') {
            throw new InvalidArgumentException('Hanya sesi aktif (open) yang dapat ditutup.');
        }

        return DB::transaction(function () use ($session, $data) {
            $actualCash = intval($data['actual_cash'] ?? 0);
            $notes = $data['notes'] ?? null;
            $closedBy = $data['closed_by_user_id'] ?? Auth::id();

            if (!$closedBy) {
                throw new InvalidArgumentException('User ID yang melakukan penutupan wajib ditentukan.');
            }

            // Calculate expected cash dynamically
            // Expected Cash = Opening Cash + Cash Sales (payment_method = 'cash')
            $cashSales = $session->sales()
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('grand_total');

            $expectedCash = $session->opening_cash + $cashSales;
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
