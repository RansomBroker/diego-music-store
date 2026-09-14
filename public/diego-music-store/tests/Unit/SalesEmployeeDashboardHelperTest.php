<?php

namespace Tests\Unit;

use App\Helpers\SalesEmployeeDashboardHelper;
use PHPUnit\Framework\TestCase;

class SalesEmployeeDashboardHelperTest extends TestCase
{
    public function test_calculate_daily_target_divides_monthly_by_working_days()
    {
        $monthlyTarget = 25000000.0;
        $dailyTarget = SalesEmployeeDashboardHelper::calculateDailyTarget($monthlyTarget, 25);

        $this->assertEquals(1000000.0, $dailyTarget);
    }

    public function test_calculate_achievement_percent_caps_at_100()
    {
        $percent = SalesEmployeeDashboardHelper::calculateAchievementPercent(30000000, 25000000);
        $this->assertEquals(100, $percent);

        $halfPercent = SalesEmployeeDashboardHelper::calculateAchievementPercent(12500000, 25000000);
        $this->assertEquals(50, $halfPercent);
    }

    public function test_calculate_commission_tier_progress_returns_correct_next_tier()
    {
        $currentSales = 15000000.0; // Achieved Tier 1 (10jt), working towards Tier 2 (25jt)
        $tierInfo = SalesEmployeeDashboardHelper::calculateCommissionTierProgress($currentSales);

        $this->assertEquals('Tier Bronze', $tierInfo['current_tier']);
        $this->assertEquals('Tier Silver', $tierInfo['next_tier']);
        $this->assertEquals(10000000.0, $tierInfo['remaining_to_next']);
    }

    public function test_format_rupiah_formats_properly()
    {
        $formatted = SalesEmployeeDashboardHelper::formatRupiah(2500000);
        $this->assertEquals('Rp 2.500.000', $formatted);
    }
}
