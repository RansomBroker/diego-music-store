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

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->user->assignRole($ownerRole);

        (new UserPrivileges)->ensurePermissionsExist();
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

    /** @test */
    public function it_can_clone_role_via_table_action()
    {
        $role = Role::create(['name' => 'Supervisor Gudang', 'guard_name' => 'web']);
        $role->givePermissionTo(['inventory.products', 'inventory.transfers']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('cloneRole', $role, data: [
                'name' => 'Staf Gudang Junior',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('roles', ['name' => 'Staf Gudang Junior']);
        $clonedRole = Role::findByName('Staf Gudang Junior');
        $this->assertTrue($clonedRole->hasPermissionTo('inventory.products'));
        $this->assertTrue($clonedRole->hasPermissionTo('inventory.transfers'));
    }

    /** @test */
    public function it_can_save_new_erp_permissions()
    {
        $role = Role::create(['name' => 'Akuntan Perusahaan', 'guard_name' => 'web']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('editPermissions', $role, data: [
                'permissions' => [
                    'accounting.coa' => true,
                    'accounting.journals' => true,
                    'reports.accounting' => true,
                ],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($role->hasPermissionTo('accounting.coa'));
        $this->assertTrue($role->hasPermissionTo('accounting.journals'));
        $this->assertTrue($role->hasPermissionTo('reports.accounting'));
    }
}
