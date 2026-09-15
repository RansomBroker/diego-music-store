<?php

namespace Tests\Feature;

use App\Livewire\PosCommissions;
use App\Models\Branch;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosCommissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_commissions_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pos.commissions'))
            ->assertStatus(200);
    }

    public function test_user_can_create_commission_scheme(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->set('schemeName', 'Komisi Sales 3%')
            ->set('calculationType', 'percentage')
            ->set('rate', 3.0)
            ->set('appliesTo', 'all_sales')
            ->call('saveScheme')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('commission_schemes', [
            'name' => 'Komisi Sales 3%',
            'calculation_type' => 'percentage',
            'rate' => 3.0,
            'is_active' => true,
        ]);
    }

    public function test_user_can_toggle_scheme_status(): void
    {
        $user = User::factory()->create();
        $scheme = CommissionScheme::create([
            'name' => 'Skema Toggle Test',
            'calculation_type' => 'percentage',
            'rate' => 2.5,
            'applies_to' => 'all_sales',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->call('toggleSchemeStatus', $scheme->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('commission_schemes', [
            'id' => $scheme->id,
            'is_active' => false,
        ]);
    }

    public function test_user_can_delete_scheme(): void
    {
        $user = User::factory()->create();
        $scheme = CommissionScheme::create([
            'name' => 'Skema Delete Test',
            'calculation_type' => 'fixed_amount',
            'rate' => 50000,
            'applies_to' => 'all_sales',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->call('deleteScheme', $scheme->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('commission_schemes', [
            'id' => $scheme->id,
        ]);
    }

    public function test_user_can_approve_employee_recap(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Komisi',
            'code' => 'CBG-COMM-01',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = $user->employee;
        $employee->update(['branch_id' => $branch->id]);

        $log = SalesCommissionLog::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'sale_amount' => 1000000,
            'commission_amount' => 50000,
            'status' => 'pending',
            'notes' => 'Komisi Test',
        ]);

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->set('filterMonth', now()->format('Y-m'))
            ->call('approveEmployeeRecap', $employee->id)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('sales_commission_logs', [
            'id' => $log->id,
            'status' => 'approved',
        ]);
    }

    public function test_user_can_switch_tabs(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->assertSee('Rekap Komisi Sales')
            ->assertSee('Skema & Aturan Komisi', false)
            ->assertSee('Log Transaksi Komisi')
            ->set('activeTab', 'schemes')
            ->assertSee('Daftar Skema & Aturan Komisi Aktif', false)
            ->set('activeTab', 'logs')
            ->assertSee('Log Transaksi Komisi Per Penjualan');
    }
}
