<?php

namespace App\Helpers;

class SalesEmployeeDashboardHelper
{
    /**
     * Calculate daily target based on monthly target and effective working days in a month.
     */
    public static function calculateDailyTarget(float $monthlyTarget, int $workingDays = 25): float
    {
        if ($monthlyTarget <= 0) {
            return 0.0;
        }

        return round($monthlyTarget / max(1, $workingDays), 2);
    }

    /**
     * Calculate achievement percentage capped at 100%.
     */
    public static function calculateAchievementPercent(float $actual, float $target): int
    {
        if ($target <= 0) {
            return 0;
        }

        return (int) min(100, max(0, round(($actual / $target) * 100)));
    }

    /**
     * Calculate commission tier progress and remaining amount to unlock the next tier.
     */
    public static function calculateCommissionTierProgress(float $currentMonthlySales, ?array $tiers = null): array
    {
        if (empty($tiers)) {
            $tiers = [
                ['tier' => 1, 'name' => 'Tier Bronze', 'min_sales' => 10000000.0, 'rate' => 1.0],
                ['tier' => 2, 'name' => 'Tier Silver', 'min_sales' => 25000000.0, 'rate' => 2.0],
                ['tier' => 3, 'name' => 'Tier Gold',   'min_sales' => 50000000.0, 'rate' => 3.5],
            ];
        }

        usort($tiers, fn($a, $b) => $a['min_sales'] <=> $b['min_sales']);

        $currentTier = null;
        $nextTier    = null;

        foreach ($tiers as $index => $t) {
            if ($currentMonthlySales >= $t['min_sales']) {
                $currentTier = $t;
            } else {
                if ($nextTier === null) {
                    $nextTier = $t;
                }
            }
        }

        if ($currentTier === null) {
            $nextTier = $tiers[0];
            $remainingToNext = max(0, $nextTier['min_sales'] - $currentMonthlySales);
            $tierProgress = self::calculateAchievementPercent($currentMonthlySales, $nextTier['min_sales']);
        } elseif ($nextTier !== null) {
            $remainingToNext = max(0, $nextTier['min_sales'] - $currentMonthlySales);
            $tierProgress = self::calculateAchievementPercent($currentMonthlySales, $nextTier['min_sales']);
        } else {
            // Max tier reached
            $remainingToNext = 0.0;
            $tierProgress = 100;
        }

        return [
            'current_tier'      => $currentTier ? $currentTier['name'] : 'Basic',
            'current_rate'      => $currentTier ? $currentTier['rate'] : 0.5,
            'next_tier'         => $nextTier ? $nextTier['name'] : 'Maksimum Tier (Gold)',
            'next_tier_target'  => $nextTier ? $nextTier['min_sales'] : ($currentTier ? $currentTier['min_sales'] : 0.0),
            'remaining_to_next' => $remainingToNext,
            'tier_progress'     => $tierProgress,
            'all_tiers'         => $tiers,
        ];
    }

    /**
     * Format Rupiah helper.
     */
    public static function formatRupiah(float|int $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}
