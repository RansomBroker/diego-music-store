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

        // Check if there is already an active (open) session at this branch (by ANY user)
        $activeSession = CashSession::with('user')
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            $ownerName = $activeSession->user->name ?? 'Karyawan lain';
            throw new InvalidArgumentException("Sesi kasir di cabang ini sedang dibuka oleh {$ownerName}. Harap tutup sesi tersebut terlebih dahulu.");
        }

        $session = CashSession::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'expected_cash' => $openingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);

        // Auto clock-in employee if linked to this user (kecualikan Owner / Admin)
        $user = \App\Models\User::find($userId);
        if ($user && $user->employee && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            app(\App\Actions\Attendance\ClockIn::class)->execute(
                $user->employee,
                $branchId,
                'Otomatis presensi saat Buka Sesi Kasir'
            );
        }

        return $session;
    }
}
