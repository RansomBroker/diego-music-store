<?php

namespace App\Actions\Commission;

use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SalesCommissionLog;
use Illuminate\Support\Facades\DB;

class CalculateSaleCommission
{
    /**
     * Execute action to calculate and log commission for a sales transaction.
     *
     * @param  Sale  $sale
     * @param  Employee  $employee
     * @param  CommissionScheme|null  $schemeOverride
     * @return SalesCommissionLog|null
     */
    public function execute(Sale $sale, Employee $employee, ?CommissionScheme $schemeOverride = null): ?SalesCommissionLog
    {
        return DB::transaction(function () use ($sale, $employee, $schemeOverride) {
            $scheme = $schemeOverride;

            if (!$scheme) {
                // 1. Try finding employee-specific scheme first (many-to-many or single employee_id)
                $scheme = CommissionScheme::where('is_active', true)
                    ->where(function ($q) use ($employee) {
                        $q->whereHas('employees', function ($sub) use ($employee) {
                            $sub->where('employees.id', $employee->id);
                        })->orWhere('employee_id', $employee->id);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                // 2. Fallback to branch-level or global active scheme
                if (!$scheme) {
                    $scheme = CommissionScheme::where('is_active', true)
                        ->whereNull('employee_id')
                        ->whereDoesntHave('employees')
                        ->where(function ($query) use ($sale) {
                            $query->whereNull('branch_id')
                                ->orWhere('branch_id', $sale->branch_id);
                        })
                        ->orderBy('id', 'desc')
                        ->first();
                }
            }

            if (!$scheme) {
                return null;
            }

            $saleAmount = (float) $sale->grand_total;
            $commissionAmount = 0.0;

            if ($scheme->calculation_type === 'percentage') {
                $commissionAmount = $saleAmount * ((float) $scheme->rate / 100);
            } else {
                $commissionAmount = (float) $scheme->rate;
            }

            $log = SalesCommissionLog::create([
                'employee_id' => $employee->id,
                'sale_id' => $sale->id,
                'commission_scheme_id' => $scheme->id,
                'date' => $sale->created_at ? $sale->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'sale_amount' => $saleAmount,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
                'notes' => "Komisi otomatis transaksi #{$sale->id} ({$scheme->name})",
            ]);

            return $log;
        });
    }
}
