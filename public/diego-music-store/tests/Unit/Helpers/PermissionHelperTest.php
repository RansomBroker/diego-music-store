<?php

namespace Tests\Unit\Helpers;

use App\Helpers\PermissionHelper;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_only_owner_variants_are_identified_as_owner_and_protected(): void
    {
        foreach (['owner', 'Owner', 'OWNER'] as $name) {
            $this->assertTrue(PermissionHelper::isProtectedRoleName($name));
        }

        foreach (['super_admin', 'admin', 'karyawan', 'sales', 'manager'] as $name) {
            $this->assertFalse(PermissionHelper::isProtectedRoleName($name));
        }

        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $this->assertTrue(PermissionHelper::isOwner($owner));

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole($superAdminRole);
        $this->assertFalse(PermissionHelper::isOwner($superAdmin));
    }

    public function test_config_sections_contain_valid_unique_canonical_permissions(): void
    {
        $sections = PermissionHelper::getSections();

        $this->assertArrayHasKey('frontoffice', $sections);
        $this->assertArrayHasKey('backoffice', $sections);
        $this->assertSame('fo.access', $sections['frontoffice']['access_permission']);
        $this->assertSame('bo.access', $sections['backoffice']['access_permission']);

        $all = PermissionHelper::getAllPermissionNames();
        $this->assertNotEmpty($all);
        $this->assertSame(count($all), count(array_unique($all)));
        $this->assertContains('fo.access', $all);
        $this->assertContains('bo.access', $all);
        $this->assertContains('fo.pos.view', $all);
        $this->assertContains('bo.user_privileges.manage', $all);
    }

    public function test_feature_access_requires_both_panel_gate_and_feature_permission(): void
    {
        $role = Role::create(['name' => 'Kasir Terbatas', 'guard_name' => 'web']);
        $role->syncPermissions(['fo.pos.view']);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        // Has feature permission without fo.access panel gate => denied
        $this->assertFalse(PermissionHelper::canAccessFrontoffice($user));
        $this->assertFalse(PermissionHelper::canAccessFrontofficeFeature($user, 'fo.pos.view'));

        // Add fo.access => allowed for fo.pos.view, still denied for ungranted features & BO
        $role->givePermissionTo('fo.access');
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $this->assertTrue(PermissionHelper::canAccessFrontoffice($user));
        $this->assertTrue(PermissionHelper::canAccessFrontofficeFeature($user, 'fo.pos.view'));
        $this->assertFalse(PermissionHelper::canAccessFrontofficeFeature($user, 'fo.pos.discount'));
        $this->assertFalse(PermissionHelper::canAccessBackoffice($user));
        $this->assertFalse(PermissionHelper::canAccessBackofficeFeature($user, 'bo.dashboard.view'));
    }

    public function test_resolve_role_access_summary_labels_all_combinations(): void
    {
        $owner = Role::findByName('owner');
        $this->assertSame('FO & BO (Penuh)', PermissionHelper::resolveRoleAccessSummary($owner)['access_label']);

        $foOnly = Role::create(['name' => 'FO Saja', 'guard_name' => 'web']);
        $foOnly->syncPermissions(['fo.access', 'fo.dashboard.view']);
        $foSummary = PermissionHelper::resolveRoleAccessSummary($foOnly);
        $this->assertTrue($foSummary['has_fo']);
        $this->assertFalse($foSummary['has_bo']);
        $this->assertSame('Front Office', $foSummary['access_label']);
        $this->assertSame(1, $foSummary['fo_count']);

        $boOnly = Role::create(['name' => 'BO Saja', 'guard_name' => 'web']);
        $boOnly->syncPermissions(['bo.access', 'bo.dashboard.view', 'bo.products.view']);
        $boSummary = PermissionHelper::resolveRoleAccessSummary($boOnly);
        $this->assertFalse($boSummary['has_fo']);
        $this->assertTrue($boSummary['has_bo']);
        $this->assertSame('Backoffice', $boSummary['access_label']);
        $this->assertSame(2, $boSummary['bo_count']);

        $both = Role::create(['name' => 'Manager Hybrid', 'guard_name' => 'web']);
        $both->syncPermissions(['fo.access', 'bo.access', 'fo.dashboard.view', 'bo.dashboard.view']);
        $bothSummary = PermissionHelper::resolveRoleAccessSummary($both);
        $this->assertTrue($bothSummary['has_fo']);
        $this->assertTrue($bothSummary['has_bo']);
        $this->assertSame('FO & BO', $bothSummary['access_label']);

        $none = Role::create(['name' => 'Kosong', 'guard_name' => 'web']);
        $this->assertSame('Belum Aktif', PermissionHelper::resolveRoleAccessSummary($none)['access_label']);
    }
}

