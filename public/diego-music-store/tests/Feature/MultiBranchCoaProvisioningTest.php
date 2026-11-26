<?php

namespace Tests\Feature;

use App\Actions\Accounting\GenerateBalanceSheetReport;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Actions\Branch\ProvisionBranchCoaAccounts;
use App\Helpers\AccountHelper;
use App\Helpers\BranchHelper;
use App\Helpers\FinancialReportHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
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

        // Branch must have 96 detail accounts from CSV + 3 generated branch-scoped accounts (99 details, 145 total)
        $detailCount = Account::where('branch_id', $branch->id)->where('is_header', false)->count();
        $this->assertEquals(99, $detailCount);
        $totalCount = Account::where('branch_id', $branch->id)->count();
        $this->assertEquals(145, $totalCount);

        $hutangKonsinyasi = Account::where('branch_id', $branch->id)->where('code', '211101002')->first();
        $this->assertNotNull($hutangKonsinyasi);
        $this->assertSame('HUTANG KONSINYASI', $hutangKonsinyasi->name);
        $this->assertSame('211100000', Account::find($hutangKonsinyasi->parent_id)?->code);

        foreach (['111201006', '111202005', '111202008', '111300000', '111400000', '122101001', '122200000', '311200000', '311300000', '311400000'] as $removedCode) {
            $this->assertNull(Account::where('branch_id', $branch->id)->where('code', $removedCode)->first());
        }

        // Verify all equity detail accounts (Modal, Prive, Laba Ditahan, Laba Tahun Berjalan) sit under 311100000 (MODAL)
        $modalHeader = Account::where('branch_id', $branch->id)->where('code', '311100000')->first();
        $this->assertNotNull($modalHeader);
        $this->assertTrue($modalHeader->is_header);
        foreach (['311101001', '311101002', '311101003', '311201001', '311301001'] as $equityDetailCode) {
            $equityAcc = Account::where('branch_id', $branch->id)->where('code', $equityDetailCode)->first();
            $this->assertNotNull($equityAcc, "Equity account {$equityDetailCode} should exist");
            $this->assertSame($modalHeader->id, $equityAcc->parent_id, "Equity account {$equityDetailCode} should be parented under MODAL (311100000)");
        }

        $balanceSheet = (new GenerateBalanceSheetReport())->execute(now()->format('Y-m-d'), $branch->id);
        $equityByCode = collect($balanceSheet['equity']['items'])->keyBy('code');
        $this->assertSame(1, $equityByCode['300000000']['level']);
        $this->assertSame(2, $equityByCode['311100000']['level']);
        foreach (['311101001', '311101002', '311101003', '311201001', '311301001'] as $equityDetailCode) {
            $this->assertSame(3, $equityByCode[$equityDetailCode]['level']);
            $this->assertSame('311100000', $equityByCode[$equityDetailCode]['parent_code']);
        }

        // Verify root headers (Level 1) exist
        $rootHeaders = Account::where('branch_id', $branch->id)->whereNull('parent_id')->count();
        $this->assertEquals(6, $rootHeaders);

        // Verify sub-headers (Level 2) and detail accounts have correct parent_id
        $kasBesar = Account::where('branch_id', $branch->id)->where('code', '111101001')->first();
        $this->assertNotNull($kasBesar);
        $this->assertFalse($kasBesar->is_header);
        $this->assertNotNull($kasBesar->parent_id);

        $parent = Account::find($kasBesar->parent_id);
        $this->assertNotNull($parent);
        $this->assertEquals('111100000', $parent->code);
        $this->assertEquals($branch->id, $parent->branch_id);

        // Verify pointer columns on branch use dedicated generated accounts (not generic 111401001/111601001/211601001)
        $branch->refresh();
        $branch->load(['inventoryAccount', 'interbranchReceivableAccount', 'interbranchPayableAccount']);
        $this->assertNotNull($branch->inventoryAccount);
        $this->assertSame($branch->id, $branch->inventoryAccount->branch_id);
        $this->assertNotEquals('111401001', $branch->inventoryAccount->code);
        $this->assertStringStartsWith('11180', $branch->inventoryAccount->code);
        $this->assertSame('Persediaan Barang Dagang - Cabang Pontianak Kota', $branch->inventoryAccount->name);

        $this->assertNotNull($branch->interbranchReceivableAccount);
        $this->assertSame($branch->id, $branch->interbranchReceivableAccount->branch_id);
        $this->assertNotEquals('111601001', $branch->interbranchReceivableAccount->code);
        $this->assertStringStartsWith('11160', $branch->interbranchReceivableAccount->code);
        $this->assertSame('Piutang Antar Cabang - Cabang Pontianak Kota', $branch->interbranchReceivableAccount->name);

        $this->assertNotNull($branch->interbranchPayableAccount);
        $this->assertSame($branch->id, $branch->interbranchPayableAccount->branch_id);
        $this->assertNotEquals('211601001', $branch->interbranchPayableAccount->code);
        $this->assertStringStartsWith('21130', $branch->interbranchPayableAccount->code);
        $this->assertSame('Hutang Antar Cabang - Cabang Pontianak Kota', $branch->interbranchPayableAccount->name);
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

        $this->assertEquals(99, Account::where('branch_id', $branch1->id)->where('is_header', false)->count());
        $this->assertEquals(99, Account::where('branch_id', $branch2->id)->where('is_header', false)->count());
        $this->assertEquals(145, Account::where('branch_id', $branch1->id)->count());
        $this->assertEquals(145, Account::where('branch_id', $branch2->id)->count());

        // Branch-scoped report account resolution must NOT leak branch2's generated accounts into branch1
        $branch1->refresh()->load('inventoryAccount');
        $branch2->refresh()->load('inventoryAccount');
        $branch1ReportCodes = FinancialReportHelper::resolveConsolidatedAccounts($branch1->id)['accounts']->pluck('code')->all();
        $this->assertContains($branch1->inventoryAccount->code, $branch1ReportCodes);
        $this->assertNotContains($branch2->inventoryAccount->code, $branch1ReportCodes);
    }

    public function test_repairs_generic_inventory_link_and_relinks_initial_stock_while_preserving_legacy_codes(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pusat (Back Office)', 'is_active' => true]);
        app(ProvisionBranchCoaAccounts::class)->execute($branch);

        // Simulate prior polluted state: generic 111401001 hijacked and linked as branch inventory
        $genericInv = Account::where('branch_id', $branch->id)->where('code', '111401001')->firstOrFail();
        $genericInv->update(['name' => 'Persediaan Barang Dagang - Cabang Pusat (Back Office)']);
        $branch->update(['inventory_account_id' => $genericInv->id]);

        $equity = Account::where('branch_id', $branch->id)->where('code', '311101003')->firstOrFail();
        $stockEntry = JournalEntry::create([
            'branch_id' => $branch->id,
            'entry_no' => 'JV-STK-MIG-01',
            'date' => now()->toDateString(),
            'description' => 'Saldo Awal Persediaan',
            'reference_type' => 'InitialStock',
            'status' => 'posted',
            'posted_at' => now(),
        ]);
        JournalItem::create(['journal_entry_id' => $stockEntry->id, 'account_id' => $genericInv->id, 'debit' => 1952360700, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $stockEntry->id, 'account_id' => $equity->id, 'debit' => 0, 'credit' => 1952360700]);

        EnsureBranchCoaAccounts::execute($branch);
        $moved = EnsureBranchCoaAccounts::relinkInitialStockToGeneratedInventory($branch);

        $branch->refresh()->load('inventoryAccount');
        $genericInv->refresh();

        $this->assertSame(1, $moved);
        $this->assertSame('PERSEDIAAN BARANG DAGANG', $genericInv->name);
        $this->assertNotSame($genericInv->id, $branch->inventory_account_id);
        $this->assertStringStartsWith('11180', $branch->inventoryAccount->code);
        $this->assertSame(
            1,
            JournalItem::where('journal_entry_id', $stockEntry->id)->where('account_id', $branch->inventory_account_id)->count()
        );

        // Production legacy 11410001 with parent_id = null must be preserved and parented under 111800000
        $prodBranch = Branch::create(['name' => 'Cabang Prod Legacy', 'is_active' => true]);
        $legacyInv = Account::create([
            'code' => '11410001',
            'name' => 'Persediaan Barang Dagang - Cabang Prod Legacy',
            'classification' => 'asset',
            'account_subtype' => 'inventory',
            'normal_balance' => 'debit',
            'is_active' => true,
            'is_header' => false,
            'parent_id' => null,
        ]);
        $prodBranch->update(['inventory_account_id' => $legacyInv->id]);

        EnsureBranchCoaAccounts::execute($prodBranch);
        $prodBranch->refresh();
        $legacyInv->refresh();

        $this->assertSame($legacyInv->id, $prodBranch->inventory_account_id);
        $this->assertSame('11410001', $legacyInv->code);
        $this->assertNotNull($legacyInv->parent_id);
        $this->assertSame('111800000', Account::find($legacyInv->parent_id)?->code);
    }
}
