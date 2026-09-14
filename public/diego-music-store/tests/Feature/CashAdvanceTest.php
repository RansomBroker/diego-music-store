<?php

namespace Tests\Feature;

use App\Actions\CashAdvance\ApproveCashAdvance;
use App\Actions\CashAdvance\CreateCashAdvanceRequest;
use App\Actions\CashAdvance\RejectCashAdvance;
use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Actions\Payroll\ProcessPayrollPayment;
use App\Filament\Resources\EmployeeCashAdvances\EmployeeCashAdvanceResource;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAdvanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_cash_advance_request_validates_50_percent_limit(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Test', 'code' => 'CBG-KSB-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-01',
            'name' => 'Karyawan Kasbon A',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $action = app(CreateCashAdvanceRequest::class);

        // Attempt requesting 3,000,000 (exceeds 50% limit of 4,000,000 = 2,000,000)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('melebihi batas limit 50% gaji pokok');
        $action->execute($employee->id, 3000000, 3, 'Keperluan Darurat', $user);
    }

    public function test_valid_cash_advance_request_creation(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Test 2', 'code' => 'CBG-KSB-02', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-02',
            'name' => 'Karyawan Kasbon B',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $action = app(CreateCashAdvanceRequest::class);
        $advance = $action->execute($employee->id, 1200000, 3, 'Renovasi', $user);

        $this->assertNotNull($advance);
        $this->assertEquals('pending', $advance->status);
        $this->assertEquals(1200000, $advance->amount);
        $this->assertEquals(400000, $advance->monthly_installment);
        $this->assertEquals(1200000, $advance->remaining_amount);
    }

    public function test_approve_and_reject_cash_advance_actions(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Test 3', 'code' => 'CBG-KSB-03', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-03',
            'name' => 'Karyawan Kasbon C',
            'basic_salary' => 5000000,
            'is_active' => true,
        ]);

        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1500000, 3, 'Laptop', $user);

        // Approve
        $approved = app(ApproveCashAdvance::class)->execute($advance->id, $user, 'Disetujui Manager');
        $this->assertEquals('approved', $approved->status);
        $this->assertNotNull($approved->approved_at);

        // Reject another
        $advance2 = app(CreateCashAdvanceRequest::class)->execute($employee->id, 500000, 1, 'Kebutuhan Lain', $user);
        $rejected = app(RejectCashAdvance::class)->execute($advance2->id, $user, 'Alasan Belum Jelas');
        $this->assertEquals('rejected', $rejected->status);
    }

    public function test_generate_payroll_auto_deducts_active_cash_advance_installment(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Payroll', 'code' => 'CBG-KSB-04', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-04',
            'name' => 'Karyawan Kasbon Payroll',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        // Create approved cash advance 1,200,000 3 months (400,000/month)
        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1200000, 3, 'Biaya Medis', $user);
        app(ApproveCashAdvance::class)->execute($advance->id, $user);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);
        $item = $payroll->items->first();

        // 3000000 - 400000 = 2600000
        $this->assertEquals(400000, $item->other_deduction_amount);
        $this->assertEquals(2600000, $item->net_salary);
        $this->assertNotEmpty($item->deduction_details);
    }

    public function test_processing_payroll_payment_reduces_cash_advance_remaining_balance(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Pay', 'code' => 'CBG-KSB-05', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-05',
            'name' => 'Karyawan Kasbon Pay',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        // Create approved cash advance 500,000 for 1 month
        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 500000, 1, 'Kasbon Singkat', $user);
        app(ApproveCashAdvance::class)->execute($advance->id, $user);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);
        app(ProcessPayrollPayment::class)->execute($payroll->id, $user);

        $advanceFresh = $advance->fresh();
        $this->assertEquals(500000, $advanceFresh->paid_amount);
        $this->assertEquals(0, $advanceFresh->remaining_amount);
        $this->assertEquals('paid_off', $advanceFresh->status);
    }

    public function test_cancel_cash_advance_request(): void
    {
        $branch = Branch::create(['name' => 'Cabang Cancel Kasbon', 'code' => 'CBG-KSB-06', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-06',
            'name' => 'Karyawan Cancel Kasbon',
            'basic_salary' => 3500000,
            'is_active' => true,
        ]);

        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 800000, 2, 'Pengajuan Salah', $user);
        $cancelled = app(\App\Actions\CashAdvance\CancelCashAdvanceRequest::class)->execute($advance->id, $user);

        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_settle_cash_advance_early_action(): void
    {
        $branch = Branch::create(['name' => 'Cabang Early Settlement', 'code' => 'CBG-KSB-07', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KSB-07',
            'name' => 'Karyawan Pelunasan Awal',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1000000, 2, 'Renovasi', $user);
        app(ApproveCashAdvance::class)->execute($advance->id, $user);

        // Early manual repayment outside payroll
        $settled = app(\App\Actions\CashAdvance\SettleCashAdvanceEarly::class)->execute($advance->id, 1000000, 'cash', 'Pelunasan Tunai Toko', $user);

        $this->assertEquals('paid_off', $settled->status);
        $this->assertEquals(0, $settled->remaining_amount);
        $this->assertEquals(1000000, $settled->paid_amount);
    }

    public function test_employee_cash_advance_filament_resource_navigation_registered(): void
    {
        $this->assertTrue(EmployeeCashAdvanceResource::shouldRegisterNavigation());
        $this->assertTrue(EmployeeCashAdvanceResource::canViewAny());
        $this->assertEquals('Manajemen Karyawan', EmployeeCashAdvanceResource::getNavigationGroup());
    }
}
