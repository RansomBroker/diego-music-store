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

        // Seed basic accounts
        $this->cashAccount = Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        $this->capitalAccount = Account::firstOrCreate(['code' => '3-1000'], ['name' => 'Modal Pemilik', 'classification' => 'Equity', 'is_active' => true]);
        $this->expenseAccount = Account::firstOrCreate(['code' => '6-1000'], ['name' => 'Beban Operasional & Gaji', 'classification' => 'Expense', 'is_active' => true]);

        $this->user = User::factory()->create();
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
}
