<?php

namespace App\Actions\CashSession;

use App\Models\CashSession;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class OpenCashSession
{
    /**
     * Execute the action to open a new Cash Session.
     *
     * @param  array<string, mixed>  $data
     * @return CashSession
     */
    public function execute(array $data): CashSession
    {
        $userId = $data['user_id'] ?? Auth::id();
        $branchId = $data['branch_id'] ?? null;
        $openingCash = intval($data['opening_cash'] ?? 0);
        $notes = $data['notes'] ?? null;

        if (!$userId) {
            throw new InvalidArgumentException('User ID wajib ditentukan untuk membuka sesi.');
        }

        if (!$branchId) {
            throw new InvalidArgumentException('Cabang wajib ditentukan untuk membuka sesi.');
        }

        // Check if there is already an active (open) session for this user at this branch
        $activeSession = CashSession::where('user_id', $userId)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            throw new InvalidArgumentException('Anda sudah memiliki sesi kasir aktif di cabang ini.');
        }

        return CashSession::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'expected_cash' => $openingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);
    }
}
