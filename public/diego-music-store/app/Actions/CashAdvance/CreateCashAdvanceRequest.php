<?php

namespace App\Actions\CashAdvance;

use App\Models\Employee;
use App\Models\EmployeeCashAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCashAdvanceRequest
{
    /**
     * Create a new Cash Advance request with 50% basic salary limit validation.
     *
     * @param int $employeeId
     * @param float $amount
     * @param int $tenorMonths
     * @param string|null $reason
     * @param User|null $creator
     * @return EmployeeCashAdvance
     */
    public function execute(int $employeeId, float $amount, int $tenorMonths = 1, ?string $reason = null, ?User $creator = null): EmployeeCashAdvance
    {
        return DB::transaction(function () use ($employeeId, $amount, $tenorMonths, $reason, $creator) {
            $employee = Employee::findOrFail($employeeId);

            if ($amount <= 0) {
                throw new \Exception('Nominal kasbon harus lebih dari Rp 0.');
            }

            if ($tenorMonths <= 0) {
                $tenorMonths = 1;
            }

            // Limit validation: Total active advance + requested amount <= 50% basic salary
            $basicSalary = (float) ($employee->basic_salary ?: 0);
            $maxLimit = $basicSalary * 0.50;
            $activeBalance = $employee->active_cash_advance_balance;

            if (($activeBalance + $amount) > $maxLimit) {
                throw new \Exception("Pengajuan kasbon Rp " . number_format($amount, 0, ',', '.') . " melebihi batas limit 50% gaji pokok (Maksimal Limit: Rp " . number_format($maxLimit, 0, ',', '.') . ", Saldo Aktif: Rp " . number_format($activeBalance, 0, ',', '.') . ").");
            }

            $monthlyInstallment = round($amount / $tenorMonths, 2);

            return EmployeeCashAdvance::create([
                'advance_number' => EmployeeCashAdvance::generateAdvanceNumber(),
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id,
                'request_date' => now()->format('Y-m-d'),
                'amount' => $amount,
                'tenor_months' => $tenorMonths,
                'monthly_installment' => $monthlyInstallment,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'status' => 'pending',
                'reason' => $reason,
            ]);
        });
    }
}
