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
}
