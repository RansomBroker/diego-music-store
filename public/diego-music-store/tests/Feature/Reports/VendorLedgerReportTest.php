<?php

namespace Tests\Feature\Reports;

use App\Actions\Accounting\GenerateVendorLedgerReport;
use App\Filament\Pages\Reports\VendorLedger;
use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorLedgerReportTest extends TestCase
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
            'name'      => 'Admin Akuntansi',
            'username'  => 'adminfinance',
            'email'     => 'finance@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $this->supplier = Supplier::create([
            'name'      => 'PT Yamaha Musik Indonesia',
            'code'      => 'SUP-001',
            'phone'     => '021-5551234',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_calculates_vendor_ap_beginning_and_ending_balances_correctly()
    {
        $priorDate  = now()->subDays(10)->toDateString();
        $periodDate = now()->toDateString();

        // 1. Prior Purchase Transaction (Sets Beginning Balance = 15.000.000)
        PurchaseTransaction::create([
            'transaction_no'   => 'PUR-PRIOR-001',
            'transaction_date' => $priorDate,
            'supplier_id'      => $this->supplier->id,
            'branch_id'        => $this->branch->id,
            'grand_total'      => 15000000,
            'status'           => 'posted',
            'created_by'       => $this->user->id,
        ]);

        // 2. Period Purchase Transaction (+ 5.000.000)
        PurchaseTransaction::create([
            'transaction_no'   => 'PUR-PERIOD-001',
            'transaction_date' => $periodDate,
            'supplier_id'      => $this->supplier->id,
            'branch_id'        => $this->branch->id,
            'grand_total'      => 5000000,
            'status'           => 'posted',
            'created_by'       => $this->user->id,
        ]);

        // 3. Period Supplier Payment (- 8.000.000)
        SupplierPayment::create([
            'payment_no'   => 'PAY-PERIOD-001',
            'payment_date' => $periodDate,
            'supplier_id'   => $this->supplier->id,
            'branch_id'     => $this->branch->id,
            'total_amount' => 8000000,
            'status'       => 'posted',
            'created_by'   => $this->user->id,
        ]);

        $action = new GenerateVendorLedgerReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->supplier->id, $this->branch->id);

        $vendorLedger = $report['vendors'][0];

        // Verification:
        // Beginning AP Balance = 15M.
        // Total Additions = 5M. Total Payments = 8M.
        // Ending AP Balance = 12M (15M + 5M - 8M).

        $this->assertEquals(15000000, $vendorLedger['beginning_balance']);
        $this->assertEquals(5000000, $vendorLedger['total_additions']);
        $this->assertEquals(8000000, $vendorLedger['total_payments']);
        $this->assertEquals(12000000, $vendorLedger['ending_balance']);
    }

    /** @test */
    public function it_can_render_vendor_ledger_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(VendorLedger::class)
            ->assertStatus(200)
            ->assertSee('Laporan Buku Vendor (Kartu Hutang Supplier)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(VendorLedger::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
