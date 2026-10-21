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
            ->assertSee('Produk Berinsentif Komisi')
            ->assertSee('Produk Fokus Bulan Ini (Back Office)')
            ->assertSee('Grafik Performa Penjualan Sales (1 Tahun)')
            ->assertSee('Absensi Staf');
    }

    public function test_it_displays_active_backoffice_focus_products()
    {
        $branch = \App\Models\Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $user = User::factory()->create();
        \App\Models\Employee::create([
            'user_id'                => $user->id,
            'branch_id'              => $branch->id,
            'name'                   => $user->name,
            'nik'                    => 'EMP-0099',
            'monthly_off_days_quota' => 4,
            'is_active'              => true,
        ]);
        $this->actingAs($user);

        $product = \App\Models\Product::create([
            'name'      => 'Yamaha Acoustic Guitar FG800',
            'category'  => 'Gitar',
            'type'      => 'physical',
            'is_active' => true,
        ]);
        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'YAM-FG800',
            'price'      => 3200000,
            'is_active'  => true,
        ]);
        \App\Models\ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
            'stock' => 7,
            'hpp' => 2400000,
        ]);

        \App\Models\FocusProduct::create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Produk Prioritas Akhir Tahun',
        ]);

        Livewire::test(SalesEmployeeDashboardWidgets::class)
            ->assertSee('Yamaha Acoustic Guitar FG800')
            ->assertSee('YAM-FG800')
            ->assertSee('3.200.000')
            ->assertSee('7 Unit')
            ->assertSee('Prioritas Toko');
    }
}
