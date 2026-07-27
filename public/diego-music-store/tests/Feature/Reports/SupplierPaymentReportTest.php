<?php

namespace Tests\Feature\Reports;

use App\Actions\Procurement\GenerateSupplierPaymentReport;
use App\Filament\Pages\Reports\SupplierPaymentReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierPaymentReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;
    protected Account $account;

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
            'name'      => 'PT Korg Indonesia',
            'code'      => 'SUP-KRG-01',
            'phone'     => '021-7776666',
            'is_active' => true,
        ]);

        $this->account = Account::create([
            'code'           => '1-1100',
            'name'           => 'Bank BCA Operasional',
            'classification' => 'asset',
            'is_header'      => false,
            'is_active'      => true,
        ]);
    }

    /** @test */
    public function it_calculates_supplier_payment_totals_and_allocations_correctly()
    {
        // 1. Purchase Invoice: 15.000.000
        $pt = PurchaseTransaction::create([
            'transaction_no'   => 'PT-KRG-001',
            'invoice_number'   => 'INV-KRG-999',
            'transaction_date' => now()->startOfMonth()->toDateString(),
            'due_date'         => now()->endOfMonth()->toDateString(),
            'supplier_id'      => $this->supplier->id,
            'branch_id'        => $this->branch->id,
            'purchase_type'    => 'Kredit',
            'subtotal'         => 15000000,
            'grand_total'      => 15000000,
            'status'           => 'posted',
            'created_by'       => $this->user->id,
        ]);

        // 2. Supplier Payment: 10.000.000
        $pay = SupplierPayment::create([
            'payment_no'        => 'PAY-KRG-001',
            'payment_date'      => now()->toDateString(),
            'supplier_id'       => $this->supplier->id,
            'branch_id'         => $this->branch->id,
            'account_id'        => $this->account->id,
            'payment_method'    => 'Transfer BCA',
            'payment_reference' => 'TRF-BCA-888',
            'total_amount'      => 10000000,
            'status'            => 'posted',
            'created_by'        => $this->user->id,
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id'    => $pay->id,
            'purchase_transaction_id' => $pt->id,
            'amount_due'             => 15000000,
            'amount_paid'            => 10000000,
        ]);

        $action = new GenerateSupplierPaymentReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id, $this->supplier->id);

        $this->assertEquals(1, $report['total_payments_count']);
        $this->assertEquals(1, $report['total_invoices_paid']);
        $this->assertEquals(1, $report['total_suppliers_paid']);
        $this->assertEquals(10000000, $report['total_amount_paid']);

        $paymentRow = $report['payments'][0];
        $this->assertEquals('PAY-KRG-001', $paymentRow['payment_no']);
        $this->assertEquals(10000000, $paymentRow['total_amount']);
        $this->assertEquals(1, count($paymentRow['items']));
    }

    /** @test */
    public function it_can_render_supplier_payment_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(SupplierPaymentReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Pelunasan Hutang Supplier');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(SupplierPaymentReport::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
