<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\UserPrivileges;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPrivilegesPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_render_user_privileges_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->assertStatus(200)
            ->assertSee('Setting Hak Akses User');
    }

    /** @test */
    public function it_can_mount_and_call_table_edit_permissions_action()
    {
        $role = Role::create(['name' => 'Staff Toko', 'guard_name' => 'web']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('editPermissions', $role, data: [
                'permissions' => [
                    'pos.access' => true,
                    'master.customers' => true,
                ],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($role->hasPermissionTo('pos.access'));
        $this->assertTrue($role->hasPermissionTo('master.customers'));
    }

    /** @test */
    public function it_can_create_role_via_header_action()
    {
        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callAction('createRole', data: [
                'name' => 'Manager Operasional',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager Operasional',
        ]);
    }
}
