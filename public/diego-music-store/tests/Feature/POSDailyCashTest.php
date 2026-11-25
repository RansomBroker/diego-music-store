<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSDailyCashTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Account $cashAccount;
    protected Account $capitalAccount;
    protected Account $expenseAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Seed basic accounts
        $this->cashAccount = Account::firstOrCreate(['code' => '111101001'], ['name' => 'KAS', 'classification' => 'Asset', 'is_active' => true]);
        $this->capitalAccount = Account::firstOrCreate(['code' => '311101001'], ['name' => 'MODAL DISETOR', 'classification' => 'Equity', 'is_active' => true]);
        $this->expenseAccount = Account::firstOrCreate(['code' => '611101014'], ['name' => 'BEBAN UMUM LAIN-LAIN', 'classification' => 'Expense', 'is_active' => true]);

        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole($ownerRole);
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);
    }

    /** @test */
    public function it_shows_warning_when_no_active_session()
    {
        $response = $this->get(route('pos.daily-cash'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Kasir Belum Dibuka');
    }

    /** @test */
    public function it_can_render_the_daily_cash_page_with_active_session()
    {
        // Create active session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'actual_cash' => 500000,
            'difference' => 0,
        ]);

        $response = $this->get(route('pos.daily-cash'));

        $response->assertStatus(200);
        $response->assertSee('Kas Harian (Petty Cash)');
        $response->assertSee('Rp 500.000');
    }

    /** @test */
    public function it_can_record_cash_inflow()
    {
        // Create active session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'actual_cash' => 500000,
            'difference' => 0,
        ]);

        Livewire::test('App\Livewire\POSDailyCash')
            ->call('openInModal')
            ->assertSet('showInModal', true)
            ->set('inAmount', 150000)
            ->set('inSourceAccountId', $this->capitalAccount->id)
            ->set('inNotes', 'Tambahan modal laci kasir')
            ->call('saveInflow')
            ->assertSet('showInModal', false)
            ->assertSet('expectedCash', 650000); // 500k + 150k

        // Assert database records
        $this->assertDatabaseHas('cash_transactions', [
            'cash_session_id' => $session->id,
            'type' => 'in',
            'amount' => 150000,
            'source_account_id' => $this->capitalAccount->id,
            'destination_account_id' => $this->cashAccount->id,
            'notes' => 'Tambahan modal laci kasir',
            'status' => 'posted',
        ]);
    }

    /** @test */
    public function it_can_record_cash_outflow()
    {
        // Create active session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'actual_cash' => 500000,
            'difference' => 0,
        ]);

        Livewire::test('App\Livewire\POSDailyCash')
            ->call('openOutModal')
            ->assertSet('showOutModal', true)
            ->set('outAmount', 50000)
            ->set('outDestinationAccountId', $this->expenseAccount->id)
            ->set('outNotes', 'Uang galon aqua')
            ->call('saveOutflow')
            ->assertSet('showOutModal', false)
            ->assertSet('expectedCash', 450000); // 500k - 50k

        // Assert database records
        $this->assertDatabaseHas('cash_transactions', [
            'cash_session_id' => $session->id,
            'type' => 'out',
            'amount' => 50000,
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'notes' => 'Uang galon aqua',
            'status' => 'posted',
        ]);
    }

    /** @test */
    public function it_prevents_cash_outflow_exceeding_drawer_balance()
    {
        // Create active session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 100000,
            'expected_cash' => 100000,
            'actual_cash' => 100000,
            'difference' => 0,
        ]);

        Livewire::test('App\Livewire\POSDailyCash')
            ->call('openOutModal')
            ->set('outAmount', 150000) // Exceeds 100k
            ->set('outDestinationAccountId', $this->expenseAccount->id)
            ->set('outNotes', 'Belanja ATK besar')
            ->call('saveOutflow')
            ->assertSet('showOutModal', true); // Still showing because of error
    }

    /** @test */
    public function it_can_filter_daily_cash_by_search_type_account_date_range_session_and_branch()
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Kedua',
            'address' => 'Jl. Dua',
            'phone' => '456',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($otherBranch);

        $closedSession = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'closed',
            'opened_at' => now()->subDay(),
            'closed_at' => now()->subDay()->addHours(8),
            'opening_cash' => 300000,
            'expected_cash' => 300000,
            'actual_cash' => 300000,
            'difference' => 0,
        ]);

        $activeSession = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
        ]);

        // Transaksi kemarin (sesi lama)
        CashTransaction::create([
            'transaction_no' => 'CSH-OLD-001',
            'branch_id' => $this->branch->id,
            'cash_session_id' => $closedSession->id,
            'type' => 'out',
            'transaction_date' => now()->subDay()->toDateString(),
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'amount' => 75000,
            'notes' => 'Beli kabel gitar kemarin',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // Transaksi hari ini (sesi aktif) - Kas Masuk
        CashTransaction::create([
            'transaction_no' => 'CSH-TODAY-IN',
            'branch_id' => $this->branch->id,
            'cash_session_id' => $activeSession->id,
            'type' => 'in',
            'transaction_date' => now()->toDateString(),
            'source_account_id' => $this->capitalAccount->id,
            'destination_account_id' => $this->cashAccount->id,
            'amount' => 200000,
            'notes' => 'Tambah modal receh',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // Transaksi hari ini (sesi aktif) - Kas Keluar
        CashTransaction::create([
            'transaction_no' => 'CSH-TODAY-OUT',
            'branch_id' => $this->branch->id,
            'cash_session_id' => $activeSession->id,
            'type' => 'out',
            'transaction_date' => now()->toDateString(),
            'source_account_id' => $this->cashAccount->id,
            'destination_account_id' => $this->expenseAccount->id,
            'amount' => 40000,
            'notes' => 'Beli galon air',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // Transaksi cabang kedua hari ini
        CashTransaction::create([
            'transaction_no' => 'CSH-BR2-001',
            'branch_id' => $otherBranch->id,
            'cash_session_id' => null,
            'type' => 'in',
            'transaction_date' => now()->toDateString(),
            'source_account_id' => $this->capitalAccount->id,
            'destination_account_id' => $this->cashAccount->id,
            'amount' => 90000,
            'notes' => 'Kas cabang dua',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // 1. Default mount: sesi aktif -> tampil transaksi sesi aktif saja
        $component = Livewire::test('App\Livewire\POSDailyCash')
            ->assertSet('selectedSessionId', 'active')
            ->assertSee('CSH-TODAY-IN')
            ->assertSee('CSH-TODAY-OUT')
            ->assertDontSee('CSH-OLD-001')
            ->assertDontSee('CSH-BR2-001');

        // 2. Filter tipe Kas Keluar
        $component
            ->set('selectedType', 'out')
            ->assertSee('CSH-TODAY-OUT')
            ->assertDontSee('CSH-TODAY-IN');

        // 3. Filter akun kontra (MODAL DISETOR) + reset tipe ke all
        $component
            ->set('selectedType', 'all')
            ->set('selectedAccountId', (string) $this->capitalAccount->id)
            ->assertSee('CSH-TODAY-IN')
            ->assertDontSee('CSH-TODAY-OUT');

        // 4. Filter pencarian teks
        $component
            ->set('selectedAccountId', '')
            ->set('search', 'galon')
            ->assertSee('CSH-TODAY-OUT')
            ->assertDontSee('CSH-TODAY-IN');

        // 5. Filter rentang tanggal kemarin -> otomatis pindah ke Semua Sesi dan menampilkan transaksi kemarin
        $component
            ->set('search', '')
            ->set('fromDate', now()->subDay()->toDateString())
            ->assertSet('selectedSessionId', 'all')
            ->assertSee('CSH-OLD-001')
            ->assertSee('CSH-TODAY-IN')
            ->assertSee('CSH-TODAY-OUT');

        // 6. Filter sesi spesifik (sesi lama)
        $component
            ->set('selectedSessionId', (string) $closedSession->id)
            ->assertSee('CSH-OLD-001')
            ->assertDontSee('CSH-TODAY-IN');

        // 7. Filter cabang kedua (walau tidak ada sesi aktif, tabel tetap muncul)
        $component
            ->set('selectedBranchId', $otherBranch->id)
            ->assertSet('selectedSessionId', 'all')
            ->assertSee('CSH-BR2-001')
            ->assertDontSee('CSH-OLD-001');

        // 8. Reset filter kembali ke default
        $component
            ->set('selectedBranchId', $this->branch->id)
            ->call('resetFilters')
            ->assertSet('selectedSessionId', 'active')
            ->assertSet('selectedType', 'all')
            ->assertSet('search', '')
            ->assertSee('CSH-TODAY-IN')
            ->assertDontSee('CSH-OLD-001');
    }

    /** @test */
    public function it_can_record_cash_transfer_setor_kas_with_default_accounts_and_reduces_expected_cash_and_z_report()
    {
        $pettyCashAccount = Account::firstOrCreate(
            ['code' => '111101002'],
            ['name' => 'KAS Kecil', 'classification' => 'Asset', 'is_header' => false, 'is_active' => true]
        );

        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 349100,
            'expected_cash' => 349100,
        ]);

        // Simulasikan saldo laci 7.653.100 (modal 349.100 + cash in 7.304.000)
        CashTransaction::create([
            'transaction_no' => 'CSH-SIM-IN',
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'type' => 'in',
            'transaction_date' => now()->toDateString(),
            'source_account_id' => $this->capitalAccount->id,
            'destination_account_id' => $this->cashAccount->id,
            'amount' => 7304000,
            'notes' => 'Simulasi tambahan tunai',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // Buka modal Setor Kas: default sumber = 111101002 (KAS Kecil / Laci), tujuan = 111101001 (KAS Besar / Owner)
        Livewire::test('App\Livewire\POSDailyCash')
            ->call('openTransferModal')
            ->assertSet('showTransferModal', true)
            ->assertSet('transferSourceAccountId', $pettyCashAccount->id)
            ->assertSet('transferDestinationAccountId', $this->cashAccount->id)
            ->set('transferAmount', 7300000)
            ->set('transferNotes', 'Setor dari Kas Kecil ke Kas Besar sebelum tutup kasir')
            ->call('saveTransfer')
            ->assertSet('showTransferModal', false)
            ->assertSet('expectedCash', 353100);

        $this->assertDatabaseHas('cash_transactions', [
            'cash_session_id' => $session->id,
            'type' => 'transfer',
            'source_account_id' => $pettyCashAccount->id,
            'destination_account_id' => $this->cashAccount->id,
            'amount' => 7300000,
            'status' => 'posted',
        ]);

        // Jurnal otomatis: Debit KAS Besar (111101001), Credit KAS Kecil (111101002)
        $this->assertDatabaseHas('journal_items', [
            'account_id' => $this->cashAccount->id,
            'debit' => 7300000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_items', [
            'account_id' => $pettyCashAccount->id,
            'debit' => 0,
            'credit' => 7300000,
        ]);

        // Tutup sesi dengan uang fisik 353.100 -> selisih harus 0 (Pas)
        $closedSession = app(\App\Actions\CashSession\CloseCashSession::class)->execute($session->fresh(), [
            'actual_cash' => 353100,
        ]);

        $this->assertSame(353100, (int) $closedSession->expected_cash);
        $this->assertSame(0, (int) $closedSession->difference);

        // Z-Report menampilkan baris Setor Kas
        session(['active_branch_id' => $this->branch->id]);
        $this->get(route('pos.z-report', $closedSession->id))
            ->assertOk()
            ->assertSee('Setor Kas:')
            ->assertSee('-Rp 7.300.000')
            ->assertSee('Rp 353.100');
    }

    /** @test */
    public function it_loads_all_active_revenue_equity_accounts_for_cash_in_and_expense_accounts_for_cash_out()
    {
        $otherRevenue = Account::firstOrCreate(
            ['code' => '411901001'],
            ['name' => 'PENDAPATAN LAIN-LAIN', 'classification' => 'Revenue', 'is_header' => false, 'is_active' => true]
        );
        $electricityExpense = Account::firstOrCreate(
            ['code' => '611101005'],
            ['name' => 'BEBAN LISTRIK & AIR', 'classification' => 'Expense', 'is_header' => false, 'is_active' => true]
        );
        $headerExpense = Account::firstOrCreate(
            ['code' => '611100000'],
            ['name' => 'HEADER BEBAN', 'classification' => 'Expense', 'is_header' => true, 'is_active' => true]
        );
        $sixRevenue = Account::firstOrCreate(
            ['code' => '621101002'],
            ['name' => 'PENDAPATAN BUNGA BANK', 'classification' => 'revenue', 'is_header' => false, 'is_active' => true]
        );
        $bankBca = Account::firstOrCreate(
            ['code' => '111201001'],
            ['name' => 'BANK BCA', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]
        );
        $advanceAcc = Account::firstOrCreate(
            ['code' => '111201006'],
            ['name' => 'Uang Muka Pembelian', 'classification' => 'asset', 'is_header' => false, 'is_active' => true]
        );

        $component = Livewire::test('App\Livewire\POSDailyCash');

        $inCodes = collect($component->get('inflowSources'))->pluck('code')->all();
        $outCodes = collect($component->get('outflowDestinations'))->pluck('code')->all();
        $transferCodes = collect($component->get('transferAccounts'))->pluck('code')->all();

        $this->assertContains($otherRevenue->code, $inCodes);
        $this->assertContains($this->capitalAccount->code, $inCodes);
        $this->assertContains($electricityExpense->code, $outCodes);
        $this->assertContains($this->expenseAccount->code, $outCodes);
        $this->assertNotContains($headerExpense->code, $outCodes);
        $this->assertNotContains($sixRevenue->code, $outCodes);
        $this->assertContains($this->cashAccount->code, $transferCodes);
        $this->assertContains($bankBca->code, $transferCodes);
        $this->assertNotContains($advanceAcc->code, $transferCodes);
    }
}
