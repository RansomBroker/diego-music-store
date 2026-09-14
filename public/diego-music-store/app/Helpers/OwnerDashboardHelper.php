<?php

namespace App\Helpers;

class OwnerDashboardHelper
{
    /**
     * Calculate Pareto 80/20 breakdown for a collection or array of items.
     * Items are sorted in descending order by valueField. Cumulative percentages are calculated.
     * Items contributing up to ~80% of total sum are marked as Pareto Class 'A' (Top Contributor).
     */
    public static function calculatePareto(array $items, string $valueField = 'total_value'): array
    {
        if (empty($items)) {
            return [
                'items'             => [],
                'top_80_count'      => 0,
                'total_count'       => 0,
                'top_80_value'      => 0.0,
                'grand_total_value' => 0.0,
                'pareto_ratio'      => 0.0,
            ];
        }

        // Sort items by value descending
        usort($items, fn($a, $b) => ($b[$valueField] ?? 0) <=> ($a[$valueField] ?? 0));

        $grandTotal = array_sum(array_column($items, $valueField));
        if ($grandTotal <= 0) {
            $grandTotal = 1.0; // avoid division by zero
        }

        $cumulative = 0.0;
        $top80Count = 0;
        $top80Value = 0.0;
        $processed  = [];

        foreach ($items as $item) {
            $val = (float) ($item[$valueField] ?? 0);
            $cumulative += $val;
            $cumPercent = round(($cumulative / $grandTotal) * 100, 2);

            $isTop80 = ($cumPercent <= 80.0) || ($top80Count === 0);

            if ($isTop80) {
                $top80Count++;
                $top80Value += $val;
                $categoryGroup = 'A (80% Contributor)';
            } elseif ($cumPercent <= 95.0) {
                $categoryGroup = 'B (15% Contributor)';
            } else {
                $categoryGroup = 'C (5% Contributor)';
            }

            $item['value']             = $val;
            $item['percentage']        = round(($val / $grandTotal) * 100, 2);
            $item['cumulative_percent'] = $cumPercent;
            $item['pareto_class']      = $categoryGroup;
            $item['is_top_80']         = $isTop80;

            $processed[] = $item;
        }

        return [
            'items'            => $processed,
            'top_80_count'     => $top80Count,
            'total_count'      => count($items),
            'top_80_value'     => $top80Value,
            'grand_total_value' => $grandTotal,
            'pareto_ratio'     => count($items) > 0 ? round(($top80Count / count($items)) * 100, 1) : 0,
        ];
    }

    /**
     * Forecast the next period value using Linear Regression (y = m*x + c).
     */
    public static function predictNextPeriodLinear(array $historicalValues): float
    {
        $n = count($historicalValues);
        if ($n === 0) {
            return 0.0;
        }
        if ($n === 1) {
            return (float) max(0, reset($historicalValues));
        }

        $sumX  = 0;
        $sumY  = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = (float) $historicalValues[$i];

            $sumX  += $x;
            $sumY  += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator == 0) {
            return (float) max(0, array_sum($historicalValues) / $n);
        }

        $slope     = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        $nextX          = $n + 1;
        $predictedValue = ($slope * $nextX) + $intercept;

        return (float) max(0, round($predictedValue, 2));
    }

    /**
     * Calculate Stock Turnover Ratio (HPP / Average Inventory Value).
     */
    public static function calculateStockTurnoverRatio(float $cogs, float $avgStockValue): float
    {
        if ($avgStockValue <= 0) {
            return 0.0;
        }

        return round($cogs / $avgStockValue, 2);
    }

    /**
     * Format Rupiah helper.
     */
    public static function formatRupiah(float|int $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Format percentage helper.
     */
    public static function formatPercentage(float|int $value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals, ',', '.') . '%';
    }
}
