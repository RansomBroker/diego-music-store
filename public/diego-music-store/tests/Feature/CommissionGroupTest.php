<?php

namespace Tests\Feature;

use App\Actions\Commission\ApproveGroupCommission;
use App\Actions\Commission\CalculateSaleCommission;
use App\Actions\Commission\EvaluateGroupCommission;
use App\Livewire\PosCommissions;
use App\Models\Branch;
use App\Models\CommissionGroup;
use App\Models\CommissionGroupMember;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommissionGroupTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Employee $leader;
    protected Employee $memberA;
    protected Employee $memberB;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $userLeader = User::factory()->create();
        $userLeader->assignRole('sales');
        $this->leader = Employee::create([
            'user_id' => $userLeader->id,
            'branch_id' => $this->branch->id,
            'nik' => 'EMP-001',
            'name' => 'Budi Leader',
            'is_active' => true,
        ]);

        $userA = User::factory()->create();
        $userA->assignRole('sales');
        $this->memberA = Employee::create([
            'user_id' => $userA->id,
            'branch_id' => $this->branch->id,
            'nik' => 'EMP-002',
            'name' => 'Sales Andi',
            'is_active' => true,
        ]);

        $userB = User::factory()->create();
        $userB->assignRole('sales');
        $this->memberB = Employee::create([
            'user_id' => $userB->id,
            'branch_id' => $this->branch->id,
            'nik' => 'EMP-003',
            'name' => 'Sales Bimo',
            'is_active' => true,
        ]);
    }

    public function test_group_commission_locked_when_one_member_fails_target(): void
    {
        $group = CommissionGroup::create([
            'branch_id' => $this->branch->id,
            'name' => 'Tim Sales Gitar',
            'leader_employee_id' => $this->leader->id,
            'rate' => 0.10, // 0.1%
            'is_active' => true,
        ]);

        CommissionGroupMember::create([
            'commission_group_id' => $group->id,
            'employee_id' => $this->memberA->id,
            'monthly_target_amount' => 10000000, // Target: 10 Juta
        ]);

        CommissionGroupMember::create([
            'commission_group_id' => $group->id,
            'employee_id' => $this->memberB->id,
            'monthly_target_amount' => 15000000, // Target: 15 Juta
        ]);

        $year = (int) now()->year;
        $month = (int) now()->month;

        // Member A achieves target (12 Juta > 10 Juta)
        SalesCommissionLog::create([
            'employee_id' => $this->memberA->id,
            'date' => now()->toDateString(),
            'sale_amount' => 12000000,
            'commission_amount' => 240000,
            'status' => 'pending',
        ]);

        // Member B fails target (10 Juta < 15 Juta)
        SalesCommissionLog::create([
            'employee_id' => $this->memberB->id,
            'date' => now()->toDateString(),
            'sale_amount' => 10000000,
            'commission_amount' => 200000,
            'status' => 'pending',
        ]);

        $evaluator = new EvaluateGroupCommission();
        $result = $evaluator->execute($group, $year, $month);

        $this->assertFalse($result['is_unlocked'], 'Grup harus berstatus LOCKED jika ada anggota yang tidak achieve.');
        $this->assertEquals(0.0, $result['commission_amount'], 'Komisi harus Rp 0 jika grup locked.');
        $this->assertEquals(2, $result['total_members']);
        $this->assertEquals(1, $result['achieved_members_count']);
        $this->assertEquals(1, $result['unachieved_members_count']);
        $this->assertEquals(22000000, $result['total_group_sales']);

        // Attempting approval should fail
        $approver = new ApproveGroupCommission();
        $claim = $approver->execute($group, $year, $month, $this->user);

        $this->assertFalse($claim['success']);
        $this->assertStringContainsString('masih terkunci', $claim['message']);
    }

    public function test_group_commission_unlocked_when_all_members_achieve_target(): void
    {
        $group = CommissionGroup::create([
            'branch_id' => $this->branch->id,
            'name' => 'Tim Sales Gitar',
            'leader_employee_id' => $this->leader->id,
            'rate' => 0.10, // 0.1%
            'is_active' => true,
        ]);

        CommissionGroupMember::create([
            'commission_group_id' => $group->id,
            'employee_id' => $this->memberA->id,
            'monthly_target_amount' => 10000000,
        ]);

        CommissionGroupMember::create([
            'commission_group_id' => $group->id,
            'employee_id' => $this->memberB->id,
            'monthly_target_amount' => 15000000,
        ]);

        $year = (int) now()->year;
        $month = (int) now()->month;

        // Member A achieves target (12 Juta)
        SalesCommissionLog::create([
            'employee_id' => $this->memberA->id,
            'date' => now()->toDateString(),
            'sale_amount' => 12000000,
            'commission_amount' => 240000,
            'status' => 'pending',
        ]);

        // Member B achieves target (18 Juta >= 15 Juta)
        SalesCommissionLog::create([
            'employee_id' => $this->memberB->id,
            'date' => now()->toDateString(),
            'sale_amount' => 18000000,
            'commission_amount' => 360000,
            'status' => 'pending',
        ]);

        $evaluator = new EvaluateGroupCommission();
        $result = $evaluator->execute($group, $year, $month);

        $this->assertTrue($result['is_unlocked'], 'Grup harus berstatus UNLOCKED karena semua anggota achieve.');
        $this->assertEquals(30000000, $result['total_group_sales']);
        // 0.1% dari 30.000.000 = 30.000
        $this->assertEquals(30000.0, $result['commission_amount']);
        $this->assertEquals(2, $result['achieved_members_count']);
        $this->assertEquals(0, $result['unachieved_members_count']);

        // Approving group commission should succeed and create a log for leader
        $approver = new ApproveGroupCommission();
        $claim = $approver->execute($group, $year, $month, $this->user);

        $this->assertTrue($claim['success']);
        $this->assertNotNull($claim['log']);
        $this->assertEquals($this->leader->id, $claim['log']->employee_id);
        $this->assertEquals(30000.0, (float) $claim['log']->commission_amount);
        $this->assertEquals('approved', $claim['log']->status);
    }

    public function test_leader_still_earns_personal_commission_independently(): void
    {
        // 1. Leader earns personal sales commission (e.g. 100.000)
        SalesCommissionLog::create([
            'employee_id' => $this->leader->id,
            'date' => now()->toDateString(),
            'sale_amount' => 5000000,
            'commission_amount' => 100000,
            'status' => 'approved',
            'notes' => 'Komisi pribadi sales',
        ]);

        // 2. Leader also gets unlocked group commission (30.000)
        $group = CommissionGroup::create([
            'branch_id' => $this->branch->id,
            'name' => 'Tim Sales Retail',
            'leader_employee_id' => $this->leader->id,
            'rate' => 0.10,
            'is_active' => true,
        ]);

        CommissionGroupMember::create([
            'commission_group_id' => $group->id,
            'employee_id' => $this->memberA->id,
            'monthly_target_amount' => 5000000,
        ]);

        SalesCommissionLog::create([
            'employee_id' => $this->memberA->id,
            'date' => now()->toDateString(),
            'sale_amount' => 30000000,
            'commission_amount' => 600000,
            'status' => 'pending',
        ]);

        $year = (int) now()->year;
        $month = (int) now()->month;

        $approver = new ApproveGroupCommission();
        $claim = $approver->execute($group, $year, $month, $this->user);

        $this->assertTrue($claim['success']);

        // Total commissions accumulated for leader in this month: 100.000 + 30.000 = 130.000
        $leaderTotal = (float) SalesCommissionLog::where('employee_id', $this->leader->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('commission_amount');

        $this->assertEquals(130000.0, $leaderTotal);
    }

    public function test_livewire_pos_commissions_group_crud_and_claim(): void
    {
        $this->actingAs($this->user);

        // Test Livewire component
        $component = Livewire::test(PosCommissions::class)
            ->set('activeTab', 'groups')
            ->call('openGroupModal')
            ->assertSet('showGroupModal', true)
            ->set('groupName', 'Tim Sukses 2026')
            ->set('groupLeaderEmployeeId', $this->leader->id)
            ->set('groupRate', 0.10)
            ->set('groupMembers', [
                ['employee_id' => $this->memberA->id, 'monthly_target_amount' => 5000000],
                ['employee_id' => $this->memberB->id, 'monthly_target_amount' => 8000000],
            ])
            ->call('saveGroup')
            ->assertSet('showGroupModal', false)
            ->assertDispatched('toast');

        $this->assertDatabaseHas('commission_groups', [
            'name' => 'Tim Sukses 2026',
            'leader_employee_id' => $this->leader->id,
        ]);

        $this->assertDatabaseHas('commission_group_members', [
            'employee_id' => $this->memberA->id,
            'monthly_target_amount' => 5000000,
        ]);

        $createdGroup = CommissionGroup::where('name', 'Tim Sukses 2026')->first();

        // Make members achieve targets
        SalesCommissionLog::create([
            'employee_id' => $this->memberA->id,
            'date' => now()->toDateString(),
            'sale_amount' => 10000000,
            'commission_amount' => 200000,
            'status' => 'pending',
        ]);

        SalesCommissionLog::create([
            'employee_id' => $this->memberB->id,
            'date' => now()->toDateString(),
            'sale_amount' => 10000000,
            'commission_amount' => 200000,
            'status' => 'pending',
        ]);

        // Open detail modal and claim
        $component->call('openGroupDetailModal', $createdGroup->id)
            ->assertSet('showGroupDetailModal', true)
            ->call('claimGroupCommission', $createdGroup->id)
            ->assertSet('showGroupDetailModal', false)
            ->assertDispatched('toast');

        // Leader should have a group commission log now (0.1% of 20 Juta = 20.000)
        $this->assertDatabaseHas('sales_commission_logs', [
            'employee_id' => $this->leader->id,
            'commission_amount' => 20000,
            'status' => 'approved',
        ]);
    }
}
