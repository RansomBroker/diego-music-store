<?php

namespace Tests\Feature\Actions;

use App\Actions\Payroll\ApprovePayroll;
use App\Actions\Payroll\CancelPayroll;
use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Actions\Payroll\ProcessPayrollPayment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateMonthlyPayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_monthly_payroll_action_computes_net_salary(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Payroll Test',
            'code' => 'CBG-PAY-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PAY-01',
            'name' => 'Staff Payroll A',
            'basic_salary' => 3500000,
            'is_active' => true,
        ]);

        $period = now()->format('Y-m');
        $action = app(GenerateMonthlyPayroll::class);
        $payroll = $action->execute($period, $branch->id, $user);

        $this->assertNotNull($payroll);
        $this->assertEquals(1, $payroll->total_employees);
        $this->assertEquals(3500000, $payroll->total_basic_salary);
        $this->assertEquals(3500000, $payroll->total_net_salary);
    }

    public function test_process_payroll_payment_action(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pay Test 2', 'code' => 'CBG-PAY-02', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PAY-02',
            'name' => 'Staff Payroll B',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);

        $processAction = app(ProcessPayrollPayment::class);
        $paidPayroll = $processAction->execute($payroll->id, $user);

        $this->assertEquals('paid', $paidPayroll->status);
        $this->assertNotNull($paidPayroll->paid_at);
    }

    public function test_generate_monthly_payroll_includes_approved_overtime(): void
    {
        $branch = Branch::create(['name' => 'Cabang OT Test', 'code' => 'CBG-OT-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-OT-01',
            'name' => 'Staff Lembur A',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $period = now()->format('Y-m');

        EmployeeOvertime::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'date' => now()->format('Y-m-d'),
            'start_time' => '17:00',
            'end_time' => '20:00',
            'hours' => 3.0,
            'hourly_rate' => 30000,
            'overtime_amount' => 90000,
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);
        $item = $payroll->items->first();

        $this->assertEquals(90000, $item->overtime_amount);
        $this->assertNotEmpty($item->overtime_details);
        $this->assertEquals(3090000, $item->net_salary);
    }

    public function test_approve_and_cancel_payroll_actions(): void
    {
        $branch = Branch::create(['name' => 'Cabang Lifecycle Test', 'code' => 'CBG-LC-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-LC-01',
            'name' => 'Staff Lifecycle',
            'basic_salary' => 2500000,
            'is_active' => true,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);
        $this->assertEquals('draft', $payroll->status);

        $approvedPayroll = app(ApprovePayroll::class)->execute($payroll->id, $user);
        $this->assertEquals('approved', $approvedPayroll->status);
        $this->assertNotNull($approvedPayroll->approved_at);

        $cancelledPayroll = app(CancelPayroll::class)->execute($payroll->id, $user);
        $this->assertEquals('cancelled', $cancelledPayroll->status);
    }

    public function test_cannot_cancel_paid_payroll(): void
    {
        $branch = Branch::create(['name' => 'Cabang Paid Cancel', 'code' => 'CBG-PC-01', 'is_active' => true]);
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PC-01',
            'name' => 'Staff PC',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);
        app(ProcessPayrollPayment::class)->execute($payroll->id, $user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payroll yang sudah berstatus Paid (dibayar) tidak dapat dibatalkan.');
        app(CancelPayroll::class)->execute($payroll->id, $user);
    }

    public function test_regenerating_cancelled_payroll_resets_to_draft(): void
    {
        $branch = Branch::create(['name' => 'Cabang Reset Batal', 'code' => 'CBG-RB-01', 'is_active' => true]);
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-RB-01',
            'name' => 'Staff RB',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $period = now()->format('Y-m');
        $payroll = app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);
        app(CancelPayroll::class)->execute($payroll->id, $user);
        $this->assertEquals('cancelled', $payroll->fresh()->status);

        // Regenerate should reset cancelled back to draft
        $regenerated = app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);
        $this->assertEquals('draft', $regenerated->status);
    }

    public function test_cannot_regenerate_paid_payroll(): void
    {
        $branch = Branch::create(['name' => 'Cabang Paid Regen', 'code' => 'CBG-PR-01', 'is_active' => true]);
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PR-01',
            'name' => 'Staff PR',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $period = now()->format('Y-m');
        $payroll = app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);
        app(ProcessPayrollPayment::class)->execute($payroll->id, $user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah berstatus PAID (dibayar) dan tidak dapat dihitung ulang.');
        app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);
    }
}
