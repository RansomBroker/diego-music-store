<?php

namespace App\Actions\OwnerDashboard;

use App\Helpers\OwnerDashboardHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesCommissionLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetOwnerDashboardData
{
    /**
     * Execute aggregating owner dashboard metrics and charts.
     */
    public function execute(array $filters = []): array
    {
        $dateFrom        = !empty($filters['dateFrom']) ? Carbon::parse($filters['dateFrom'])->startOfDay() : now()->startOfMonth()->startOfDay();
        $dateTo          = !empty($filters['dateTo']) ? Carbon::parse($filters['dateTo'])->endOfDay() : now()->endOfDay();
        $branchId        = !empty($filters['branchId']) ? (int) $filters['branchId'] : null;
        $productCategory = !empty($filters['productCategory']) ? trim($filters['productCategory']) : null;
        $saleCategory    = !empty($filters['saleCategory']) ? trim($filters['saleCategory']) : null;

        // Base Sale Query
        $salesQuery = Sale::query()
            ->where('status', 'completed')
            ->whereDate('invoice_date', '>=', $dateFrom->format('Y-m-d'))
            ->whereDate('invoice_date', '<=', $dateTo->format('Y-m-d'));

        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        if ($saleCategory) {
            $salesQuery->where('sale_category', $saleCategory);
        }
        if ($productCategory) {
            $salesQuery->whereHas('items.variant.product', fn($q) => $q->where('category', $productCategory));
        }

        $sales = $salesQuery->with(['items.variant.product', 'customer', 'branch', 'salesRep'])->get();

        // 1. Ringkasan Keuangan
        $financialSummary = $this->getFinancialSummary($sales, $dateFrom, $dateTo, $branchId);

        // 2. Grafik Penjualan dan Pembelian
        $salesVsPurchasesChart = $this->getSalesVsPurchasesChart($dateFrom, $dateTo, $branchId);

        // 3. Grafik Turn Over Stok
        $stockTurnoverChart = $this->getStockTurnoverChart($sales, $branchId, $productCategory);

        // 4. Grafik Pareto 80/20 (Produk & Pelanggan)
        $paretoChart = $this->getParetoChart($sales);

        // 5. Grafik Tren Penjualan Bulanan & Prediksi
        $monthlyTrendChart = $this->getMonthlyTrendChart($branchId, $productCategory);

        // 6. Grafik Penjualan Per-Kategori
        $categoryChart = $this->getSalesByCategoryChart($sales);

        // 7. Grafik Penjualan Per-Cabang
        $branchChart = $this->getSalesByBranchChart($dateFrom, $dateTo, $saleCategory, $productCategory);

        // 8. Grafik Monthly Report (Daily Peak/Low Traffic)
        $dailyTrafficChart = $this->getDailyTrafficChart($sales, $dateFrom, $dateTo);

        // 9. Grafik Waktu Pengunjung (Hourly Traffic Jam)
        $hourlyTrafficChart = $this->getHourlyTrafficChart($sales);

        // 10. Grafik Performa Sales
        $salesPerformanceChart = $this->getSalesPerformanceChart($sales, $dateFrom, $dateTo, $branchId);

        return [
            'filters' => [
                'dateFrom'        => $dateFrom->format('Y-m-d'),
                'dateTo'          => $dateTo->format('Y-m-d'),
                'branchId'        => $branchId,
                'productCategory' => $productCategory,
                'saleCategory'    => $saleCategory,
            ],
            'financialSummary'      => $financialSummary,
            'salesVsPurchasesChart' => $salesVsPurchasesChart,
            'stockTurnoverChart'    => $stockTurnoverChart,
            'paretoChart'           => $paretoChart,
            'monthlyTrendChart'     => $monthlyTrendChart,
            'categoryChart'         => $categoryChart,
            'branchChart'           => $branchChart,
            'dailyTrafficChart'     => $dailyTrafficChart,
            'hourlyTrafficChart'    => $hourlyTrafficChart,
            'salesPerformanceChart' => $salesPerformanceChart,
        ];
    }

    /**
     * 1. Calculate Financial Summary totals.
     */
    private function getFinancialSummary($sales, Carbon $dateFrom, Carbon $dateTo, ?int $branchId): array
    {
        $totalSales = (float) $sales->sum('grand_total');

        // Total Expenses
        $expenseQuery = CashTransaction::query()
            ->whereIn('type', ['cash_out', 'expense'])
            ->whereBetween('transaction_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $totalExpenses = (float) $expenseQuery->sum('amount');

        // Accounts Payable (Unpaid credit purchases)
        $purchaseQuery = PurchaseTransaction::query()
            ->where('purchase_type', 'Kredit')
            ->whereIn('status', ['posted', 'completed', 'approved']);
        if ($branchId) {
            $purchaseQuery->where('branch_id', $branchId);
        }
        $purchases = $purchaseQuery->get();
        $totalHutang = 0.0;
        foreach ($purchases as $purch) {
            $totalHutang += $purch->getRemainingUnpaidAmount();
        }

        // Cash & Bank Balance
        $accounts = Account::where(function ($q) {
            $q->where('classification', 'like', '%kas%')
                ->orWhere('classification', 'like', '%bank%')
                ->orWhere('code', 'like', '1-10%')
                ->orWhere('code', 'like', '1-11%');
        })->get();

        $saldoKasBank = 0.0;
        foreach ($accounts as $acc) {
            $saldoKasBank += (float) $acc->balance;
        }

        return [
            'total_penjualan'   => $totalSales,
            'total_pengeluaran' => $totalExpenses,
            'total_hutang'      => $totalHutang,
            'saldo_kas_bank'    => $saldoKasBank,
            'laba_kotor_est'    => $this->calculateGrossProfit($sales),
        ];
    }

    private function calculateGrossProfit($sales): float
    {
        $profit = 0.0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $variant = $item->variant;
                $hpp = $variant ? ($variant->hpp ?: $variant->cost_price ?: 0) : 0;
                $profit += (($item->unit_price - $hpp) * $item->quantity);
            }
        }
        return (float) max(0, $profit);
    }

    /**
     * 2. Sales vs Purchases comparison chart.
     */
    private function getSalesVsPurchasesChart(Carbon $dateFrom, Carbon $dateTo, ?int $branchId): array
    {
        $months = [];
        $salesData = [];
        $purchasesData = [];

        $current = $dateFrom->copy()->startOfMonth();
        $end = $dateTo->copy()->endOfMonth();

        while ($current->lte($end)) {
            $monthKey = $current->format('Y-m');
            $monthLabel = $current->translatedFormat('M Y');
            $months[] = $monthLabel;

            // Monthly sales sum
            $sQuery = Sale::where('status', 'completed')
                ->whereYear('invoice_date', $current->year)
                ->whereMonth('invoice_date', $current->month);
            if ($branchId) {
                $sQuery->where('branch_id', $branchId);
            }
            $salesData[] = (float) $sQuery->sum('grand_total');

            // Monthly purchase sum
            $pQuery = PurchaseTransaction::whereIn('status', ['posted', 'completed'])
                ->whereYear('transaction_date', $current->year)
                ->whereMonth('transaction_date', $current->month);
            if ($branchId) {
                $pQuery->where('branch_id', $branchId);
            }
            $purchasesData[] = (float) $pQuery->sum('grand_total');

            $current->addMonth();
        }

        return [
            'labels'    => $months,
            'sales'     => $salesData,
            'purchases' => $purchasesData,
        ];
    }

    /**
     * 3. Stock Turnover Chart by Category.
     */
    private function getStockTurnoverChart($sales, ?int $branchId, ?string $productCategory): array
    {
        $cogsByCategory = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $cat = $item->variant?->product?->category ?: 'Umum';
                if ($productCategory && strtolower($cat) !== strtolower($productCategory)) {
                    continue;
                }
                $hpp = $item->variant?->hpp ?: $item->variant?->cost_price ?: 0;
                $cogsByCategory[$cat] = ($cogsByCategory[$cat] ?? 0) + ($hpp * $item->quantity);
            }
        }

        // Compute Stock Value by Category
        $stockQuery = ProductBranchStock::with(['productVariant.product']);
        if ($branchId) {
            $stockQuery->where('branch_id', $branchId);
        }
        $stocks = $stockQuery->get();

        $stockValueByCategory = [];
        foreach ($stocks as $st) {
            $variant = $st->productVariant ?? $st->variant;
            $cat = $variant?->product?->category ?: 'Umum';
            $hpp = $variant?->hpp ?: $variant?->cost_price ?: 0;
            $stockValueByCategory[$cat] = ($stockValueByCategory[$cat] ?? 0) + ($st->stock * $hpp);
        }

        $allCategories = array_unique(array_merge(array_keys($cogsByCategory), array_keys($stockValueByCategory)));
        sort($allCategories);

        $turnoverRatios = [];
        $speedLabels = [];

        foreach ($allCategories as $cat) {
            $cogs = $cogsByCategory[$cat] ?? 0.0;
            $stockVal = $stockValueByCategory[$cat] ?? 0.0;
            $ratio = OwnerDashboardHelper::calculateStockTurnoverRatio($cogs, $stockVal);
            $turnoverRatios[] = $ratio;

            if ($ratio >= 3.0) {
                $speed = 'Cepat (High Turn)';
            } elseif ($ratio >= 1.0) {
                $speed = 'Sedang (Medium Turn)';
            } else {
                $speed = 'Lambat (Low Turn)';
            }
            $speedLabels[] = $speed;
        }

        return [
            'labels'         => $allCategories,
            'turnover_ratios' => $turnoverRatios,
            'speed_labels'   => $speedLabels,
        ];
    }

    /**
     * 4. Pareto 80/20 Chart for Products & Customers.
     */
    private function getParetoChart($sales): array
    {
        // Pareto Products by Profit & Revenue
        $productStats = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $pName = $item->variant?->product?->name ?? 'Produk Unnamed';
                $hpp = $item->variant?->hpp ?: $item->variant?->cost_price ?: 0;
                $profit = ($item->unit_price - $hpp) * $item->quantity;

                if (!isset($productStats[$pName])) {
                    $productStats[$pName] = [
                        'name'        => $pName,
                        'total_value' => 0.0,
                        'total_profit' => 0.0,
                        'total_qty'   => 0,
                    ];
                }
                $productStats[$pName]['total_value'] += $item->subtotal;
                $productStats[$pName]['total_profit'] += $profit;
                $productStats[$pName]['total_qty'] += $item->quantity;
            }
        }
        $paretoProducts = OwnerDashboardHelper::calculatePareto(array_values($productStats), 'total_value');

        // Pareto Customers
        $customerStats = [];
        foreach ($sales as $sale) {
            $cName = $sale->customer?->name ?? 'Pelanggan Umum (Walk-in)';
            if (!isset($customerStats[$cName])) {
                $customerStats[$cName] = [
                    'name'        => $cName,
                    'total_value' => 0.0,
                ];
            }
            $customerStats[$cName]['total_value'] += $sale->grand_total;
        }
        $paretoCustomers = OwnerDashboardHelper::calculatePareto(array_values($customerStats), 'total_value');

        return [
            'products'  => $paretoProducts,
            'customers' => $paretoCustomers,
        ];
    }

    /**
     * 5. Monthly Sales Trend Chart & Forecast next month.
     */
    private function getMonthlyTrendChart(?int $branchId, ?string $productCategory): array
    {
        $labels = [];
        $actualData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $labels[] = $monthLabel;

            $query = Sale::where('status', 'completed')
                ->whereYear('invoice_date', $date->year)
                ->whereMonth('invoice_date', $date->month);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($productCategory) {
                $query->whereHas('items.variant.product', fn($q) => $q->where('category', $productCategory));
            }

            $actualData[] = (float) $query->sum('grand_total');
        }

        // Forecast next month
        $predictedNextMonth = OwnerDashboardHelper::predictNextPeriodLinear($actualData);
        $nextMonthLabel = now()->addMonth()->translatedFormat('M Y') . ' (Prediksi)';

        $forecastLabels = array_merge($labels, [$nextMonthLabel]);
        $forecastData = array_merge($actualData, [$predictedNextMonth]);

        return [
            'labels'             => $labels,
            'actual'             => $actualData,
            'forecast_labels'    => $forecastLabels,
            'forecast_data'      => $forecastData,
            'predicted_next_val' => $predictedNextMonth,
        ];
    }

    /**
     * 6. Sales & Profit by Category Chart.
     */
    private function getSalesByCategoryChart($sales): array
    {
        $catData = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $cat = $item->variant?->product?->category ?: 'Umum';
                $hpp = $item->variant?->hpp ?: $item->variant?->cost_price ?: 0;
                $profit = ($item->unit_price - $hpp) * $item->quantity;

                if (!isset($catData[$cat])) {
                    $catData[$cat] = [
                        'revenue' => 0.0,
                        'profit'  => 0.0,
                    ];
                }
                $catData[$cat]['revenue'] += $item->subtotal;
                $catData[$cat]['profit'] += $profit;
            }
        }

        ksort($catData);

        return [
            'labels'   => array_keys($catData),
            'revenues' => array_column($catData, 'revenue'),
            'profits'  => array_column($catData, 'profit'),
        ];
    }

    /**
     * 7. Sales & Profit by Branch Chart.
     */
    private function getSalesByBranchChart(Carbon $dateFrom, Carbon $dateTo, ?string $saleCategory, ?string $productCategory): array
    {
        $branches = Branch::where('is_active', true)->get();
        $labels = [];
        $revenues = [];
        $profits = [];

        foreach ($branches as $branch) {
            $bQuery = Sale::where('status', 'completed')
                ->where('branch_id', $branch->id)
                ->whereBetween('invoice_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);

            if ($saleCategory) {
                $bQuery->where('sale_category', $saleCategory);
            }
            if ($productCategory) {
                $bQuery->whereHas('items.variant.product', fn($q) => $q->where('category', $productCategory));
            }

            $bSales = $bQuery->with('items.variant')->get();
            $rev = (float) $bSales->sum('grand_total');
            $prof = $this->calculateGrossProfit($bSales);

            $labels[]   = $branch->name;
            $revenues[] = $rev;
            $profits[]  = $prof;
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'profits'  => $profits,
        ];
    }

    /**
     * 8. Daily Visitor Traffic Chart (Low & Peak).
     */
    private function getDailyTrafficChart($sales, Carbon $dateFrom, Carbon $dateTo): array
    {
        $dailyCounts = [];
        $dailyTotals = [];

        $current = $dateFrom->copy();
        while ($current->lte($dateTo)) {
            $dayKey = $current->format('d M');
            $dailyCounts[$dayKey] = 0;
            $dailyTotals[$dayKey] = 0.0;
            $current->addDay();
        }

        foreach ($sales as $sale) {
            $dayKey = Carbon::parse($sale->invoice_date)->format('d M');
            if (isset($dailyCounts[$dayKey])) {
                $dailyCounts[$dayKey]++;
                $dailyTotals[$dayKey] += $sale->grand_total;
            }
        }

        $peakDay = !empty($dailyCounts) ? array_search(max($dailyCounts), $dailyCounts) : '-';
        $lowDay  = !empty($dailyCounts) ? array_search(min($dailyCounts), $dailyCounts) : '-';

        return [
            'labels'       => array_keys($dailyCounts),
            'counts'       => array_values($dailyCounts),
            'totals'       => array_values($dailyTotals),
            'peak_day'     => $peakDay,
            'peak_count'   => !empty($dailyCounts) ? max($dailyCounts) : 0,
            'low_day'      => $lowDay,
            'low_count'    => !empty($dailyCounts) ? min($dailyCounts) : 0,
        ];
    }

    /**
     * 9. Hourly Visitor Traffic Jam Chart.
     */
    private function getHourlyTrafficChart($sales): array
    {
        $hours = [];
        for ($h = 8; $h <= 21; $h++) {
            $hours[sprintf('%02d:00', $h)] = 0;
        }

        foreach ($sales as $sale) {
            $hourKey = sprintf('%02d:00', Carbon::parse($sale->created_at)->hour);
            if (isset($hours[$hourKey])) {
                $hours[$hourKey]++;
            }
        }

        $busyHour = !empty($hours) ? array_search(max($hours), $hours) : '-';

        return [
            'labels'    => array_keys($hours),
            'counts'    => array_values($hours),
            'busy_hour' => $busyHour,
            'busy_count' => !empty($hours) ? max($hours) : 0,
        ];
    }

    /**
     * 10. Sales Employee Performance Chart.
     */
    private function getSalesPerformanceChart($sales, Carbon $dateFrom, Carbon $dateTo, ?int $branchId): array
    {
        $salesStaff = Employee::with(['user'])
            ->where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        $labels = [];
        $achievedSales = [];
        $targetSales   = [];
        $attendancePercents = [];

        $totalDaysInPeriod = max(1, $dateFrom->diffInDays($dateTo) + 1);

        foreach ($salesStaff as $emp) {
            $empSales = (float) SalesCommissionLog::where('employee_id', $emp->id)
                ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                ->sum('sale_amount');

            // Fallback sum from Sale model if no log entry
            if ($empSales <= 0 && $emp->user_id) {
                $empSales = (float) $sales->where('sales_rep_id', $emp->user_id)->sum('grand_total');
            }

            $target = 25000000.0; // Default target
            $hadirCount = EmployeeAttendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                ->where('status', 'hadir')
                ->count();

            $attPercent = min(100, round(($hadirCount / $totalDaysInPeriod) * 100, 1));

            $labels[]             = $emp->name;
            $achievedSales[]      = $empSales;
            $targetSales[]        = $target;
            $attendancePercents[] = $attPercent;
        }

        return [
            'labels'              => $labels,
            'achieved_sales'      => $achievedSales,
            'target_sales'        => $targetSales,
            'attendance_percents' => $attendancePercents,
        ];
    }
}
