<?php

namespace App\Actions\Payroll;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelPayroll
{
    /**
     * Cancel draft/approved payroll so period can be regenerated clean if needed.
     *
     * @param int $payrollId
     * @param User|null $user
     * @return Payroll
     */
    public function execute(int $payrollId, ?User $user = null): Payroll
    {
        return DB::transaction(function () use ($payrollId, $user) {
            $payroll = Payroll::findOrFail($payrollId);

            if ($payroll->status === 'paid') {
                throw new \Exception('Payroll yang sudah berstatus Paid (dibayar) tidak dapat dibatalkan.');
            }

            $payroll->update([
                'status' => 'cancelled',
                'notes' => trim(($payroll->notes ?? '') . ' [Dibatalkan oleh ' . ($user->name ?? 'Admin') . ' pada ' . now()->format('Y-m-d H:i') . ']'),
            ]);

            return $payroll;
        });
    }
}
