<?php

namespace Tests\Unit;

use App\Helpers\OwnerDashboardHelper;
use PHPUnit\Framework\TestCase;

class OwnerDashboardHelperTest extends TestCase
{
    public function test_calculate_pareto_correctly_identifies_top_80_percent_contributors()
    {
        $items = [
            ['name' => 'Item A', 'total_value' => 700],
            ['name' => 'Item B', 'total_value' => 200],
            ['name' => 'Item C', 'total_value' => 50],
            ['name' => 'Item D', 'total_value' => 50],
        ];

        $result = OwnerDashboardHelper::calculatePareto($items, 'total_value');

        $this->assertEquals(4, $result['total_count']);
        $this->assertEquals(1000.0, $result['grand_total_value']);
        $this->assertCount(4, $result['items']);
        $this->assertEquals('Item A', $result['items'][0]['name']);
        $this->assertTrue($result['items'][0]['is_top_80']);
    }

    public function test_predict_next_period_linear_calculates_projection()
    {
        // Linearity y = 10 * x -> [10, 20, 30, 40] -> next should be 50
        $historical = [10.0, 20.0, 30.0, 40.0];
        $nextPrediction = OwnerDashboardHelper::predictNextPeriodLinear($historical);

        $this->assertEquals(50.0, $nextPrediction);
    }

    public function test_calculate_stock_turnover_ratio()
    {
        $cogs = 100000.0;
        $avgStock = 25000.0;

        $ratio = OwnerDashboardHelper::calculateStockTurnoverRatio($cogs, $avgStock);

        $this->assertEquals(4.0, $ratio);
    }

    public function test_format_rupiah_returns_correct_string()
    {
        $formatted = OwnerDashboardHelper::formatRupiah(1500000);
        $this->assertEquals('Rp 1.500.000', $formatted);
    }
}
