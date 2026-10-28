<?php

namespace Tests\Feature;

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\ProvisionBranchCoaAccounts;
use App\Helpers\AccountHelper;
use App\Helpers\BranchHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiBranchCoaProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_branch_automatically_provisions_all_97_coa_accounts_with_hierarchy(): void
    {
        $branch = CreateBranch::execute([
            'name'       => 'Cabang Pontianak Kota',
            'store_name' => 'Diego Musik Pontianak',
            'city'       => 'Pontianak',
        ]);

        $this->assertDatabaseHas('branches', [
            'id'   => $branch->id,
            'name' => 'Cabang Pontianak Kota',
        ]);

        // Branch must have exactly 97 COA accounts provisioned
        $branchAccountsCount = Account::where('branch_id', $branch->id)->count();
        $this->assertEquals(97, $branchAccountsCount);

        // Verify root headers (Level 1) exist
        $rootHeaders = Account::where('branch_id', $branch->id)->whereNull('parent_id')->count();
        $this->assertEquals(8, $rootHeaders);

        // Verify sub-headers (Level 2) and detail accounts have correct parent_id
        $kasBesar = Account::where('branch_id', $branch->id)->where('code', '111101001')->first();
        $this->assertNotNull($kasBesar);
        $this->assertFalse($kasBesar->is_header);
        $this->assertNotNull($kasBesar->parent_id);

        $parent = Account::find($kasBesar->parent_id);
        $this->assertNotNull($parent);
        $this->assertEquals('111101', $parent->code);
        $this->assertEquals($branch->id, $parent->branch_id);

        // Verify pointer columns on branch are set
        $branch->refresh();
        $this->assertNotNull($branch->inventory_account_id);
        $this->assertNotNull($branch->interbranch_receivable_account_id);
        $this->assertNotNull($branch->interbranch_payable_account_id);
    }

    public function test_multiple_branches_can_have_identical_account_codes_without_collision(): void
    {
        $branchA = CreateBranch::execute([
            'name'       => 'Cabang A',
            'store_name' => 'Store A',
        ]);

        $branchB = CreateBranch::execute([
            'name'       => 'Cabang B',
            'store_name' => 'Store B',
        ]);

        $accountA = Account::where('branch_id', $branchA->id)->where('code', '111101001')->first();
        $accountB = Account::where('branch_id', $branchB->id)->where('code', '111101001')->first();

        $this->assertNotNull($accountA);
        $this->assertNotNull($accountB);
        $this->assertNotEquals($accountA->id, $accountB->id);
        $this->assertEquals('111101001', $accountA->code);
        $this->assertEquals('111101001', $accountB->code);
    }

    public function test_account_helper_resolves_account_belonging_to_active_branch(): void
    {
        $branchA = CreateBranch::execute(['name' => 'Cabang Alpha', 'store_name' => 'Alpha']);
        $branchB = CreateBranch::execute(['name' => 'Cabang Beta', 'store_name' => 'Beta']);

        $owner = User::create([
            'name'     => 'Owner Test',
            'username' => 'ownertest',
            'email'    => 'owner@test.com',
            'password' => bcrypt('password'),
        ]);

        // Acting as user with branch A active
        $this->actingAs($owner);
        session(['pos_active_branch_id' => $branchA->id]);

        $resolvedA = AccountHelper::findByCode('111101001');
        $this->assertNotNull($resolvedA);
        $this->assertEquals($branchA->id, $resolvedA->branch_id);

        // Switch active session to branch B
        session(['pos_active_branch_id' => $branchB->id]);

        $resolvedB = AccountHelper::findByCode('111101001');
        $this->assertNotNull($resolvedB);
        $this->assertEquals($branchB->id, $resolvedB->branch_id);
    }

    public function test_provision_all_populates_missing_accounts_for_all_branches(): void
    {
        $branch1 = Branch::create(['name' => 'Cabang Tanpa Akun 1', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Cabang Tanpa Akun 2', 'is_active' => true]);

        $this->assertEquals(0, Account::where('branch_id', $branch1->id)->count());
        $this->assertEquals(0, Account::where('branch_id', $branch2->id)->count());

        app(ProvisionBranchCoaAccounts::class)->executeAll();

        $this->assertEquals(97, Account::where('branch_id', $branch1->id)->count());
        $this->assertEquals(97, Account::where('branch_id', $branch2->id)->count());
    }
}
