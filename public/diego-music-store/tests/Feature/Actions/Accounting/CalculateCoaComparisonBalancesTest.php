<?php

namespace Tests\Feature\Actions\Accounting;

use App\Actions\Accounting\CalculateCoaComparisonBalances;
use App\Filament\Widgets\FinancialComparisonChartWidget;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalculateCoaComparisonBalancesTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;
    protected Account $assetHeader;
    protected Account $liabilityHeader;
    protected Account $equityHeader;
    protected Account $cashAccount;
    protected Account $payableAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchA = Branch::create(['name' => 'Cabang Jakarta', 'code' => 'JKT', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang Surabaya', 'code' => 'SBY', 'is_active' => true]);

        // Create Accounts Hierarchy
        $this->assetHeader = Account::create([
            'code' => '100000000',
            'name' => 'ASET',
            'classification' => 'asset',
            'is_header' => true,
            'is_active' => true,
        ]);

        $this->liabilityHeader = Account::create([
            'code' => '200000000',
            'name' => 'LIABILITAS',
            'classification' => 'liability',
            'is_header' => true,
            'is_active' => true,
        ]);

        $this->equityHeader = Account::create([
            'code' => '300000000',
            'name' => 'EKUITAS',
            'classification' => 'equity',
            'is_header' => true,
            'is_active' => true,
        ]);

        // Child detail accounts
        $this->cashAccount = Account::create([
            'code' => '111101001',
            'name' => 'KAS UTAMA',
            'classification' => 'asset',
            'is_header' => false,
            'parent_id' => $this->assetHeader->id,
            'is_active' => true,
        ]);

        $this->payableAccount = Account::create([
            'code' => '211101001',
            'name' => 'HUTANG DAGANG',
            'classification' => 'liability',
            'is_header' => false,
            'parent_id' => $this->liabilityHeader->id,
            'is_active' => true,
        ]);
    }

    public function test_empty_accounts_returns_empty_results(): void
    {
        $action = new CalculateCoaComparisonBalances();
        $result = $action->execute([]);

        $this->assertEmpty($result['items']);
        $this->assertEquals(0.0, $result['total_chart_value']);
        $this->assertNull($result['ratio']);
    }

    public function test_calculates_cumulative_balance_for_headers_from_child_journal_entries(): void
    {
        $user = User::factory()->create();

        // Create a posted journal entry in Branch A:
        // Debit: KAS (Asset) Rp 10.000.000
        // Credit: HUTANG DAGANG (Liability) Rp 4.000.000
        $entry = JournalEntry::create([
            'branch_id' => $this->branchA->id,
            'entry_no' => 'JV-TEST-001',
            'date' => now()->format('Y-m-d'),
            'description' => 'Pinjaman & Tambahan Kas',
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->cashAccount->id,
            'debit' => 10000000,
            'credit' => 0,
        ]);

        JournalItem::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $this->payableAccount->id,
            'debit' => 0,
            'credit' => 4000000,
        ]);

        $action = new CalculateCoaComparisonBalances();
        $result = $action->execute(['100000000', '200000000'], $this->branchA->id);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(10000000.0, $result['items'][0]['raw_balance']);
        $this->assertEquals(4000000.0, $result['items'][1]['raw_balance']);

        // Check ratio: Liabilitas / Aset = 4.000.000 / 10.000.000 = 40.0%
        $this->assertNotNull($result['ratio']);
        $this->assertEquals(40.0, $result['ratio']['percentage']);
    }

    public function test_branch_isolation(): void
    {
        $user = User::factory()->create();

        // Posted entry for Branch B only
        $entryB = JournalEntry::create([
            'branch_id' => $this->branchB->id,
            'entry_no' => 'JV-TEST-002',
            'date' => now()->format('Y-m-d'),
            'description' => 'Kas Cabang B',
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        JournalItem::create([
            'journal_entry_id' => $entryB->id,
            'account_id' => $this->cashAccount->id,
            'debit' => 5000000,
            'credit' => 0,
        ]);

        $action = new CalculateCoaComparisonBalances();

        // Querying for Branch A should yield 0
        $resultA = $action->execute(['100000000'], $this->branchA->id);
        $this->assertEquals(0.0, $resultA['items'][0]['raw_balance']);

        // Querying for Branch B should yield 5.000.000
        $resultB = $action->execute(['100000000'], $this->branchB->id);
        $this->assertEquals(5000000.0, $resultB['items'][0]['raw_balance']);
    }

    public function test_financial_comparison_chart_widget_renders_and_handles_interactions(): void
    {
        $component = Livewire::test(FinancialComparisonChartWidget::class)
            ->assertStatus(200)
            ->assertSee('Komparasi Keuangan (COA)')
            ->assertSee('Liabilitas vs Aset')
            ->call('applyPreset', 'balance_sheet')
            ->assertSet('selectedAccounts', ['100000000', '200000000', '300000000'])
            ->call('removeAccount', '300000000')
            ->assertSet('selectedAccounts', ['100000000', '200000000'])
            ->call('toggleAccount', '100000000')
            ->assertSet('selectedAccounts', ['200000000']);

        $component->assertStatus(200);
    }
}
