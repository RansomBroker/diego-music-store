<?php

namespace App\Actions\CashAdvance;

use App\Models\EmployeeCashAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettleCashAdvanceEarly
{
    /**
     * Process early manual repayment of cash advance outside payroll.
     *
     * @param int $advanceId
     * @param float $repaymentAmount
     * @param string $paymentMethod
     * @param string|null $notes
     * @param User|null $user
     * @return EmployeeCashAdvance
     */
    public function execute(int $advanceId, float $repaymentAmount, string $paymentMethod = 'cash', ?string $notes = null, ?User $user = null): EmployeeCashAdvance
    {
        return DB::transaction(function () use ($advanceId, $repaymentAmount, $paymentMethod, $notes, $user) {
            $advance = EmployeeCashAdvance::findOrFail($advanceId);

            if ($advance->status !== 'approved' && $advance->status !== 'paid_off') {
                throw new \Exception('Hanya kasbon yang disetujui yang dapat dilakukan pelunasan awal.');
            }

            if ($advance->remaining_amount <= 0) {
                throw new \Exception('Kasbon ini sudah lunas.');
            }

            if ($repaymentAmount <= 0) {
                throw new \Exception('Nominal pelunasan harus lebih dari Rp 0.');
            }

            $actualPay = min((float) $advance->remaining_amount, $repaymentAmount);
            $newPaid = (float) $advance->paid_amount + $actualPay;
            $newRemaining = max(0.0, (float) $advance->amount - $newPaid);
            $newStatus = $newRemaining <= 0 ? 'paid_off' : 'approved';

            $noteEntry = sprintf(
                " [Pelunasan Manual %s: Rp %s via %s pada %s]",
                $user?->name ?? 'Admin',
                number_format($actualPay, 0, ',', '.'),
                strtoupper($paymentMethod),
                now()->format('Y-m-d H:i')
            );

            $advance->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => $newRemaining,
                'status' => $newStatus,
                'notes' => trim(($advance->notes ?? '') . $noteEntry),
            ]);

            return $advance;
        });
    }
}
