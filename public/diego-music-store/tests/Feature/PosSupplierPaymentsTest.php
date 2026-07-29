<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\Account;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosSupplierPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Customer $customer;
    protected Account $cashAccount;
    protected Sale $piutangSale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test POS',
            'address' => 'Jl. Test POS',
            'phone' => '021-12345',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);

        $this->customer = Customer::create([
            'name' => 'Test Pelanggan Piutang',
            'phone' => '08123456789',
            'address' => 'Customer Address',
            'outstanding_debt' => 5000000,
        ]);

        $this->cashAccount = Account::create([
            'code' => '1-1101',
            'name' => 'Kas POS Test',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);

        $this->piutangSale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-2026-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 5000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 5000000,
            'payment_method' => 'Piutang',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_render_pos_piutang_settlement_page()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->assertStatus(200)
            ->assertSee('Pelunasan Piutang');
    }

    /** @test */
    public function it_loads_unpaid_sales_when_customer_is_selected()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->set('customer_id', $this->customer->id)
            ->assertSet('items.0.sale_id', $this->piutangSale->id)
            ->assertSet('items.0.amount_due', 5000000);
    }

    /** @test */
    public function it_can_process_piutang_settlement()
    {
        Livewire::test('App\Livewire\PosSupplierPayments')
            ->call('openCreate')
            ->set('customer_id', $this->customer->id)
            ->set('account_id', $this->cashAccount->id)
            ->set('payment_method', 'Tunai')
            ->set('items.0.is_selected', true)
            ->set('items.0.amount_paid', 5000000)
            ->call('save', 'posted')
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('sales', [
            'id' => $this->piutangSale->id,
            'status' => 'completed',
        ]);

        $this->assertEquals(0, $this->customer->fresh()->outstanding_debt);
    }
}
