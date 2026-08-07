<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Actions\Purchases\CreatePurchaseReturn;
use App\Actions\Procurement\GeneratePurchaseReturnReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->supplier = Supplier::create(['name' => 'PT Supplier Laporan']);
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
    }
}
