<?php

namespace Tests\Feature;

use App\Livewire\PosKpiPerformance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\KpiTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosKpiPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_kpi_performance_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.kpi-performance'));

        $response->assertStatus(200);
        $response->assertSee('Performance KPI');
        $response->assertSee('1. Rekap Evaluasi Bulanan');
        $response->assertSee('2. Dashboard Realtime KPI Saya');
        $response->assertSee('3. Template KPI per Jabatan/User');
    }

    public function test_livewire_component_can_create_kpi_template(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PosKpiPerformance::class)
            ->set('templateName', 'Template Livewire Test')
            ->set('templatePosition', 'Sales Executive')
            ->set('maxBonusAmount', 500000)
            ->set('targetSalesAmount', 10000000)
            ->set('weightSales', 40.0)
            ->set('targetAtvAmount', 250000)
            ->set('weightAtv', 20.0)
            ->set('targetAttendancePct', 95.0)
            ->set('weightAttendance', 20.0)
            ->set('targetPunctualityPct', 95.0)
            ->set('weightPunctuality', 20.0)
            ->call('saveTemplate');

        $this->assertDatabaseHas('kpi_templates', [
            'name' => 'Template Livewire Test',
            'position' => 'Sales Executive',
            'max_bonus_amount' => 500000,
        ]);
    }

    public function test_livewire_can_switch_tabs_and_filter(): void
    {
        $user = User::factory()->create();
        $branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '0812345678',
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosKpiPerformance::class)
            ->set('activeTab', 'dashboard')
            ->assertSee('Dashboard Realtime KPI Staf')
            ->set('activeTab', 'templates')
            ->assertSee('Master Template KPI')
            ->set('activeTab', 'recap')
            ->set('filterBranchId', (string) $branch->id)
            ->set('filterMonth', '2026-09')
            ->assertSet('filterMonth', '2026-09')
            ->call('recalculateAllKpi')
            ->assertDispatched('toast');
    }

    public function test_template_modal_handles_currency_and_modal_state(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(PosKpiPerformance::class)
            ->call('openTemplateModal')
            ->assertSet('showTemplateModal', true)
            ->set('templateName', 'Template Currency Test')
            ->set('maxBonusAmount', '1.500.000')
            ->set('targetSalesAmount', '25.000.000')
            ->set('targetAtvAmount', '500.000')
            ->call('saveTemplate')
            ->assertSet('showTemplateModal', false);

        $this->assertDatabaseHas('kpi_templates', [
            'name' => 'Template Currency Test',
            'max_bonus_amount' => 1500000,
            'target_sales_amount' => 25000000,
            'target_atv_amount' => 500000,
        ]);
    }
}
