<?php

namespace App\Actions\CashSession;

use App\Models\CashSession;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReopenCashSession
{
    /**
     * Execute the action to reopen a closed Cash Session.
     * Resets status, closed_at, actual_cash, difference, closed_by_user_id, etc.
     *
     * @param  CashSession  $session
     * @return CashSession
     */
    public function execute(CashSession $session): CashSession
    {
        if ($session->status !== 'closed') {
            throw new InvalidArgumentException('Hanya sesi tutup (closed) yang dapat dibuka kembali.');
        }

        // Safeguard: Check if this is the most recently created session for this branch
        $latestSession = CashSession::where('branch_id', $session->branch_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestSession && $latestSession->id !== $session->id) {
            throw new InvalidArgumentException('Hanya sesi kasir terakhir di cabang ini yang dapat dibuka kembali.');
        }

        // Safeguard: Check if there is already another active open session for this branch
        $activeSession = CashSession::where('branch_id', $session->branch_id)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            throw new InvalidArgumentException('Cabang ini sudah memiliki sesi kasir lain yang sedang aktif.');
        }

        return DB::transaction(function () use ($session) {
            $session->update([
                'closed_at' => null,
                'actual_cash' => null,
                'expected_cash' => $session->opening_cash,
                'difference' => null,
                'status' => 'open',
                'closed_by_user_id' => null,
                'notes' => null,
            ]);

            return $session;
        });
    }
}
