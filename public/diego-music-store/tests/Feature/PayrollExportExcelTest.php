<?php

namespace Tests\Feature;

use App\Actions\Payroll\ExportPayrollItemToExcel;
use App\Actions\Payroll\ExportPayrollToExcel;
use App\Exports\PayrollExport;
use App\Exports\PayrollItemExport;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class PayrollExportExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_payroll_to_excel_action_returns_valid_xlsx_response(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'code' => 'CBG-EXP-01', 'is_active' => true]);
        $user = User::factory()->create();

        $employee = Employee::create([
            'user_id'             => $user->id,
            'branch_id'           => $branch->id,
            'nik'                 => 'EMP-EXP-01',
            'name'                => 'Budi Programmer',
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Budi Programmer',
            'basic_salary'        => 5000000,
            'is_active'           => true,
        ]);

        $payroll = Payroll::create([
            'payroll_code'       => 'PAY-2026-09-001',
            'period'             => '2026-09',
            'branch_id'          => $branch->id,
            'total_employees'    => 1,
            'total_basic_salary' => 5000000,
            'total_allowances'   => 500000,
            'total_commissions'  => 200000,
            'total_kpi_bonuses'  => 300000,
            'total_deductions'   => 100000,
            'total_net_salary'   => 5900000,
            'status'             => 'draft',
            'created_by'         => $user->id,
        ]);

        PayrollItem::create([
            'payroll_id'                 => $payroll->id,
            'employee_id'                => $employee->id,
            'branch_id'                  => $branch->id,
            'basic_salary'               => 5000000,
            'allowance_amount'           => 500000,
            'overtime_amount'            => 0,
            'commission_amount'          => 200000,
            'kpi_bonus_amount'           => 300000,
            'violation_deduction_amount' => 50000,
            'other_deduction_amount'     => 50000,
            'net_salary'                 => 5900000,
            'bank_name'                  => 'BCA',
            'bank_account_number'        => '1234567890',
            'bank_account_holder'        => 'Budi Programmer',
            'notes'                      => 'Bonus proyek lancar',
        ]);

        // Uji Export Class langsung
        $export = new PayrollExport($payroll);
        $arrayData = $export->array();

        $this->assertNotEmpty($arrayData);
        $this->assertEquals('DIEGO MUSIC STORE - LAPORAN PAYROLL & GAJI KARYAWAN', $arrayData[0][0]);
        $this->assertEquals('Payroll 2026-09', $export->title());
        $this->assertArrayHasKey('G', $export->columnFormats());

        // Uji Action execute()
        $action = new ExportPayrollToExcel();
        $response = $action->execute($payroll);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Payroll_Gaji_2026-09_PAY-2026-09-001.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_export_single_payroll_item_to_excel_action_returns_valid_xlsx_response(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test 3', 'code' => 'CBG-EXP-03', 'is_active' => true]);
        $user = User::factory()->create();

        $employee = Employee::create([
            'user_id'             => $user->id,
            'branch_id'           => $branch->id,
            'nik'                 => 'EMP-EXP-03',
            'name'                => 'Siti Developer',
            'bank_name'           => 'Mandiri',
            'bank_account_number' => '9876543210',
            'bank_account_holder' => 'Siti Developer',
            'basic_salary'        => 6000000,
            'is_active'           => true,
        ]);

        $payroll = Payroll::create([
            'payroll_code'       => 'PAY-2026-09-003',
            'period'             => '2026-09',
            'branch_id'          => $branch->id,
            'total_employees'    => 1,
            'total_basic_salary' => 6000000,
            'total_allowances'   => 0,
            'total_commissions'  => 0,
            'total_kpi_bonuses'  => 0,
            'total_deductions'   => 0,
            'total_net_salary'   => 6000000,
            'status'             => 'draft',
            'created_by'         => $user->id,
        ]);

        $item = PayrollItem::create([
            'payroll_id'                 => $payroll->id,
            'employee_id'                => $employee->id,
            'branch_id'                  => $branch->id,
            'basic_salary'               => 6000000,
            'allowance_amount'           => 1000000,
            'overtime_amount'            => 0,
            'commission_amount'          => 0,
            'kpi_bonus_amount'           => 0,
            'violation_deduction_amount' => 0,
            'other_deduction_amount'     => 0,
            'net_salary'                 => 7000000,
            'bank_name'                  => 'Mandiri',
            'bank_account_number'        => '9876543210',
            'bank_account_holder'        => 'Siti Developer',
            'notes'                      => 'Slip khusus',
        ]);

        // Uji Single Item Export Class
        $export = new PayrollItemExport($item);
        $arrayData = $export->array();

        $this->assertNotEmpty($arrayData);
        $this->assertEquals('DIEGO MUSIC STORE - SLIP GAJI KARYAWAN', $arrayData[0][0]);
        $this->assertStringContainsString('Siti Developer', $export->title());
        $this->assertArrayHasKey('D', $export->columnFormats());

        // Uji Action Single Item
        $action = new ExportPayrollItemToExcel();
        $response = $action->execute($item);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Slip_Gaji_siti_developer_2026-09.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_export_payroll_excel_route_works_for_authenticated_user(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test 2', 'code' => 'CBG-EXP-02', 'is_active' => true]);
        $user = User::factory()->create();

        $payroll = Payroll::create([
            'payroll_code'       => 'PAY-2026-09-002',
            'period'             => '2026-09',
            'branch_id'          => $branch->id,
            'total_employees'    => 0,
            'total_basic_salary' => 0,
            'total_allowances'   => 0,
            'total_commissions'  => 0,
            'total_kpi_bonuses'  => 0,
            'total_deductions'   => 0,
            'total_net_salary'   => 0,
            'status'             => 'draft',
            'created_by'         => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('pos.payroll.export-excel', $payroll->id));

        $response->assertStatus(200);
        $response->assertDownload('Payroll_Gaji_2026-09_PAY-2026-09-002.xlsx');
    }

    public function test_export_payroll_item_excel_route_works_for_authenticated_user(): void
    {
        $branch = Branch::create(['name' => 'Cabang Test 4', 'code' => 'CBG-EXP-04', 'is_active' => true]);
        $user = User::factory()->create();

        $employee = Employee::create([
            'user_id'             => $user->id,
            'branch_id'           => $branch->id,
            'nik'                 => 'EMP-EXP-04',
            'name'                => 'Ahmad Staf',
            'basic_salary'        => 4000000,
            'is_active'           => true,
        ]);

        $payroll = Payroll::create([
            'payroll_code'       => 'PAY-2026-09-004',
            'period'             => '2026-09',
            'branch_id'          => $branch->id,
            'total_employees'    => 1,
            'status'             => 'draft',
            'created_by'         => $user->id,
        ]);

        $item = PayrollItem::create([
            'payroll_id'                 => $payroll->id,
            'employee_id'                => $employee->id,
            'branch_id'                  => $branch->id,
            'basic_salary'               => 4000000,
            'net_salary'                 => 4000000,
        ]);

        $response = $this->actingAs($user)->get(route('pos.payroll.item.export-excel', $item->id));

        $response->assertStatus(200);
        $response->assertDownload('Slip_Gaji_ahmad_staf_2026-09.xlsx');
    }

    public function test_export_payroll_excel_route_requires_authentication(): void
    {
        $response = $this->get('/pos/payroll/export-excel/1');

        $response->assertRedirect(route('pos.login'));
    }
}
