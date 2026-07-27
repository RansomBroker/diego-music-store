<?php

namespace Tests\Feature\Reports;

use App\Actions\Procurement\GenerateAccountsPayableReport;
use App\Filament\Pages\Reports\AccountsPayableReport;
use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountsPayableReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'        => 'Cabang Utama',
            'store_name'  => 'Diego Music Store Main',
            'address'     => 'Jl. Musik No. 123',
            'phone'       => '081234567890',
            'is_active'   => true,
        ]);

        $this->user = User::factory()->create([
            'name'      => 'Admin Finance',
            'username'  => 'adminfinance',
            'email'     => 'finance@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $this->supplier = Supplier::create([
            'name'      => 'PT Roland Indonesia',
            'code'      => 'SUP-RLD-01',
            'phone'     => '021-9998888',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_calculates_accounts_payable_aging_and_unpaid_balances_correctly()
    {
        // 1. Credit Purchase Transaction: Total 10.000.000 (Due 15 days ago => Overdue 1-30)
        $pt = PurchaseTransaction::create([
            'transaction_no'   => 'PT-CREDIT-001',
            'invoice_number'   => 'INV-RLD-555',
            'transaction_date' => now()->subDays(45)->toDateString(),
            'due_date'         => now()->subDays(15)->toDateString(),
            'supplier_id'      => $this->supplier->id,
            'branch_id'        => $this->branch->id,
            'purchase_type'    => 'Kredit',
            'subtotal'         => 10000000,
            'grand_total'      => 10000000,
            'status'           => 'posted',
            'created_by'       => $this->user->id,
        ]);

        $account = \App\Models\Account::create(['code' => '1101', 'name' => 'Kas Utama', 'classification' => 'Asset', 'is_header' => false, 'is_active' => true]);

        // 2. Partial Payment: 4.000.000 (Remaining Unpaid = 6.000.000)
        $pay = SupplierPayment::create([
            'payment_no'     => 'PAY-SUP-001',
            'payment_date'   => now()->subDays(5)->toDateString(),
            'supplier_id'     => $this->supplier->id,
            'branch_id'       => $this->branch->id,
            'account_id'      => $account->id,
            'payment_method'  => 'Transfer',
            'total_amount'   => 4000000,
            'status'         => 'posted',
            'created_by'     => $this->user->id,
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id'    => $pay->id,
            'purchase_transaction_id' => $pt->id,
            'amount_due'             => 10000000,
            'amount_paid'            => 4000000,
        ]);

        $action = new GenerateAccountsPayableReport();
        $report = $action->execute(now()->toDateString(), $this->branch->id, $this->supplier->id);

        $this->assertEquals(1, $report['total_invoices']);
        $this->assertEquals(10000000, $report['total_grand_total']);
        $this->assertEquals(4000000, $report['total_paid']);
        $this->assertEquals(6000000, $report['total_unpaid']);

        // Check Overdue & Aging bucket (15 days overdue => aging_1_30)
        $this->assertEquals(6000000, $report['total_overdue']);
        $this->assertEquals(6000000, $report['aging_buckets']['aging_1_30']);
    }

    /** @test */
    public function it_can_render_accounts_payable_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(AccountsPayableReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Hutang Usaha (Accounts Payable)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(AccountsPayableReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
