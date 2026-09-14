<?php

namespace Tests\Feature;

use App\Livewire\SalesEmployeeDashboardWidgets;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesEmployeeDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_employee_dashboard_widgets_renders_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(SalesEmployeeDashboardWidgets::class)
            ->assertSee('Performa Sales')
            ->assertSee('Indikator Target')
            ->assertSee('Target Penjualan Bulanan')
            ->assertSee('Target Penjualan Harian')
            ->assertSee('Komisi Penjualan Bulan Ini')
            ->assertSee('Unlock Tier Komisi Next')
            ->assertSee('Leaderboard Top 3 Sales')
            ->assertSee('Produk Fokus Bulan Ini')
            ->assertSee('Grafik Performa Penjualan Sales (1 Tahun)')
            ->assertSee('Absensi Staf');
    }
}
