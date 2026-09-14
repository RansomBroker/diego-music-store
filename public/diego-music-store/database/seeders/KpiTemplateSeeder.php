<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\KpiTemplate;
use Illuminate\Database\Seeder;

class KpiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Template for Sales Executive
        KpiTemplate::firstOrCreate(
            ['name' => 'KPI Sales Executive Default'],
            [
                'position' => 'Sales Executive',
                'max_bonus_amount' => 500000,
                'target_sales_amount' => 10000000,
                'weight_sales' => 40.00,
                'target_atv_amount' => 250000,
                'weight_atv' => 20.00,
                'target_attendance_pct' => 95.00,
                'weight_attendance' => 20.00,
                'target_punctuality_pct' => 95.00,
                'weight_punctuality' => 20.00,
                'bonus_tiering_rules' => [
                    ['min_score' => 90, 'bonus_pct' => 100],
                    ['min_score' => 75, 'bonus_pct' => 75],
                    ['min_score' => 0, 'bonus_pct' => 0],
                ],
                'is_active' => true,
            ]
        );

        // 2. Template for Cashier / Kasir
        KpiTemplate::firstOrCreate(
            ['name' => 'KPI Kasir / Cashier Default'],
            [
                'position' => 'Cashier',
                'max_bonus_amount' => 350000,
                'target_sales_amount' => 5000000,
                'weight_sales' => 20.00,
                'target_atv_amount' => 150000,
                'weight_atv' => 20.00,
                'target_attendance_pct' => 98.00,
                'weight_attendance' => 30.00,
                'target_punctuality_pct' => 98.00,
                'weight_punctuality' => 30.00,
                'bonus_tiering_rules' => [
                    ['min_score' => 90, 'bonus_pct' => 100],
                    ['min_score' => 80, 'bonus_pct' => 70],
                    ['min_score' => 0, 'bonus_pct' => 0],
                ],
                'is_active' => true,
            ]
        );

        // 3. Template for Store Manager
        KpiTemplate::firstOrCreate(
            ['name' => 'KPI Store Manager Default'],
            [
                'position' => 'Store Manager',
                'max_bonus_amount' => 1000000,
                'target_sales_amount' => 25000000,
                'weight_sales' => 50.00,
                'target_atv_amount' => 500000,
                'weight_atv' => 20.00,
                'target_attendance_pct' => 95.00,
                'weight_attendance' => 15.00,
                'target_punctuality_pct' => 95.00,
                'weight_punctuality' => 15.00,
                'bonus_tiering_rules' => [
                    ['min_score' => 90, 'bonus_pct' => 100],
                    ['min_score' => 75, 'bonus_pct' => 75],
                    ['min_score' => 0, 'bonus_pct' => 0],
                ],
                'is_active' => true,
            ]
        );

        // 4. Calculate initial KPI evaluations for active employees for current month
        $employees = Employee::where('is_active', true)->get();
        $action = app(\App\Actions\Kpi\CalculateEmployeeKpi::class);
        $period = now()->format('Y-m');

        foreach ($employees as $index => $emp) {
            $eval = $action->execute($emp, $period);

            // Populate sample data if evaluation score is 0
            if ($eval && $eval->final_kpi_score == 0) {
                $sales = ($index + 1) * 3500000;
                $atv = 280000;
                $att = 96.0;
                $punc = 92.0;

                $salesScore = min(100, round(($sales / 10000000) * 100, 2));
                $atvScore = min(100, round(($atv / 250000) * 100, 2));
                $attScore = min(100, round(($att / 95.0) * 100, 2));
                $puncScore = min(100, round(($punc / 95.0) * 100, 2));

                $compositeScore = round(($salesScore * 0.4) + ($atvScore * 0.2) + ($attScore * 0.2) + ($puncScore * 0.2), 2);
                $earnedBonus = $compositeScore >= 90 ? 500000 : ($compositeScore >= 75 ? 375000 : 0);

                $eval->update([
                    'actual_sales_amount' => $sales,
                    'sales_score' => $salesScore,
                    'actual_atv_amount' => $atv,
                    'atv_score' => $atvScore,
                    'actual_attendance_pct' => $att,
                    'attendance_score' => $attScore,
                    'actual_punctuality_pct' => $punc,
                    'punctuality_score' => $puncScore,
                    'final_kpi_score' => $compositeScore,
                    'earned_bonus_amount' => $earnedBonus,
                    'status' => $index === 0 ? 'approved' : 'draft',
                ]);
            }
        }
    }
}
