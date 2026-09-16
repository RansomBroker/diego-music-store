<?php

namespace Tests\Feature;

use App\Actions\Procurement\GeneratePurchaseReturnReport;
use App\Actions\Purchases\CreatePurchaseReturn;
use App\Filament\Pages\Reports\PurchaseReturnReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseReturnReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Laporan', 'store_name' => 'Diego Store']);
        $this->supplier = Supplier::create(['name' => 'PT Supplier Laporan', 'phone' => '08123456789']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_generate_purchase_return_report()
    {
        $product = Product::create(['name' => 'Mixer Yamaha MG10XU', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'MIX-YMH-10',
            'price' => 3500000,
            'cost_price' => 2800000,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260803-0088',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'subtotal' => 5600000,
            'grand_total' => 5600000,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 2,
            'qty_received' => 2,
            'price' => 2800000,
            'subtotal' => 5600000,
        ]);

        $return = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Barang penyok',
            'return_type' => 'replacement',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detail->id, 'quantity' => 1]
            ]
        ]);

        $report = (new GeneratePurchaseReturnReport())->execute(
            now()->startOfMonth()->format('Y-m-d'),
            now()->format('Y-m-d'),
            $this->branch->id,
            $this->supplier->id,
            'posted',
            'summary'
        );

        $this->assertEquals(1, $report['total_transactions']);
        $this->assertEquals(2800000, $report['total_return_amount']);
        $this->assertEquals(1, $report['total_qty_returned']);
        $this->assertEquals(1, $report['breakdown_by_type']['replacement']['count']);
        $this->assertEquals(1, $report['breakdown_by_type']['replacement']['qty']);
    }

    /** @test */
    public function it_can_filter_purchase_return_report_by_return_type()
    {
        $product = Product::create(['name' => 'Gitar Fender Stratocaster', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-FND-01',
            'price' => 12000000,
            'cost_price' => 9000000,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260901-0012',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'subtotal' => 18000000,
            'grand_total' => 18000000,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 2,
            'qty_received' => 2,
            'price' => 9000000,
            'subtotal' => 18000000,
        ]);

        // Retur 1: Tukar Guling
        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Cacat pabrik - tukar guling',
            'return_type' => 'replacement',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detail->id, 'quantity' => 1]
            ]
        ]);

        // Retur 2: Penyesuaian Faktur
        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Penyesuaian faktur hutang',
            'return_type' => 'invoice_deduction',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detail->id, 'quantity' => 1]
            ]
        ]);

        // Filter: invoice_deduction
        $reportDeduction = (new GeneratePurchaseReturnReport())->execute(
            now()->startOfMonth()->format('Y-m-d'),
            now()->format('Y-m-d'),
            $this->branch->id,
            $this->supplier->id,
            'posted',
            'summary',
            null,
            'invoice_deduction'
        );

        $this->assertEquals(1, $reportDeduction['total_transactions']);
        $this->assertEquals('invoice_deduction', $reportDeduction['return_type']);
        $this->assertEquals('Penyesuaian Faktur', $reportDeduction['return_type_label']);
        $this->assertEquals(9000000, $reportDeduction['total_return_amount']);

        // Filter: replacement
        $reportReplacement = (new GeneratePurchaseReturnReport())->execute(
            now()->startOfMonth()->format('Y-m-d'),
            now()->format('Y-m-d'),
            $this->branch->id,
            $this->supplier->id,
            'posted',
            'summary',
            null,
            'replacement'
        );

        $this->assertEquals(1, $reportReplacement['total_transactions']);
        $this->assertEquals('replacement', $reportReplacement['return_type']);
        $this->assertEquals('Tukar Guling', $reportReplacement['return_type_label']);
    }

    /** @test */
    public function it_can_generate_purchase_return_report_in_by_supplier_mode()
    {
        $supplierB = Supplier::create(['name' => 'CV Sumber Audio', 'phone' => '08987654321']);

        $productA = Product::create(['name' => 'Drum Tama Superstar', 'type' => 'physical']);
        $variantA = ProductVariant::create([
            'product_id' => $productA->id,
            'sku' => 'DRM-TAMA-01',
            'price' => 15000000,
            'cost_price' => 11000000,
        ]);

        $ptA = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260901-0021',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'subtotal' => 22000000,
            'grand_total' => 22000000,
            'status' => 'posted',
        ]);

        $detailA = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $ptA->id,
            'product_variant_id' => $variantA->id,
            'qty_po' => 2,
            'qty_received' => 2,
            'price' => 11000000,
            'subtotal' => 22000000,
        ]);

        // Retur ke Supplier A
        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $ptA->id,
            'reason' => 'Kerusakan fisik drum',
            'return_type' => 'replacement',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detailA->id, 'quantity' => 2]
            ]
        ]);

        $report = (new GeneratePurchaseReturnReport())->execute(
            now()->startOfMonth()->format('Y-m-d'),
            now()->format('Y-m-d'),
            null,
            null,
            'all',
            'by_supplier'
        );

        $this->assertEquals('by_supplier', $report['mode']);
        $this->assertNotEmpty($report['supplier_history']);
        $this->assertEquals(1, $report['total_suppliers']);

        $firstSupplier = $report['supplier_history'][0];
        $this->assertEquals($this->supplier->name, $firstSupplier['supplier_name']);
        $this->assertEquals(1, $firstSupplier['total_transactions']);
        $this->assertEquals(2, $firstSupplier['total_qty_returned']);
        $this->assertEquals(22000000, $firstSupplier['total_return_amount']);

        // Check items history grouped under supplier
        $this->assertCount(1, $firstSupplier['items_history']);
        $itemHistory = $firstSupplier['items_history'][0];
        $this->assertEquals('DRM-TAMA-01', $itemHistory['sku']);
        $this->assertEquals(2, $itemHistory['total_qty']);
        $this->assertEquals(22000000, $itemHistory['total_amount']);
    }

    /** @test */
    public function it_can_render_purchase_return_report_page_and_open_detail_modal()
    {
        $product = Product::create(['name' => 'Speaker JBL EON', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SPK-JBL-01',
            'price' => 6000000,
            'cost_price' => 4500000,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260901-0099',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'subtotal' => 4500000,
            'grand_total' => 4500000,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 1,
            'qty_received' => 1,
            'price' => 4500000,
            'subtotal' => 4500000,
        ]);

        $ret = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Driver tweeter mati',
            'return_type' => 'replacement',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detail->id, 'quantity' => 1]
            ]
        ]);

        Livewire::test(PurchaseReturnReport::class)
            ->assertSuccessful()
            ->call('openReturnDetailModal', $ret->id)
            ->assertDispatched('open-modal', id: 'return-detail-modal')
            ->assertSet('selectedReturnDetail.return_no', $ret->return_no)
            ->assertSet('selectedReturnDetail.return_type_label', 'Tukar Guling (Barang Baru)')
            ->assertSet('selectedReturnDetail.supplier_name', $this->supplier->name);
    }

    /** @test */
    public function it_can_generate_purchase_return_report_in_ledger_mode()
    {
        $supplierB = Supplier::create(['name' => 'PT Vendor Musik Dua', 'phone' => '08111222333']);

        $product = Product::create(['name' => 'Kabel Jack Mogami', 'type' => 'physical']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'KBL-MOG-01',
            'price' => 250000,
            'cost_price' => 150000,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260901-0888',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $supplierB->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'subtotal' => 1500000,
            'grand_total' => 1500000,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 10,
            'qty_received' => 10,
            'price' => 150000,
            'subtotal' => 1500000,
        ]);

        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Kabel noiseless cacat solder',
            'return_type' => 'refund',
            'status' => 'posted',
            'items' => [
                ['purchase_transaction_detail_id' => $detail->id, 'quantity' => 4]
            ]
        ]);

        $report = (new GeneratePurchaseReturnReport())->execute(
            now()->startOfMonth()->format('Y-m-d'),
            now()->format('Y-m-d'),
            null,
            null,
            'all',
            'ledger'
        );

        $this->assertEquals('ledger', $report['mode']);
        $this->assertNotEmpty($report['supplier_ledgers']);
        
        $supplierLedger = collect($report['supplier_ledgers'])->firstWhere('supplier_id', $supplierB->id);
        $this->assertNotNull($supplierLedger);
        $this->assertEquals('PT Vendor Musik Dua', $supplierLedger['supplier_name']);
        $this->assertEquals(1, $supplierLedger['total_transactions']);
        $this->assertEquals(4, $supplierLedger['total_qty_returned']);
        $this->assertEquals(600000, $supplierLedger['total_amount']);
        $this->assertEquals(1, $supplierLedger['by_type']['refund']['count']);
        $this->assertEquals(600000, $supplierLedger['by_type']['refund']['amount']);
        $this->assertCount(1, $supplierLedger['entries']);
        $this->assertCount(1, $supplierLedger['entries'][0]['items']);
        $this->assertEquals('KBL-MOG-01', $supplierLedger['entries'][0]['items'][0]['sku']);
        $this->assertEquals(4, $supplierLedger['entries'][0]['items'][0]['qty']);
    }
}

