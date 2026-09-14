<?php

namespace App\Actions\Payroll;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessPayrollPayment
{
    /**
     * Mark payroll as paid.
     *
     * @param int $payrollId
     * @param User|null $user
     * @return Payroll
     */
    public function execute(int $payrollId, ?User $user = null): Payroll
    {
        return DB::transaction(function () use ($payrollId, $user) {
            $payroll = Payroll::findOrFail($payrollId);

            $payroll->update([
                'status' => 'paid',
                'approved_by' => $user?->id ?: $payroll->approved_by,
                'approved_at' => $payroll->approved_at ?: now(),
                'paid_at' => now(),
            ]);

            // Deduct active cash advances balance
            foreach ($payroll->items as $item) {
                if (empty($item->deduction_details) || !is_array($item->deduction_details)) {
                    continue;
                }

                foreach ($item->deduction_details as $ded) {
                    if (isset($ded['advance_id']) && isset($ded['installment_amount'])) {
                        $advance = \App\Models\EmployeeCashAdvance::find($ded['advance_id']);
                        if ($advance && $advance->status === 'approved') {
                            $installment = (float) $ded['installment_amount'];
                            $newPaid = $advance->paid_amount + $installment;
                            $newRemaining = max(0.0, $advance->amount - $newPaid);
                            $newStatus = $newRemaining <= 0 ? 'paid_off' : 'approved';

                            $advance->update([
                                'paid_amount' => $newPaid,
                                'remaining_amount' => $newRemaining,
                                'status' => $newStatus,
                            ]);
                        }
                    }
                }
            }

            return $payroll;
        });
    }
}
