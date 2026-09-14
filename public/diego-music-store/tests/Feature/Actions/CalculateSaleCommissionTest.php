<?php

namespace Tests\Feature\Actions;

use App\Actions\Commission\ApproveCommissionRecap;
use App\Actions\Commission\CalculateSaleCommission;
use App\Models\Branch;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateSaleCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_sale_commission_percentage_scheme(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Utama Test',
            'code' => 'CBG-COMM-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-COMM-01',
            'name' => 'Sales Test A',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $scheme = CommissionScheme::create([
            'branch_id' => $branch->id,
            'name' => 'Komisi Sales 5%',
            'calculation_type' => 'percentage',
            'rate' => 5.0,
            'applies_to' => 'all_sales',
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'sales_rep_id' => $user->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-COMM-TEST-001',
            'invoice_date' => now()->format('Y-m-d'),
            'grand_total' => 1000000,
            'payment_status' => 'paid',
        ]);

        $action = app(CalculateSaleCommission::class);
        $log = $action->execute($sale, $employee, $scheme);

        $this->assertNotNull($log);
        $this->assertEquals(50000, $log->commission_amount);
        $this->assertEquals('pending', $log->status);

        $this->assertDatabaseHas('sales_commission_logs', [
            'employee_id' => $employee->id,
            'sale_id' => $sale->id,
            'commission_amount' => 50000,
            'status' => 'pending',
        ]);
    }

    public function test_approve_commission_recap_action(): void
    {
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'nik' => 'EMP-COMM-02',
            'name' => 'Sales Test B',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        SalesCommissionLog::create([
            'employee_id' => $employee->id,
            'date' => now()->format('Y-m-d'),
            'sale_amount' => 500000,
            'commission_amount' => 25000,
            'status' => 'pending',
        ]);

        $approveAction = app(ApproveCommissionRecap::class);
        $count = $approveAction->execute($employee->id, now()->format('Y-m'), $user);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('sales_commission_logs', [
            'employee_id' => $employee->id,
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
    }

    public function test_calculate_sale_commission_prioritizes_employee_specific_scheme(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Spec Test',
            'code' => 'CBG-SPEC-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-COMM-SPEC',
            'name' => 'Sales Spec C',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        // General scheme (2%)
        CommissionScheme::create([
            'branch_id' => $branch->id,
            'name' => 'Komisi General 2%',
            'calculation_type' => 'percentage',
            'rate' => 2.0,
            'is_active' => true,
        ]);

        // Employee-specific scheme (10%)
        $employeeScheme = CommissionScheme::create([
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'name' => 'Komisi Khusus Senior 10%',
            'calculation_type' => 'percentage',
            'rate' => 10.0,
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'sales_rep_id' => $user->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-SPEC-001',
            'invoice_date' => now()->format('Y-m-d'),
            'grand_total' => 1000000,
            'payment_status' => 'paid',
        ]);

        $action = app(CalculateSaleCommission::class);
        $log = $action->execute($sale, $employee);

        $this->assertNotNull($log);
        $this->assertEquals($employeeScheme->id, $log->commission_scheme_id);
        $this->assertEquals(100000, $log->commission_amount);
    }

    public function test_calculate_sale_commission_supports_multiple_target_employees(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Multi Test',
            'code' => 'CBG-MULTI-01',
            'is_active' => true,
        ]);

        $user1 = User::factory()->create();
        $emp1 = Employee::create([
            'user_id' => $user1->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-MULTI-01',
            'name' => 'Sales Multi A',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $user2 = User::factory()->create();
        $emp2 = Employee::create([
            'user_id' => $user2->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-MULTI-02',
            'name' => 'Sales Multi B',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $multiScheme = CommissionScheme::create([
            'branch_id' => $branch->id,
            'name' => 'Komisi Tim Spesial 8%',
            'calculation_type' => 'percentage',
            'rate' => 8.0,
            'is_active' => true,
        ]);

        $multiScheme->employees()->attach([$emp1->id, $emp2->id]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'user_id' => $user1->id,
            'sales_rep_id' => $user1->id,
            'created_by' => $user1->id,
            'invoice_number' => 'INV-MULTI-001',
            'invoice_date' => now()->format('Y-m-d'),
            'grand_total' => 2000000,
            'payment_status' => 'paid',
        ]);

        $action = app(CalculateSaleCommission::class);
        $log = $action->execute($sale, $emp1);

        $this->assertNotNull($log);
        $this->assertEquals($multiScheme->id, $log->commission_scheme_id);
        $this->assertEquals(160000, $log->commission_amount);
    }
}
