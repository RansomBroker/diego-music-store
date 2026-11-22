<?php

namespace Tests\Feature\Filament;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeSidebarDefaultCollapsedTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_panel_renders_default_collapsed_sidebar_script(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'store_name' => 'Diego Music Store',
            'is_active' => true,
        ]);

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($ownerRole);
        $user->branches()->attach($branch->id);

        $response = $this->actingAs($user)->get('/backoffice');

        $response->assertStatus(200);
        $response->assertSee("localStorage.setItem('isOpenDesktop', 'false')", false);
        $response->assertSee("localStorage.setItem('isOpen', 'false')", false);
        $response->assertSee('bo_sidebar_default_collapsed_v2', false);
        $response->assertSee('fi-body-has-sidebar-collapsible-on-desktop');
    }
}

