<?php

namespace Tests\Feature\Reports;

use App\Actions\Procurement\GeneratePurchaseReport;
use App\Filament\Pages\Reports\PurchaseReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseReportTest extends TestCase
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
            'name'      => 'Admin Purchasing',
            'username'  => 'adminpurchasing',
            'email'     => 'procurement@diegomusic.com',
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
    public function it_calculates_purchase_totals_and_items_correctly()
    {
        $product = Product::create(['name' => 'Gitar Akustik Yamaha F310', 'type' => 'physical', 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'YMH-F310', 'name' => 'Natural']);
        $unit    = Unit::create(['code' => 'PCS', 'name' => 'Pcs']);

        // Create Purchase Transaction
        $pt = PurchaseTransaction::create([
            'transaction_no'   => 'PT-20260727-001',
            'invoice_number'   => 'INV-SUP-101',
            'transaction_date' => now()->toDateString(),
            'supplier_id'      => $this->supplier->id,
            'branch_id'        => $this->branch->id,
            'purchase_type'    => 'Kredit',
            'subtotal'         => 10000000,
            'discount'         => 0,
            'tax_amount'       => 1100000,
            'shipping_cost'    => 200000,
            'grand_total'      => 11300000,
            'status'           => 'posted',
            'created_by'       => $this->user->id,
        ]);

        PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id'      => $variant->id,
            'unit_id'                 => $unit->id,
            'qty_po'                  => 5,
            'qty_received'            => 5,
            'price'                   => 2000000,
            'subtotal'                => 10000000,
        ]);

        $action = new GeneratePurchaseReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id, $this->supplier->id);

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(5, $report['total_qty']);
        $this->assertEquals(10000000, $report['total_subtotal']);
        $this->assertEquals(1100000, $report['total_tax']);
        $this->assertEquals(200000, $report['total_shipping']);
        $this->assertEquals(11300000, $report['total_grand_total']);
    }

    /** @test */
    public function it_can_render_purchase_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(PurchaseReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Pembelian');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(PurchaseReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
