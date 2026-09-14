<?php

namespace App\Actions\CashAdvance;

use App\Models\EmployeeCashAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelCashAdvanceRequest
{
    /**
     * Cancel a pending cash advance request.
     *
     * @param int $advanceId
     * @param User|null $user
     * @return EmployeeCashAdvance
     */
    public function execute(int $advanceId, ?User $user = null): EmployeeCashAdvance
    {
        return DB::transaction(function () use ($advanceId, $user) {
            $advance = EmployeeCashAdvance::findOrFail($advanceId);

            if ($advance->status !== 'pending') {
                throw new \Exception('Hanya pengajuan kasbon berstatus Pending yang dapat dibatalkan.');
            }

            $advance->update([
                'status' => 'cancelled',
                'notes' => trim(($advance->notes ?? '') . ' [Dibatalkan pada ' . now()->format('Y-m-d H:i') . ']'),
            ]);

            return $advance;
        });
    }
}
