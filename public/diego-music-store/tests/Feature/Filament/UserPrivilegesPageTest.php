<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\UserPrivileges;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
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

        $this->seed(RoleAndPermissionSeeder::class);

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->user->assignRole('owner');
    }

    public function test_it_can_render_user_privileges_filament_page(): void
    {
        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->assertStatus(200)
            ->assertSee('Setting Hak Akses User');
    }

    public function test_it_can_create_role_via_header_action_with_panel_toggles(): void
    {
        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callAction('createRole', data: [
                'name' => 'Manager Operasional',
                'access_fo' => true,
                'access_bo' => true,
            ])
            ->assertHasNoActionErrors();

        $role = Role::findByName('Manager Operasional');
        $this->assertTrue($role->hasPermissionTo('fo.access'));
        $this->assertTrue($role->hasPermissionTo('bo.access'));
    }

    public function test_it_can_edit_role_permissions_using_two_section_schema(): void
    {
        $role = Role::create(['name' => 'Staff Toko', 'guard_name' => 'web']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('editPermissions', $role, data: [
                'role_name' => 'Staff Toko',
                'access_fo' => true,
                'access_bo' => false,
                'perm_groups' => [
                    'frontoffice__fo_main__fo_pos' => ['fo.pos.view', 'fo.pos.checkout'],
                    'frontoffice__fo_master__fo_customers' => ['fo.customers.view', 'fo.customers.create'],
                ],
            ])
            ->assertHasNoTableActionErrors();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('fo.access'));
        $this->assertFalse($role->hasPermissionTo('bo.access'));
        $this->assertTrue($role->hasPermissionTo('fo.pos.view'));
        $this->assertTrue($role->hasPermissionTo('fo.pos.checkout'));
        $this->assertTrue($role->hasPermissionTo('fo.customers.create'));
        $this->assertFalse($role->hasPermissionTo('fo.pos.discount'));
    }

    public function test_it_can_save_backoffice_accounting_permissions(): void
    {
        $role = Role::create(['name' => 'Akuntan Perusahaan', 'guard_name' => 'web']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('editPermissions', $role, data: [
                'role_name' => 'Akuntan Perusahaan',
                'access_fo' => false,
                'access_bo' => true,
                'perm_groups' => [
                    'backoffice__bo_accounting__bo_accounts' => ['bo.accounts.view', 'bo.accounts.create'],
                    'backoffice__bo_accounting__bo_journal_entries' => ['bo.journal_entries.view'],
                    'backoffice__bo_financial_reports__bo_reports_balance_sheet' => ['bo.reports.balance_sheet'],
                ],
            ])
            ->assertHasNoTableActionErrors();

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('fo.access'));
        $this->assertTrue($role->hasPermissionTo('bo.access'));
        $this->assertTrue($role->hasPermissionTo('bo.accounts.view'));
        $this->assertTrue($role->hasPermissionTo('bo.journal_entries.view'));
        $this->assertTrue($role->hasPermissionTo('bo.reports.balance_sheet'));
    }

    public function test_it_can_clone_and_delete_role_via_table_actions(): void
    {
        $role = Role::create(['name' => 'Supervisor Gudang', 'guard_name' => 'web']);
        $role->givePermissionTo(['bo.access', 'bo.products.view', 'bo.stock_transfers.view']);

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('cloneRole', $role, data: [
                'name' => 'Staf Gudang Junior',
            ])
            ->assertHasNoTableActionErrors();

        $clonedRole = Role::findByName('Staf Gudang Junior');
        $this->assertTrue($clonedRole->hasPermissionTo('bo.access'));
        $this->assertTrue($clonedRole->hasPermissionTo('bo.products.view'));
        $this->assertTrue($clonedRole->hasPermissionTo('bo.stock_transfers.view'));

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->callTableAction('deleteRole', $clonedRole)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('roles', ['name' => 'Staf Gudang Junior']);
    }

    public function test_owner_role_edit_and_delete_actions_are_hidden(): void
    {
        $ownerRole = Role::findByName('owner');

        Livewire::actingAs($this->user)
            ->test(UserPrivileges::class)
            ->assertTableActionHidden('editPermissions', $ownerRole)
            ->assertTableActionHidden('deleteRole', $ownerRole);
    }

    public function test_custom_user_without_manage_permission_cannot_create_or_edit_roles(): void
    {
        $viewerRole = Role::create(['name' => 'Viewer Akses', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo(['bo.access', 'bo.user_privileges.view']);

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole($viewerRole);

        Livewire::actingAs($viewer)
            ->test(UserPrivileges::class)
            ->assertStatus(200)
            ->assertActionHidden('createRole');
    }
}
