<?php

namespace Tests\Feature;

use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchSwitchAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;
    protected Branch $branch2;
    protected Branch $branch3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch1 = Branch::create(['name' => 'Cabang Pusat', 'store_name' => 'Diego Pusat', 'is_active' => true]);
        $this->branch2 = Branch::create(['name' => 'Cabang Siantan', 'store_name' => 'Diego Siantan', 'is_active' => true]);
        $this->branch3 = Branch::create(['name' => 'Cabang Flamboyan', 'store_name' => 'Diego Flamboyan', 'is_active' => true]);
    }

    public function test_owner_and_super_admin_can_switch_to_all_active_branches(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');

        $this->assertTrue($owner->canAccessAllBranches());
        $this->assertTrue(BranchHelper::canUserSwitchToBranch($owner, $this->branch1));
        $this->assertTrue(BranchHelper::canUserSwitchToBranch($owner, $this->branch2));
        $this->assertTrue(BranchHelper::canUserSwitchToBranch($owner, $this->branch3));

        $this->actingAs($owner)->get(route('pos.switch-branch', $this->branch2->id));
        $this->assertSame($this->branch2->id, session('pos_active_branch_id'));
    }

    public function test_admin_can_switch_to_all_active_branches(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->canAccessAllBranches());
        $this->assertTrue(BranchHelper::canUserSwitchToBranch($admin, $this->branch2));

        $this->actingAs($admin)->get(route('pos.switch-branch', $this->branch2->id));
        $this->assertSame($this->branch2->id, session('pos_active_branch_id'));
    }

    public function test_user_with_branch_switch_or_master_branches_permission_can_switch_all_branches(): void
    {
        Permission::firstOrCreate(['name' => 'branch.switch', 'guard_name' => 'web']);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('karyawan');
        $user->givePermissionTo('branch.switch');

        $this->assertTrue($user->canAccessAllBranches());
        $this->assertTrue(BranchHelper::canUserSwitchToBranch($user, $this->branch3));

        $this->actingAs($user)->get(route('pos.switch-branch', $this->branch3->id));
        $this->assertSame($this->branch3->id, session('pos_active_branch_id'));
    }

    public function test_regular_user_with_multiple_branches_can_switch_between_assigned_branches_only(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('karyawan');
        $user->branches()->attach([$this->branch1->id, $this->branch2->id]);

        $this->assertFalse($user->canAccessAllBranches());
        $this->assertTrue($user->canAccessBranch($this->branch1));
        $this->assertTrue($user->canAccessBranch($this->branch2));
        $this->assertFalse($user->canAccessBranch($this->branch3));

        // Switch to branch 2 should succeed
        $this->actingAs($user)->get(route('pos.switch-branch', $this->branch2->id));
        $this->assertSame($this->branch2->id, session('pos_active_branch_id'));

        // Switch to unassigned branch 3 should be rejected and session unchanged
        $this->actingAs($user)->get(route('pos.switch-branch', $this->branch3->id));
        $this->assertSame($this->branch2->id, session('pos_active_branch_id'));
    }

    public function test_user_cannot_switch_branch_while_having_active_cash_session(): void
    {
        $cashier = User::factory()->create(['is_active' => true]);
        $cashier->assignRole('karyawan');
        $cashier->branches()->attach([$this->branch1->id, $this->branch2->id]);

        session(['pos_active_branch_id' => $this->branch1->id]);

        // Open a cash session on branch 1
        CashSession::create([
            'branch_id' => $this->branch1->id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($cashier)->get(route('pos.switch-branch', $this->branch2->id));
        $response->assertSessionHas('error');

        // pos_active_branch_id should remain branch1
        $this->assertSame($this->branch1->id, session('pos_active_branch_id'));
    }

    public function test_owner_can_switch_branch_even_if_active_cash_session_exists(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');

        session(['pos_active_branch_id' => $this->branch1->id]);

        // Open a cash session on branch 1
        CashSession::create([
            'branch_id' => $this->branch1->id,
            'user_id' => $owner->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        // Owner switch to branch 2 MUST succeed and NOT be blocked
        $response = $this->actingAs($owner)->get(route('pos.switch-branch', $this->branch2->id));
        $response->assertSessionMissing('error');
        $this->assertSame($this->branch2->id, session('pos_active_branch_id'));
    }

    public function test_allowed_branches_query_reflects_role_and_permissions(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');

        $this->assertCount(3, BranchHelper::getAllowedBranchesQuery($owner)->get());

        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach([$this->branch1->id]);

        $this->assertCount(1, BranchHelper::getAllowedBranchesQuery($karyawan)->get());
    }
}
