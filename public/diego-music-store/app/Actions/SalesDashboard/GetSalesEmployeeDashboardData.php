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

        $employee = $user?->employee;
        $employeeId = $employee?->id;
        $userId = $user?->id;

        $now = now();
        $todayDate = $now->format('Y-m-d');
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        // 1. Target Penjualan Bulanan
        $monthlyTarget = 25000000.0; // Default target 25 jt
        if ($employeeId) {
            $scheme = CommissionScheme::where('is_active', true)
                ->where(function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId)->orWhereNull('employee_id');
                })
                ->where('min_monthly_sales_target', '>', 0)
                ->orderBy('employee_id', 'desc')
                ->first();

            if ($scheme && $scheme->min_monthly_sales_target > 0) {
                $monthlyTarget = (float) $scheme->min_monthly_sales_target;
            }
        }

        // Monthly Sales Achieved
        $monthlySales = 0.0;
        if ($employeeId) {
            $monthlySales = (float) SalesCommissionLog::where('employee_id', $employeeId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('sale_amount');
        }
        if ($monthlySales <= 0 && $userId) {
            $monthlySales = (float) Sale::where('status', 'completed')
                ->where('sales_rep_id', $userId)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');
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

        // 4. Sisa Target Unlock Tier Komisi
        $tierInfo = SalesEmployeeDashboardHelper::calculateCommissionTierProgress($monthlySales);

        // 5. Leaderboard Top 3 Sales
        $leaderboard = $this->getTopSalesLeaderboard($startOfMonth, $endOfMonth);

        // 6. Produk Fokus Bulan Ini
        $focusProducts = $this->getFocusProducts();

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
        $rankings = [];

        foreach ($salesStaff as $emp) {
            $salesVal = (float) SalesCommissionLog::where('employee_id', $emp->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('sale_amount');

            if ($salesVal <= 0 && $emp->user_id) {
                $salesVal = (float) Sale::where('status', 'completed')
                    ->where('sales_rep_id', $emp->user_id)
                    ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                    ->sum('grand_total');
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
     * 6. Focus products of the month.
     */
    private function getFocusProducts(): array
    {
        $products = Product::with('variants')
            ->where('is_active', true)
            ->take(4)
            ->get();

        $focusList = [];
        foreach ($products as $p) {
            $price = $p->variants->first()?->price ?: 0;
            $focusList[] = [
                'id'         => $p->id,
                'name'       => $p->name,
                'category'   => $p->category ?: 'Umum',
                'price'      => (float) $price,
                'incentive'  => 'Extra Komisi +1.5%',
                'image_url'  => $p->image_path ? asset('storage/' . $p->image_path) : null,
            ];
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

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $labels[] = $monthLabel;

            $val = 0.0;
            if ($employeeId) {
                $val = (float) SalesCommissionLog::where('employee_id', $employeeId)
                    ->whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->sum('sale_amount');
            }

            if ($val <= 0 && $userId) {
                $val = (float) Sale::where('status', 'completed')
                    ->where('sales_rep_id', $userId)
                    ->whereYear('invoice_date', $date->year)
                    ->whereMonth('invoice_date', $date->month)
                    ->sum('grand_total');
            }

            $values[] = $val;
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
