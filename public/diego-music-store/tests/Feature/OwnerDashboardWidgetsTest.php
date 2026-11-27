<?php

namespace Tests\Feature;

use App\Livewire\OwnerDashboardWidgets;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OwnerDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_owner_role_cannot_view_owner_executive_dashboard()
    {
        $perm = \Spatie\Permission\Models\Permission::findOrCreate('fo.dashboard.owner_widgets', 'web');
        $managerRole = Role::firstOrCreate(['name' => 'MANAGER TOKO', 'guard_name' => 'web']);
        $managerRole->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->assignRole($managerRole);
        $this->actingAs($user);

        Livewire::test(OwnerDashboardWidgets::class)
            ->assertDontSee('Analisis Performa & Keuangan Bisnis');
    }

    public function test_owner_role_can_view_and_filter_owner_executive_dashboard()
    {
        \App\Models\Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($role);

        $this->actingAs($owner);

        Livewire::test(OwnerDashboardWidgets::class)
            ->assertSee('Analisis Performa & Keuangan Bisnis')
            ->assertSee('Total Penjualan Harian')
            ->assertSee('Total Pengeluaran Harian')
            ->assertSee('Saldo Kas Laci Terkini')
            ->set('productCategory', 'Gitar')
            ->assertSet('productCategory', 'Gitar');
    }
}
