<?php

namespace Tests\Feature;

use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Livewire\PosPayrollManagement;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayrollPayslipTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_payslip_pdf_view_renders_overtime_and_components(): void
    {
        $branch = Branch::create(['name' => 'Cabang Slip Test', 'code' => 'CBG-SLIP-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-SLIP-01',
            'name' => 'Karyawan Slip A',
            'basic_salary' => 3500000,
            'is_active' => true,
        ]);

        EmployeeOvertime::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'date' => '2026-08-20',
            'start_time' => '17:00',
            'end_time' => '20:00',
            'hours' => 3.0,
            'hourly_rate' => 30000,
            'overtime_amount' => 90000,
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute('2026-08', $branch->id, $user);
        $item = $payroll->items->first();

        $response = $this->actingAs($user)->get(route('pos.payroll.payslip-pdf', $item->id));

        $response->assertStatus(200);
        $response->assertSee('SLIP GAJI KARYAWAN');
        $response->assertSee('Karyawan Slip A');
        $response->assertSee('Rincian Jam Kerja Lembur');
        $response->assertSee('17:00');
        $response->assertSee('20:00');
        $response->assertSee('90.000');
    }

    public function test_bulk_payslip_pdf_view_renders_all_employees(): void
    {
        $branch = Branch::create(['name' => 'Cabang Bulk Test', 'code' => 'CBG-BULK-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee1 = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-BULK-01',
            'name' => 'Karyawan Bulk 1',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);
        $employee2 = Employee::create([
            'branch_id' => $branch->id,
            'nik' => 'EMP-BULK-02',
            'name' => 'Karyawan Bulk 2',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute('2026-08', $branch->id, $user);

        $response = $this->actingAs($user)->get(route('pos.payroll.bulk-payslip-pdf', $payroll->id));

        $response->assertStatus(200);
        $response->assertSee('SLIP GAJI MASSAL');
        $response->assertSee('Karyawan Bulk 1');
        $response->assertSee('Karyawan Bulk 2');
    }

    public function test_payroll_resource_navigation_registered(): void
    {
        $this->assertTrue(\App\Filament\Resources\Payrolls\PayrollResource::shouldRegisterNavigation());
        $this->assertTrue(\App\Filament\Resources\Payrolls\PayrollResource::canViewAny());
        $this->assertEquals('Manajemen Karyawan', \App\Filament\Resources\Payrolls\PayrollResource::getNavigationGroup());
    }

    public function test_payroll_resource_per_employee_table_display(): void
    {
        $branch = Branch::create(['name' => 'Cabang Per Orang Test', 'code' => 'CBG-PO-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PO-01',
            'name' => 'Staff Per Orang',
            'basic_salary' => 4500000,
            'is_active' => true,
        ]);

        $payroll = app(GenerateMonthlyPayroll::class)->execute(now()->format('Y-m'), $branch->id, $user);
        $item = $payroll->items->first();

        $this->assertEquals('EMP-PO-01', $item->employee->nik);
        $this->assertEquals(4500000, $item->basic_salary);
        $this->assertEquals(4500000, $item->net_salary);
    }

    public function test_pos_payroll_livewire_component_lifecycle(): void
    {
        $branch = Branch::create(['name' => 'Cabang LW Test', 'code' => 'CBG-LW-01', 'is_active' => true]);
        $user = User::factory()->create();
        Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-LW-01',
            'name' => 'Karyawan Livewire',
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        $period = now()->format('Y-m');

        Livewire::actingAs($user)
            ->test(PosPayrollManagement::class)
            ->set('filterMonth', $period)
            ->set('filterBranchId', (string) $branch->id)
            ->call('generatePayroll')
            ->assertSee('Karyawan Livewire');

        $payroll = \App\Models\Payroll::where('period', $period)->first();
        $this->assertNotNull($payroll);
        $this->assertEquals('draft', $payroll->status);

        // Test Approve via Livewire
        Livewire::actingAs($user)
            ->test(PosPayrollManagement::class)
            ->call('approvePayroll', $payroll->id);

        $this->assertEquals('approved', $payroll->fresh()->status);

        // Test Cancel via Livewire
        Livewire::actingAs($user)
            ->test(PosPayrollManagement::class)
            ->call('cancelPayroll', $payroll->id);

        $this->assertEquals('cancelled', $payroll->fresh()->status);
    }

    public function test_payroll_resource_edit_form_schema_matches_pos_payroll_modal(): void
    {
        $components = \App\Filament\Resources\Payrolls\Schemas\PayrollItemForm::getComponents();

        $this->assertCount(4, $components);

        $section1 = $components[0];
        $this->assertEquals('Pendapatan & Tunjangan (Penambahan)', $section1->getHeading());

        $section2 = $components[1];
        $this->assertEquals('Potongan & Denda (Pengurangan)', $section2->getHeading());

        $section3 = $components[2];
        $this->assertEquals('Catatan Penyesuaian', $section3->getHeading());

        $section4 = $components[3];
        $this->assertEquals('Ringkasan Gaji Bersih (Take Home Pay)', $section4->getHeading());
    }
}
