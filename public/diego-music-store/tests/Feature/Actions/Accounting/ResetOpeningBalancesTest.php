<?php

namespace Tests\Feature\Actions\Accounting;

use App\Actions\Accounting\ResetOpeningBalances;
use App\Actions\Accounting\SeedOpeningBalances;
use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Helpers\AccountHelper;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetOpeningBalancesTest extends TestCase
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

    public function test_resets_only_opening_balance_journals_and_preserves_initial_debt_and_stock(): void
    {
        $ap = AccountHelper::findByCode('211101001', $this->branch->id);
        $inventory = AccountHelper::findByCode('111401001', $this->branch->id);
        $equity = AccountHelper::findByCode('311101003', $this->branch->id);

        $stockEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-STK-001',
            'date' => now()->toDateString(),
            'description' => 'Saldo Awal Persediaan',
            'reference_type' => 'InitialStock',
            'status' => 'posted',
            'posted_at' => now(),
        ]);
        JournalItem::create(['journal_entry_id' => $stockEntry->id, 'account_id' => $inventory->id, 'debit' => 5000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $stockEntry->id, 'account_id' => $equity->id, 'debit' => 0, 'credit' => 5000000]);

        $debtEntry = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-DBT-001',
            'date' => now()->toDateString(),
            'description' => 'Saldo Awal Hutang',
            'reference_type' => 'InitialDebt',
            'status' => 'posted',
            'posted_at' => now(),
        ]);
        JournalItem::create(['journal_entry_id' => $debtEntry->id, 'account_id' => $equity->id, 'debit' => 3000000, 'credit' => 0]);
        JournalItem::create(['journal_entry_id' => $debtEntry->id, 'account_id' => $ap->id, 'debit' => 0, 'credit' => 3000000]);

        $opening = app(SeedOpeningBalances::class)->execute();
        $this->assertNotNull($opening);
        $this->assertSame(3, JournalEntry::count());

        $result = app(ResetOpeningBalances::class)->execute($this->branch->id);

        $this->assertSame(1, $result['deleted_entries']);
        $this->assertGreaterThan(0, $result['deleted_items']);
        $this->assertSame(0, JournalEntry::where('reference_type', 'OpeningBalance')->count());
        $this->assertSame(2, JournalEntry::count());
        $this->assertSame(4, JournalItem::count());

        // Resetting again returns zeroes safely
        $secondReset = app(ResetOpeningBalances::class)->execute($this->branch->id);
        $this->assertSame(['deleted_entries' => 0, 'deleted_items' => 0], $secondReset);

        // Can re-run SeedOpeningBalances cleanly after reset
        $reseeded = app(SeedOpeningBalances::class)->execute();
        $this->assertNotNull($reseeded);
        $this->assertSame(1, JournalEntry::where('reference_type', 'OpeningBalance')->count());
    }
}

