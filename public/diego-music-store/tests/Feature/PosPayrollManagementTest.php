<?php

namespace Tests\Feature;

use App\Livewire\PosPayrollManagement;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosPayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_payroll_management_page_renders_successfully(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test POS',
            'code' => 'CBG-POS-01',
            'is_active' => true,
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.payroll'));

        $response->assertStatus(200);
        $response->assertSee('Payroll & Gaji Karyawan');
        $response->assertSee('Proses Payroll Bulanan');
    }

    public function test_livewire_component_can_generate_payroll(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test POS 2',
            'code' => 'CBG-POS-02',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-TEST-01',
            'name' => 'Karyawan Test POS',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosPayrollManagement::class)
            ->set('filterBranchId', (string) $branch->id)
            ->call('generatePayroll');

        $this->assertDatabaseHas('payrolls', [
            'period' => now()->format('Y-m'),
        ]);
    }

    public function test_can_override_payroll_item_components(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test Override',
            'code' => 'CBG-POS-OVR',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-TEST-OVR',
            'name' => 'Karyawan Override',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $payroll = \App\Models\Payroll::create([
            'branch_id' => $branch->id,
            'payroll_code' => 'PAY-TEST-001',
            'period' => now()->format('Y-m'),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'draft',
            'total_basic_salary' => 3000000,
            'total_net_salary' => 3000000,
        ]);

        $item = \App\Models\PayrollItem::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'basic_salary' => 3000000,
            'allowance_amount' => 200000,
            'overtime_amount' => 100000,
            'commission_amount' => 50000,
            'kpi_bonus_amount' => 150000,
            'violation_deduction_amount' => 50000,
            'other_deduction_amount' => 0,
            'net_salary' => 3450000,
        ]);

        Livewire::actingAs($user)
            ->test(PosPayrollManagement::class)
            ->call('openEditItemModal', $item->id)
            ->assertSet('editBasicSalary', 3000000.0)
            ->set('editBasicSalary', 4000000)
            ->set('editOvertimeAmount', 250000)
            ->set('editCommissionAmount', 100000)
            ->set('editKpiBonusAmount', 200000)
            ->set('editViolationDeductionAmount', 100000)
            ->set('editOtherDeductionAmount', 50000)
            ->call('saveItemDetails');

        $item->refresh();
        // Earnings: 4,000,000 + 200,000 + 250,000 + 100,000 + 200,000 = 4,750,000
        // Deductions: 100,000 + 50,000 = 150,000
        // Net Salary: 4,600,000
        $this->assertEquals(4000000, $item->basic_salary);
        $this->assertEquals(250000, $item->overtime_amount);
        $this->assertEquals(4600000, $item->net_salary);

        $payroll->refresh();
        $this->assertEquals(4000000, $payroll->total_basic_salary);
        $this->assertEquals(4600000, $payroll->total_net_salary);
    }
}

