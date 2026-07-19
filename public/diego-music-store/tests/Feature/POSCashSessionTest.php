<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\CashSession;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class POSCashSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);

        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_can_render_the_pos_cash_session_page()
    {
        $response = $this->get(route('pos.session'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Kasir');
    }

    /** @test */
    public function it_can_show_transactions_for_a_cash_session()
    {
        // 1. Create a closed cash session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'closed',
            'opened_at' => now()->subHours(5),
            'closed_at' => now()->subHours(1),
            'opening_cash' => 500000,
            'expected_cash' => 750000,
            'actual_cash' => 750000,
            'difference' => 0,
        ]);

        // 2. Create sales for this session
        $sale1 = Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-0001',
            'invoice_date' => now(),
            'sale_category' => 'Store',
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 150000,
            'created_by' => $this->user->id,
        ]);

        $sale2 = Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-0002',
            'invoice_date' => now(),
            'sale_category' => 'Store',
            'payment_method' => 'transfer_bca',
            'status' => 'completed',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'created_by' => $this->user->id,
        ]);

        // 3. Test Livewire component
        Livewire::test('App\Livewire\POSCashSession')
            ->call('showTransactions', $session->id)
            ->assertSet('showTransactionsModal', true)
            ->assertSet('selectedSessionId', $session->id)
            ->assertSet('selectedSessionSummary', [
                'cash_total' => 150000,
                'non_cash_total' => 100000,
                'total_sales' => 250000,
                'transaction_count' => 2,
            ])
            ->assertSee('INV-0001')
            ->assertSee('INV-0002')
            ->assertSee('Tunai')
            ->assertSee('Transfer_bca');
    }

    /** @test */
    public function it_can_request_and_authorize_reopen_session()
    {
        // 1. Create a closed cash session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'closed',
            'opened_at' => now()->subHours(5),
            'closed_at' => now()->subHours(1),
            'opening_cash' => 500000,
            'expected_cash' => 750000,
            'actual_cash' => 750000,
            'difference' => 0,
        ]);

        // Create a supervisor user
        $supervisor = User::factory()->create([
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password123'),
        ]);
        $supervisor->assignRole($this->adminRole);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Request reopen through livewire component
        Livewire::test('App\Livewire\POSCashSession')
            ->call('requestReopenSession', $session->id)
            ->assertSet('showSupervisorModal', true)
            ->assertSet('supervisorAction', 'reopen')
            ->assertSet('sessionToReopenId', $session->id)
            ->set('supervisorEmail', 'supervisor@example.com')
            ->set('supervisorPassword', 'password123')
            ->call('authorizeAndClose') // Method name remains authorizeAndClose but handles reopen action
            ->assertSet('showSupervisorModal', false);

        // 3. Assert database state
        $session->refresh();
        $this->assertEquals('open', $session->status);
        $this->assertNull($session->closed_at);
        $this->assertNull($session->actual_cash);
        $this->assertNull($session->difference);
    }
}
