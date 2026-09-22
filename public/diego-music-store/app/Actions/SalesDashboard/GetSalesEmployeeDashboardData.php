<?php

namespace App\Actions\SalesDashboard;

use App\Helpers\SalesEmployeeDashboardHelper;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class GetSalesEmployeeDashboardData
{
    /**
     * Execute aggregating sales employee dashboard metrics and charts.
     */
    public function execute(?User $user = null): array
    {
        if (!$user) {
            $user = auth()->user();
        }

        $employee = $user?->employee ?: ($user ? Employee::where('user_id', $user->id)->first() : null);
        $employeeId = $employee?->id;
        $userId = $user?->id;

        $now = now();
        $todayDate = $now->format('Y-m-d');
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        // 1. Target Penjualan Bulanan & Skema Komisi Dinamis
        $branchId = $user?->branch_id ?: $employee?->branch_id;
        if (!$branchId && $userId) {
            $branchId = \App\Models\CashSession::where('user_id', $userId)->where('status', 'open')->value('branch_id')
                ?: \App\Models\Sale::where('sales_rep_id', $userId)->latest()->value('branch_id')
                ?: \App\Models\Branch::first()?->id;
        }

        $activeSchemes = CommissionScheme::where('is_active', true)
            ->where(function ($q) use ($employeeId, $branchId) {
                if ($employeeId) {
                    $q->where('employee_id', $employeeId)
                      ->orWhereHas('employees', function ($sub) use ($employeeId) {
                          $sub->where('employees.id', $employeeId);
                      });
                }
                if ($branchId) {
                    $q->orWhere(function ($b) use ($branchId) {
                        $b->where('branch_id', $branchId)
                          ->whereNull('employee_id')
                          ->whereDoesntHave('employees');
                    });
                }
                $q->orWhere(function ($g) {
                    $g->whereNull('branch_id')
                      ->whereNull('employee_id')
                      ->whereDoesntHave('employees');
                });
            })
            ->get();

        $targetSchemes = $activeSchemes->filter(fn($s) => (float)$s->min_monthly_sales_target > 0)->sortBy('min_monthly_sales_target');

        $monthlyTarget = 25000000.0; // Default target
        if ($targetSchemes->isNotEmpty()) {
            $monthlyTarget = (float) $targetSchemes->first()->min_monthly_sales_target;
        }

        // Monthly Sales Achieved: Sumber utama adalah transaksi riil (Sale)
        $monthlySales = 0.0;
        if ($userId) {
            $monthlySales = (float) Sale::where('status', 'completed')
                ->where('sales_rep_id', $userId)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');
        }
        if ($monthlySales <= 0 && $employeeId) {
            $monthlySales = (float) SalesCommissionLog::where('employee_id', $employeeId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('sale_amount');
        }

        $monthlyProgressPercent = SalesEmployeeDashboardHelper::calculateAchievementPercent($monthlySales, $monthlyTarget);

        // 2. Target Penjualan Harian
        $dailyTarget = SalesEmployeeDashboardHelper::calculateDailyTarget($monthlyTarget, 25);
        $todaySales = 0.0;
        if ($userId) {
            $todaySales = (float) Sale::where('status', 'completed')
                ->where('sales_rep_id', $userId)
                ->where('invoice_date', $todayDate)
                ->sum('grand_total');
        }
        $dailyProgressPercent = SalesEmployeeDashboardHelper::calculateAchievementPercent($todaySales, $dailyTarget);

        $dailyStatus = 'Need Effort';
        if ($dailyProgressPercent >= 100) {
            $dailyStatus = 'Target Tercapai 🎉';
        } elseif ($dailyProgressPercent >= 50) {
            $dailyStatus = 'On Track 👍';
        }

        // 3. Komisi Penjualan
        $monthlyCommission = 0.0;
        if ($employeeId) {
            $monthlyCommission = (float) SalesCommissionLog::where('employee_id', $employeeId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('commission_amount');
        }

        // 4. Sisa Target Unlock Tier Komisi Berdasarkan Skema Riil
        $customTiers = [];
        $tierIndex = 1;
        foreach ($targetSchemes as $ts) {
            $customTiers[] = [
                'tier'      => $tierIndex++,
                'name'      => $ts->name,
                'min_sales' => (float) $ts->min_monthly_sales_target,
                'rate'      => (float) $ts->rate,
            ];
        }
        $baseRate = (float) ($activeSchemes->first()?->rate ?? 0.0);
        $tierInfo = SalesEmployeeDashboardHelper::calculateCommissionTierProgress($monthlySales, !empty($customTiers) ? $customTiers : null, $baseRate);

        // 5. Leaderboard Top 3 Sales
        $leaderboard = $this->getTopSalesLeaderboard($startOfMonth, $endOfMonth);

        // 6. Produk Fokus Bulan Ini
        $focusProducts = $this->getFocusProducts($branchId, $employeeId);

        // 7. Grafik Performa Sales (1 Tahun / 12 Bulan)
        $yearlyPerformanceChart = $this->getYearlySalesPerformance($userId, $employeeId);

        // 8. Info / Bar Absensi
        $attendanceInfo = $this->getAttendanceInfo($employee, $startOfMonth, $endOfMonth);

        return [
            'monthlyTarget'          => [
                'target_amount'    => $monthlyTarget,
                'achieved_amount'  => $monthlySales,
                'progress_percent' => $monthlyProgressPercent,
            ],
            'dailyTarget'            => [
                'target_amount'    => $dailyTarget,
                'achieved_amount'  => $todaySales,
                'progress_percent' => $dailyProgressPercent,
                'status'           => $dailyStatus,
            ],
            'monthlyCommission'      => $monthlyCommission,
            'tierInfo'               => $tierInfo,
            'leaderboard'            => $leaderboard,
            'focusProducts'          => $focusProducts,
            'yearlyPerformanceChart' => $yearlyPerformanceChart,
            'attendanceInfo'         => $attendanceInfo,
        ];
    }

    /**
     * 5. Top 3 Sales Leaderboard of the current month.
     */
    private function getTopSalesLeaderboard(string $startOfMonth, string $endOfMonth): array
    {
        $salesStaff = Employee::with('user')->where('is_active', true)->get();
        $userIds = $salesStaff->pluck('user_id')->filter()->toArray();
        $employeeIds = $salesStaff->pluck('id')->toArray();

        $salesByUser = Sale::where('status', 'completed')
            ->whereIn('sales_rep_id', $userIds)
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('sales_rep_id, SUM(grand_total) as total')
            ->groupBy('sales_rep_id')
            ->pluck('total', 'sales_rep_id');

        $logsByEmployee = SalesCommissionLog::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw('employee_id, SUM(sale_amount) as total')
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id');

        $rankings = [];
        foreach ($salesStaff as $emp) {
            $salesVal = (float) ($emp->user_id ? ($salesByUser->get($emp->user_id) ?: 0.0) : 0.0);
            if ($salesVal <= 0) {
                $salesVal = (float) ($logsByEmployee->get($emp->id) ?: 0.0);
            }

            $rankings[] = [
                'employee_id' => $emp->id,
                'name'        => $emp->name,
                'sales_val'   => $salesVal,
            ];
        }

        usort($rankings, fn($a, $b) => $b['sales_val'] <=> $a['sales_val']);

        $top3 = array_slice($rankings, 0, 3);

        $medals = ['🥇', '🥈', '🥉'];
        $badgeNames = ['Gold', 'Silver', 'Bronze'];
        foreach ($top3 as $i => &$rank) {
            $rank['rank']       = $i + 1;
            $rank['medal']      = $medals[$i] ?? '🏅';
            $rank['badge_name'] = $badgeNames[$i] ?? 'Rank ' . ($i + 1);
        }

        return $top3;
    }

    /**
     * 6. Focus products of the month based on active commission schemes.
     */
    private function getFocusProducts(?int $branchId = null, ?int $employeeId = null): array
    {
        // 1. Ambil skema aktif yang menargetkan produk spesifik
        $productSchemes = CommissionScheme::with(['targetProduct.variants'])
            ->where('is_active', true)
            ->where('applies_to', 'product')
            ->whereNotNull('target_product_id')
            ->where(function ($q) use ($branchId, $employeeId) {
                if ($employeeId) {
                    $q->where('employee_id', $employeeId)
                      ->orWhereHas('employees', fn($sub) => $sub->where('employees.id', $employeeId));
                }
                if ($branchId) {
                    $q->orWhere('branch_id', $branchId);
                }
                $q->orWhere(function ($g) {
                    $g->whereNull('branch_id')->whereNull('employee_id')->whereDoesntHave('employees');
                });
            })
            ->get();

        $focusList = [];
        $seenProductIds = [];

        foreach ($productSchemes as $scheme) {
            $product = $scheme->targetProduct;
            if (!$product || in_array($product->id, $seenProductIds)) {
                continue;
            }
            $seenProductIds[] = $product->id;

            $price = $product->variants->first()?->price ?: 0;
            $incentiveLabel = $scheme->calculation_type === 'percentage'
                ? "+{$scheme->rate}% Komisi"
                : "+Rp " . number_format($scheme->rate, 0, ',', '.');

            $focusList[] = [
                'id'         => $product->id,
                'name'       => $product->name,
                'category'   => $product->category ?: 'Produk Fokus',
                'price'      => (float) $price,
                'incentive'  => $incentiveLabel,
                'image_url'  => $product->image_path ? asset('storage/' . $product->image_path) : null,
            ];
        }

        // 2. Ambil skema aktif yang menargetkan kategori spesifik
        $categorySchemes = CommissionScheme::with('targetCategory')
            ->where('is_active', true)
            ->where('applies_to', 'category')
            ->whereNotNull('target_sale_category_id')
            ->where(function ($q) use ($branchId, $employeeId) {
                if ($employeeId) {
                    $q->where('employee_id', $employeeId)
                      ->orWhereHas('employees', fn($sub) => $sub->where('employees.id', $employeeId));
                }
                if ($branchId) {
                    $q->orWhere('branch_id', $branchId);
                }
                $q->orWhere(function ($g) {
                    $g->whereNull('branch_id')->whereNull('employee_id')->whereDoesntHave('employees');
                });
            })
            ->get();

        foreach ($categorySchemes as $catScheme) {
            $catName = $catScheme->targetCategory?->name;
            $incentiveLabel = $catScheme->calculation_type === 'percentage'
                ? "+{$catScheme->rate}% Komisi"
                : "+Rp " . number_format($catScheme->rate, 0, ',', '.');

            $catProducts = Product::with('variants')
                ->where('is_active', true)
                ->where(function ($q) use ($catName) {
                    if ($catName) {
                        $q->where('category', 'like', "%{$catName}%");
                    }
                })
                ->whereNotIn('id', $seenProductIds)
                ->take(3)
                ->get();

            foreach ($catProducts as $p) {
                $seenProductIds[] = $p->id;
                $price = $p->variants->first()?->price ?: 0;
                $focusList[] = [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'category'   => $p->category ?: $catName,
                    'price'      => (float) $price,
                    'incentive'  => $incentiveLabel,
                    'image_url'  => $p->image_path ? asset('storage/' . $p->image_path) : null,
                ];
            }
        }

        return $focusList;
    }

    /**
     * 7. Yearly sales revenue performance chart for logged-in sales staff (12 months).
     */
    private function getYearlySalesPerformance(?int $userId, ?int $employeeId): array
    {
        $labels = [];
        $values = [];

        $startYearMonth = now()->subMonths(11)->startOfMonth()->format('Y-m-d');
        $endYearMonth = now()->endOfMonth()->format('Y-m-d');

        $salesByMonth = collect();
        if ($userId) {
            $salesByMonth = Sale::where('status', 'completed')
                ->where('sales_rep_id', $userId)
                ->whereBetween('invoice_date', [$startYearMonth, $endYearMonth])
                ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(grand_total) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');
        }

        $logsByMonth = collect();
        if ($employeeId && $salesByMonth->isEmpty()) {
            $logsByMonth = SalesCommissionLog::where('employee_id', $employeeId)
                ->whereBetween('date', [$startYearMonth, $endYearMonth])
                ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, SUM(sale_amount) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');
        }

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $ymKey = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (float) ($salesByMonth->get($ymKey) ?: ($logsByMonth->get($ymKey) ?: 0.0));
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * 8. Attendance Info & Bar.
     */
    private function getAttendanceInfo(?Employee $employee, string $startOfMonth, string $endOfMonth): array
    {
        if (!$employee) {
            return [
                'total_hadir'            => 0,
                'used_off_days'          => 0,
                'monthly_off_days_quota' => 4,
                'is_over_quota'          => false,
                'attendance_percent'     => 0,
            ];
        }

        $hadirCount = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'hadir')
            ->count();

        $usedOffDays = (int) ($employee->used_off_days_this_month ?? 0);
        $quotaOffDays = (int) ($employee->monthly_off_days_quota ?? 4);

        return [
            'total_hadir'            => $hadirCount,
            'used_off_days'          => $usedOffDays,
            'monthly_off_days_quota' => $quotaOffDays,
            'is_over_quota'          => $usedOffDays > $quotaOffDays,
            'attendance_percent'     => min(100, (int) round(($hadirCount / 25) * 100)),
        ];
    }
}
