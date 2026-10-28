<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Livewire\PosUsers;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiBranchUserScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $branchAdmin1;
    protected User $cashierBranch1;
    protected User $cashierBranch2;
    protected Branch $branch1;
    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $this->branch1 = Branch::create(['name' => 'Cabang Jakarta', 'is_active' => true]);
        $this->branch2 = Branch::create(['name' => 'Cabang Surabaya', 'is_active' => true]);

        // 1. Owner (global across all branches)
        $this->owner = User::factory()->create([
            'name' => 'Bapak Owner',
            'email' => 'owner@diego.com',
            'is_active' => true,
        ]);
        $this->owner->assignRole($ownerRole);

        // 2. Admin Cabang 1
        $this->branchAdmin1 = User::factory()->create([
            'name' => 'Admin Jakarta',
            'email' => 'admin.jkt@diego.com',
            'is_active' => true,
        ]);
        $this->branchAdmin1->assignRole($adminRole);
        $this->branchAdmin1->branches()->attach($this->branch1->id);

        // 3. Kasir Cabang 1
        $this->cashierBranch1 = User::factory()->create([
            'name' => 'Kasir Jakarta',
            'email' => 'kasir.jkt@diego.com',
            'is_active' => true,
        ]);
        $this->cashierBranch1->assignRole($cashierRole);
        $this->cashierBranch1->branches()->attach($this->branch1->id);

        // 4. Kasir Cabang 2
        $this->cashierBranch2 = User::factory()->create([
            'name' => 'Kasir Surabaya',
            'email' => 'kasir.sby@diego.com',
            'is_active' => true,
        ]);
        $this->cashierBranch2->assignRole($cashierRole);
        $this->cashierBranch2->branches()->attach($this->branch2->id);
    }

    public function test_owner_can_see_all_users_across_all_branches(): void
    {
        $this->actingAs($this->owner);

        $query = UserResource::getEloquentQuery();

        $this->assertTrue($query->where('users.id', $this->owner->id)->exists());
        $this->assertTrue($query->where('users.id', $this->branchAdmin1->id)->exists());
        $this->assertTrue($query->where('users.id', $this->cashierBranch1->id)->exists());
        $this->assertTrue($query->where('users.id', $this->cashierBranch2->id)->exists());
    }

    public function test_branch_admin_only_sees_own_branch_users_and_cannot_see_owner_or_other_branches(): void
    {
        $this->actingAs($this->branchAdmin1);
        session(['pos_active_branch_id' => $this->branch1->id]);

        $query = UserResource::getEloquentQuery();

        // Must see own branch users
        $this->assertTrue($query->where('users.id', $this->branchAdmin1->id)->exists());
        $this->assertTrue($query->where('users.id', $this->cashierBranch1->id)->exists());

        // Must NOT see owner or other branch users
        $this->assertFalse($query->where('users.id', $this->owner->id)->exists());
        $this->assertFalse($query->where('users.id', $this->cashierBranch2->id)->exists());
    }

    public function test_pos_users_livewire_isolates_users_for_non_owner(): void
    {
        $this->actingAs($this->branchAdmin1);
        session(['pos_active_branch_id' => $this->branch1->id]);

        Livewire::test(PosUsers::class)
            ->assertViewHas('users', function ($users) {
                return $users->contains('id', $this->cashierBranch1->id)
                    && !$users->contains('id', $this->cashierBranch2->id)
                    && !$users->contains('id', $this->owner->id);
            });
    }

    public function test_pos_users_livewire_allows_owner_to_see_all_users(): void
    {
        $this->actingAs($this->owner);
        session(['pos_active_branch_id' => $this->branch1->id]);

        Livewire::test(PosUsers::class)
            ->assertViewHas('users', function ($users) {
                return $users->contains('id', $this->cashierBranch1->id)
                    && $users->contains('id', $this->cashierBranch2->id)
                    && $users->contains('id', $this->owner->id);
            });
    }
}
