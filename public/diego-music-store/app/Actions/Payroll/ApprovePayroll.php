<?php

namespace App\Actions\Payroll;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApprovePayroll
{
    /**
     * Approve draft payroll lock figures before payment.
     *
     * @param int $payrollId
     * @param User|null $user
     * @return Payroll
     */
    public function execute(int $payrollId, ?User $user = null): Payroll
    {
        return DB::transaction(function () use ($payrollId, $user) {
            $payroll = Payroll::findOrFail($payrollId);

            if ($payroll->status === 'cancelled') {
                throw new \Exception('Payroll yang sudah dibatalkan tidak dapat disetujui.');
            }

            $payroll->update([
                'status' => 'approved',
                'approved_by' => $user?->id ?: $payroll->approved_by,
                'approved_at' => now(),
            ]);

            return $payroll;
        });
    }
}
