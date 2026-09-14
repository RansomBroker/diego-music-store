<?php

namespace App\Actions\Payroll;

use App\Models\AttendanceViolationLog;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Models\KpiEvaluation;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyPayroll
{
    /**
     * Generate or recalculate monthly payroll for active employees for a given period (YYYY-MM).
     *
     * @param string $period Format YYYY-MM
     * @param int|null $branchId
     * @param User|null $creator
     * @return Payroll
     */
    public function execute(string $period, ?int $branchId = null, ?User $creator = null): Payroll
    {
        return DB::transaction(function () use ($period, $branchId, $creator) {
            $payroll = Payroll::where('period', $period)
                ->where('branch_id', $branchId)
                ->first();

            if ($payroll && $payroll->status === 'paid') {
                throw new \Exception("Payroll periode {$period} sudah berstatus PAID (dibayar) dan tidak dapat dihitung ulang.");
            }

            if (!$payroll) {
                $payroll = Payroll::create([
                    'period' => $period,
                    'branch_id' => $branchId,
                    'payroll_code' => Payroll::generatePayrollCode($period),
                    'status' => 'draft',
                    'created_by' => $creator?->id,
                ]);
            } else if ($payroll->status === 'cancelled') {
                $payroll->update(['status' => 'draft']);
            }

            // Fetch target employees
            $employeeQuery = Employee::where('is_active', true);
            if ($branchId) {
                $employeeQuery->where('branch_id', $branchId);
            }
            $employees = $employeeQuery->get();

            $totalBasic = 0.0;
            $totalAllowances = 0.0;
            $totalCommissions = 0.0;
            $totalKpiBonuses = 0.0;
            $totalDeductions = 0.0;
            $totalNet = 0.0;

            foreach ($employees as $emp) {
                // 1. Basic Salary
                $basicSalary = (float) ($emp->basic_salary ?: 0);

                // 2. Sales Commission Amount
                $commissionAmount = (float) SalesCommissionLog::where('employee_id', $emp->id)
                    ->where('date', 'like', $period . '%')
                    ->sum('commission_amount');

                // 3. KPI Bonus Amount
                $kpiBonusAmount = (float) KpiEvaluation::where('employee_id', $emp->id)
                    ->where('period', $period)
                    ->value('earned_bonus_amount') ?: 0.0;

                // 4. Overtime Calculation (Approved Overtimes)
                $approvedOvertimes = EmployeeOvertime::where('employee_id', $emp->id)
                    ->where('date', 'like', $period . '%')
                    ->where('status', 'approved')
                    ->get();

                $overtimeAmount = (float) $approvedOvertimes->sum('overtime_amount');
                $overtimeDetails = $approvedOvertimes->map(function ($ot) {
                    return [
                        'id' => $ot->id,
                        'date' => $ot->date ? (is_string($ot->date) ? $ot->date : $ot->date->format('Y-m-d')) : '-',
                        'start_time' => $ot->start_time,
                        'end_time' => $ot->end_time,
                        'hours' => (float) $ot->hours,
                        'hourly_rate' => (float) $ot->hourly_rate,
                        'overtime_amount' => (float) $ot->overtime_amount,
                        'status' => $ot->status,
                        'notes' => $ot->notes,
                    ];
                })->toArray();

                // 5. Attendance Violation Deduction Amount
                $violationDeductionAmount = (float) AttendanceViolationLog::where('employee_id', $emp->id)
                    ->where('date', 'like', $period . '%')
                    ->sum('deduction_amount');

                // 6. Cash Advance Auto Deduction Calculation
                $activeAdvances = \App\Models\EmployeeCashAdvance::where('employee_id', $emp->id)
                    ->where('status', 'approved')
                    ->where('remaining_amount', '>', 0)
                    ->get();

                $cashAdvanceDeduction = 0.0;
                $cashAdvanceDetails = [];

                foreach ($activeAdvances as $adv) {
                    $installment = min((float) $adv->remaining_amount, (float) $adv->monthly_installment);
                    $cashAdvanceDeduction += $installment;
                    $cashAdvanceDetails[] = [
                        'advance_id' => $adv->id,
                        'advance_number' => $adv->advance_number,
                        'installment_amount' => $installment,
                        'remaining_before' => (float) $adv->remaining_amount,
                    ];
                }

                // 7. Existing item details preservation (for manual allowances/deductions)
                $existingItem = PayrollItem::where('payroll_id', $payroll->id)
                    ->where('employee_id', $emp->id)
                    ->first();

                $allowanceAmount = $existingItem ? (float) $existingItem->allowance_amount : 0.0;
                $allowanceDetails = $existingItem ? $existingItem->allowance_details : [];
                $otherDeductionAmount = $cashAdvanceDeduction + ($existingItem ? (float) $existingItem->other_deduction_amount : 0.0);
                $deductionDetails = array_merge($cashAdvanceDetails, $existingItem ? ($existingItem->deduction_details ?: []) : []);

                $netSalary = max(0.0, ($basicSalary + $allowanceAmount + $overtimeAmount + $commissionAmount + $kpiBonusAmount) - ($violationDeductionAmount + $otherDeductionAmount));

                PayrollItem::updateOrCreate(
                    [
                        'payroll_id' => $payroll->id,
                        'employee_id' => $emp->id,
                    ],
                    [
                        'branch_id' => $emp->branch_id,
                        'basic_salary' => $basicSalary,
                        'allowance_amount' => $allowanceAmount,
                        'allowance_details' => $allowanceDetails,
                        'overtime_amount' => $overtimeAmount,
                        'overtime_details' => $overtimeDetails,
                        'commission_amount' => $commissionAmount,
                        'kpi_bonus_amount' => $kpiBonusAmount,
                        'violation_deduction_amount' => $violationDeductionAmount,
                        'other_deduction_amount' => $otherDeductionAmount,
                        'deduction_details' => $deductionDetails,
                        'net_salary' => $netSalary,
                        'bank_name' => 'BCA',
                        'bank_account_number' => '123456' . $emp->id,
                        'bank_account_holder' => $emp->name,
                    ]
                );

                $totalBasic += $basicSalary;
                $totalAllowances += ($allowanceAmount + $overtimeAmount);
                $totalCommissions += $commissionAmount;
                $totalKpiBonuses += $kpiBonusAmount;
                $totalDeductions += ($violationDeductionAmount + $otherDeductionAmount);
                $totalNet += $netSalary;
            }

            $payroll->update([
                'total_employees' => $employees->count(),
                'total_basic_salary' => $totalBasic,
                'total_allowances' => $totalAllowances,
                'total_commissions' => $totalCommissions,
                'total_kpi_bonuses' => $totalKpiBonuses,
                'total_deductions' => $totalDeductions,
                'total_net_salary' => $totalNet,
            ]);

            return $payroll->fresh(['items.employee']);
        });
    }
}
