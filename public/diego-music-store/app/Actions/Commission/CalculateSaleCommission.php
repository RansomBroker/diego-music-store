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

            $date = $sale->invoice_date ? \Illuminate\Support\Carbon::parse($sale->invoice_date)->format('Y-m-d') : ($sale->created_at ? $sale->created_at->format('Y-m-d') : now()->format('Y-m-d'));

            // Check for product-specific focus schemes in sale items
            $sale->loadMissing('items.variant');
            $bonusProductCommission = 0.0;
            $bonusDetails = [];

            foreach ($sale->items as $item) {
                $productId = $item->variant?->product_id;
                if ($productId) {
                    $prodScheme = CommissionScheme::where('is_active', true)
                        ->where('applies_to', 'product')
                        ->where('target_product_id', $productId)
                        ->where(function ($q) use ($sale, $employee) {
                            $q->where(function ($sub) use ($employee) {
                                $sub->where('employee_id', $employee->id)
                                    ->orWhereHas('employees', fn($e) => $e->where('employees.id', $employee->id));
                            })->orWhere(function ($b) use ($sale) {
                                $b->whereNull('employee_id')
                                  ->whereDoesntHave('employees')
                                  ->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $sale->branch_id));
                            });
                        })
                        ->first();

                    if ($prodScheme) {
                        $itemBonus = $prodScheme->calculation_type === 'percentage'
                            ? ((float) $item->total_price) * ((float) $prodScheme->rate / 100)
                            : ((float) $prodScheme->rate) * (int) $item->quantity;

                        $bonusProductCommission += $itemBonus;
                        $bonusDetails[] = "{$prodScheme->name} (+Rp " . number_format($itemBonus, 0, ',', '.') . ")";
                    }
                }
            }

            $commissionAmount += $bonusProductCommission;
            $notes = "Komisi otomatis transaksi {$sale->invoice_number} ({$scheme->name})";
            if (!empty($bonusDetails)) {
                $notes .= " | Bonus Produk Fokus: " . implode(', ', $bonusDetails);
            }

            $log = SalesCommissionLog::updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'commission_scheme_id' => $scheme->id,
                    'date' => $date,
                    'sale_amount' => $saleAmount,
                    'commission_amount' => $commissionAmount,
                    'status' => 'pending',
                    'notes' => $notes,
                ]
            );

            return $log;
        });
    }
}
