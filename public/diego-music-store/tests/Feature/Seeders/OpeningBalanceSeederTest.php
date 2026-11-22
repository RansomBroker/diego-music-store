<?php

namespace Tests\Feature\Seeders;

use App\Actions\Accounting\GenerateBalanceSheetReport;
use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Database\Seeders\AccountSeeder;
use Database\Seeders\OpeningBalanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalanceSeederTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat (Back Office)',
            'store_name' => 'Diego Music Store Pusat',
            'sku_prefix' => 'DG',
            'journal_prefix' => 'JV-PST',
            'address' => 'Jl. Gajah Mada',
            'phone' => '0561-123456',
            'is_active' => true,
        ]);

        $this->seed(AccountSeeder::class);
        EnsureBranchCoaAccounts::execute($this->branch);
    }

    public function test_seeds_opening_balance_once_with_fixed_asset_grouping_and_balanced_sheet(): void
    {
        // Confirm fixed COA detail account 122101001 exists on branch
        $intangible = AccountHelper::findByCode('122101001', $this->branch->id);
        $this->assertNotNull($intangible);
        $this->assertSame('ASET TIDAK BERWUJUD', $intangible->name);
        $this->assertFalse($intangible->is_header);

        $this->seed(OpeningBalanceSeeder::class);

        $entry = JournalEntry::where('branch_id', $this->branch->id)
            ->where('reference_type', 'OpeningBalance')
            ->first();
        $this->assertNotNull($entry);

        // Journal must be 100% balanced
        $totalDebit = (float) JournalItem::where('journal_entry_id', $entry->id)->sum('debit');
        $totalCredit = (float) JournalItem::where('journal_entry_id', $entry->id)->sum('credit');
        $this->assertSame($totalCredit, $totalDebit);
        $this->assertSame(1420168321.0, $totalDebit);

        // Re-running must be strictly idempotent (does not duplicate or overwrite)
        $this->seed(OpeningBalanceSeeder::class);
        $this->assertSame(1, JournalEntry::where('branch_id', $this->branch->id)->count());

        // Balance sheet must balance cleanly and put 12... fixed/intangible assets under Fixed Assets
        $report = app(GenerateBalanceSheetReport::class)->execute(
            now()->format('Y-m-d'),
            $this->branch->id,
            'detail',
            'all'
        );

        $this->assertTrue($report['is_balanced']);
        $this->assertEquals(573237960.0, $report['assets']['total_current_assets']);
        // 100,208,326 - 31,597,230 + 134,179,971 - 83,514,876 + 19,166,653 + 29,000,000 - 9,883,347 = 157,559,497
        $this->assertEquals(157559497.0, $report['assets']['total_fixed_assets']);
        $this->assertEquals(495650632.0, $report['total_liabilities']);

        $fixedCodes = array_column($report['assets']['fixed_assets'], 'code');
        $this->assertContains('121101001', $fixedCodes);
        $this->assertContains('121201001', $fixedCodes);
        $this->assertContains('122101001', $fixedCodes);
        $this->assertContains('128801001', $fixedCodes);
        $this->assertContains('128801002', $fixedCodes);

        // Consolidated ("Semua Cabang" -> branchId = null) across multiple branches must NOT duplicate COA codes
        $secondBranch = Branch::create([
            'name' => 'Cabang Singkawang',
            'store_name' => 'Diego Music Store Singkawang',
            'sku_prefix' => 'DG',
            'journal_prefix' => 'JV-SKW',
            'address' => 'Jl. Diponegoro',
            'phone' => '0562-631234',
            'is_active' => true,
        ]);
        EnsureBranchCoaAccounts::execute($secondBranch);

        $consolidated = app(GenerateBalanceSheetReport::class)->execute(
            now()->format('Y-m-d'),
            null,
            'detail',
            'all'
        );

        $this->assertTrue($consolidated['is_balanced']);
        $assetCodes = array_column($consolidated['assets']['items'], 'code');
        $this->assertCount(count(array_unique($assetCodes)), $assetCodes);
        $this->assertEquals(730797457.0, $consolidated['total_assets']);
    }

    public function test_skips_completely_when_operational_journals_already_exist(): void
    {
        $kas = AccountHelper::findByCode('111101001', $this->branch->id);
        $rev = AccountHelper::findByCode('411101001', $this->branch->id);

        $entry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-LIVE-001',
            'date' => now()->toDateString(),
            'description' => 'Penjualan berjalan',
            'reference_type' => 'Sales',
            'status' => 'posted',
            'posted_at' => now(),
        ]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $kas->id, 'debit' => 100000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $rev->id, 'debit' => 0, 'credit' => 100000]);

        $this->seed(OpeningBalanceSeeder::class);

        $this->assertSame(0, JournalEntry::where('reference_type', 'OpeningBalance')->count());
        $this->assertSame(1, JournalEntry::count());
    }

    public function test_skips_only_non_zero_accounts_when_initial_debt_already_imported(): void
    {
        $ap = AccountHelper::findByCode('211101001', $this->branch->id);
        $equity = AccountHelper::findByCode('311101003', $this->branch->id);

        $debtEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-DEBT-001',
            'date' => now()->toDateString(),
            'description' => 'Initial Debt Already Imported',
            'reference_type' => 'InitialDebt',
            'status' => 'posted',
            'posted_at' => now(),
        ]);
        JournalItem::create(['journal_entry_id' => $debtEntry->id, 'account_id' => $equity->id, 'debit' => 493973512, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $debtEntry->id, 'account_id' => $ap->id, 'debit' => 0, 'credit' => 493973512]);

        $this->seed(OpeningBalanceSeeder::class);

        $opening = JournalEntry::where('reference_type', 'OpeningBalance')->firstOrFail();
        // 211101001 was already non-zero so OpeningBalance must NOT touch it
        $this->assertSame(0, JournalItem::where('journal_entry_id', $opening->id)->where('account_id', $ap->id)->count());

        // Balance sheet still balances and AP stays 493,973,512 (no double count!)
        $report = app(GenerateBalanceSheetReport::class)->execute(now()->format('Y-m-d'), $this->branch->id, 'detail', 'all');
        $this->assertTrue($report['is_balanced']);
        // 493,973,512 AP + 1,095,600 Ongkir = 495,069,112
        $this->assertEquals(495069112.0, $report['total_liabilities']);
    }

    public function test_provisions_missing_intangible_asset_account_and_migration_skips_testing_env(): void
    {
        Account::where('code', '122101001')->delete();
        $this->assertNull(AccountHelper::findByCode('122101001', $this->branch->id));

        $migration = require database_path('migrations/2026_11_22_101000_seed_opening_balances_if_zero.php');
        $migration->up();

        // In testing environment, migration must skip automatically without creating journals
        $this->assertSame(0, JournalEntry::count());

        // Invoking the action directly provisions missing 122101001 and posts opening balance once
        app(\App\Actions\Accounting\SeedOpeningBalances::class)->execute();

        $intangible = AccountHelper::findByCode('122101001', $this->branch->id);
        $this->assertNotNull($intangible);
        $this->assertSame('ASET TIDAK BERWUJUD', $intangible->name);
        $this->assertSame(1, JournalEntry::where('reference_type', 'OpeningBalance')->count());
    }
}

