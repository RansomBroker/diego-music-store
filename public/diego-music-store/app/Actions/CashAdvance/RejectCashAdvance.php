<?php

namespace App\Actions\CashAdvance;

use App\Models\EmployeeCashAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectCashAdvance
{
    /**
     * Reject cash advance request.
     *
     * @param int $advanceId
     * @param User|null $user
     * @param string|null $reason
     * @return EmployeeCashAdvance
     */
    public function execute(int $advanceId, ?User $user = null, ?string $reason = null): EmployeeCashAdvance
    {
        return DB::transaction(function () use ($advanceId, $user, $reason) {
            $advance = EmployeeCashAdvance::findOrFail($advanceId);

            if ($advance->status !== 'pending') {
                throw new \Exception('Hanya pengajuan kasbon berstatus Pending yang dapat ditolak.');
            }

            $advance->update([
                'status' => 'rejected',
                'approved_by' => $user?->id,
                'approved_at' => now(),
                'notes' => trim(($advance->notes ?? '') . ($reason ? " [Penolakan: {$reason}]" : '')),
            ]);

            return $advance;
        });
    }
}
