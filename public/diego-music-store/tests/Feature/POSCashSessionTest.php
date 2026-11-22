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

        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
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

        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        foreach (['fo.access', 'fo.cash_session.view', 'fo.cash_session.close', 'fo.cash_session.reopen'] as $perm) {
            \Spatie\Permission\Models\Permission::findOrCreate($perm, 'web');
        }
        $this->adminRole->givePermissionTo(['fo.access', 'fo.cash_session.view', 'fo.cash_session.close', 'fo.cash_session.reopen']);
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

    /** @test */
    public function it_can_render_the_z_report_page()
    {
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

        $response = $this->get(route('pos.z-report', $session->id));

        $response->assertStatus(200);
        $response->assertSee('Z-REPORT SHIFT KASIR');
        $response->assertSee('Cetak Struk');
        $response->assertSee('Download as PDF');
        $response->assertSee(route('pos.z-report.pdf', $session->id));
    }

    /** @test */
    public function it_can_download_the_z_report_as_pdf()
    {
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

        $response = $this->get(route('pos.z-report.pdf', $session->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_displays_detailed_non_cash_payment_breakdown_on_z_report()
    {
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'closed',
            'opened_at' => now()->subHours(5),
            'closed_at' => now()->subHours(1),
            'opening_cash' => 500000,
            'expected_cash' => 600000,
            'actual_cash' => 600000,
            'difference' => 0,
        ]);

        $cashAccount = \App\Models\Account::firstOrCreate(
            ['code' => '111101001'],
            ['name' => 'KAS', 'classification' => 'Asset', 'is_active' => true]
        );
        $bankBca = \App\Models\Account::firstOrCreate(
            ['code' => '111201001'],
            ['name' => 'BANK BCA', 'classification' => 'Asset', 'is_active' => true]
        );
        $bankMandiri = \App\Models\Account::firstOrCreate(
            ['code' => '111201002'],
            ['name' => 'BANK Mandiri', 'classification' => 'Asset', 'is_active' => true]
        );

        // Sale 1: Split Cash 100k + QRIS 250k
        $splitSale = Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-ZR-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 350000,
            'grand_total' => 350000,
            'paid_amount' => 350000,
            'change_amount' => 0,
            'payment_method' => 'Tunai & QRIS',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);
        $je1 = \App\Models\JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-ZR-001',
            'date' => now()->toDateString(),
            'reference_type' => 'Sales',
            'reference_id' => $splitSale->id,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        \App\Models\JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $cashAccount->id,
            'debit' => 100000,
            'credit' => 0,
            'notes' => 'Penerimaan POS - Cash',
        ]);
        \App\Models\JournalItem::create([
            'journal_entry_id' => $je1->id,
            'account_id' => $bankBca->id,
            'debit' => 250000,
            'credit' => 0,
            'notes' => 'Penerimaan POS - QRIS (Ref: QR-123)',
        ]);

        // Sale 2: Transfer Mandiri 500k
        $transferSale = Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-ZR-0002',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 500000,
            'grand_total' => 500000,
            'paid_amount' => 500000,
            'change_amount' => 0,
            'payment_method' => 'Transfer (Mandiri)',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);
        $je2 = \App\Models\JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-ZR-002',
            'date' => now()->toDateString(),
            'reference_type' => 'Sales',
            'reference_id' => $transferSale->id,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        \App\Models\JournalItem::create([
            'journal_entry_id' => $je2->id,
            'account_id' => $bankMandiri->id,
            'debit' => 500000,
            'credit' => 0,
            'notes' => 'Penerimaan POS - Debit Card (Bank: Mandiri | Ref: EDC-999)',
        ]);

        $response = $this->get(route('pos.z-report', $session->id));

        $response->assertStatus(200);
        $response->assertSee('1 tx (Rp 100.000)');
        $response->assertSee('2 tx (Rp 750.000)');
        $response->assertSee('- Debit Card (Mandiri):');
        $response->assertSee('1 tx (Rp 500.000)');
        $response->assertSee('- QRIS:');
        $response->assertSee('1 tx (Rp 250.000)');
        $response->assertSee('2 tx (Rp 850.000)');

        $pdfHtml = view('pos.z-report-pdf', [
            'session' => $session->fresh(['branch', 'user', 'closedBy']),
            'cashSalesCount' => 1,
            'cashSalesSum' => 100000,
            'nonCashSalesCount' => 2,
            'nonCashSalesSum' => 750000,
            'nonCashBreakdown' => \App\Helpers\SaleHelper::getSalesNonCashBreakdown(collect([$splitSale, $transferSale])),
            'totalSalesCount' => 2,
            'totalSalesSum' => 850000,
        ])->render();

        $this->assertStringContainsString('- Debit Card (Mandiri):', $pdfHtml);
        $this->assertStringContainsString('- QRIS:', $pdfHtml);
    }
}
