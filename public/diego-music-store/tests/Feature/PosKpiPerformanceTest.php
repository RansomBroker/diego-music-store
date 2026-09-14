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
}
