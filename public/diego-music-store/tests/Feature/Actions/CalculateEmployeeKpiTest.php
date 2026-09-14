<?php

namespace Tests\Feature\Actions;

use App\Actions\Kpi\ApproveKpiEvaluation;
use App\Actions\Kpi\CalculateEmployeeKpi;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\KpiEvaluation;
use App\Models\KpiTemplate;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateEmployeeKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_employee_kpi_action_computes_composite_score_and_bonus(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang KPI Test',
            'code' => 'CBG-KPI-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KPI-01',
            'name' => 'Sales KPI Test',
            'position' => 'Sales Executive',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $template = KpiTemplate::create([
            'name' => 'KPI Sales Executive Test',
            'position' => 'Sales Executive',
            'max_bonus_amount' => 500000,
            'target_sales_amount' => 10000000,
            'weight_sales' => 40.0,
            'target_atv_amount' => 200000,
            'weight_atv' => 20.0,
            'target_attendance_pct' => 90.0,
            'weight_attendance' => 20.0,
            'target_punctuality_pct' => 90.0,
            'weight_punctuality' => 20.0,
            'bonus_tiering_rules' => [
                ['min_score' => 90, 'bonus_pct' => 100],
                ['min_score' => 75, 'bonus_pct' => 75],
                ['min_score' => 0, 'bonus_pct' => 0],
            ],
            'is_active' => true,
        ]);

        // Create sample sales for current month
        Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'sales_rep_id' => $user->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-KPI-001',
            'invoice_date' => now()->format('Y-m-d'),
            'grand_total' => 10000000, // 100% target sales
            'payment_status' => 'paid',
        ]);

        $action = app(CalculateEmployeeKpi::class);
        $eval = $action->execute($employee, now()->format('Y-m'), $template);

        $this->assertNotNull($eval);
        $this->assertEquals(10000000, $eval->actual_sales_amount);
        $this->assertEquals(100, $eval->sales_score);
        $this->assertGreaterThan(0, $eval->final_kpi_score);
        $this->assertGreaterThan(0, $eval->earned_bonus_amount);
    }

    public function test_approve_kpi_evaluation_action(): void
    {
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'nik' => 'EMP-KPI-02',
            'name' => 'Sales KPI B',
            'is_active' => true,
        ]);

        $eval = KpiEvaluation::create([
            'employee_id' => $employee->id,
            'period' => now()->format('Y-m'),
            'final_kpi_score' => 95.0,
            'earned_bonus_amount' => 500000,
            'status' => 'draft',
        ]);

        $approveAction = app(ApproveKpiEvaluation::class);
        $updatedEval = $approveAction->execute($eval->id, $user);

        $this->assertEquals('approved', $updatedEval->status);
        $this->assertEquals($user->id, $updatedEval->approved_by);
        $this->assertNotNull($updatedEval->approved_at);
    }
}
