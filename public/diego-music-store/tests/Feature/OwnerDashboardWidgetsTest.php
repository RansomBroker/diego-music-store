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
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(OwnerDashboardWidgets::class)
            ->assertDontSee('Analisis Performansi & Keuangan Bisnis');
    }

    public function test_owner_role_can_view_and_filter_owner_executive_dashboard()
    {
        $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($role);

        $this->actingAs($owner);

        Livewire::test(OwnerDashboardWidgets::class)
            ->assertSee('Analisis Performansi & Keuangan Bisnis')
            ->set('productCategory', 'Gitar')
            ->assertSet('productCategory', 'Gitar');
    }
}
