<?php

namespace Tests\Feature\Actions;

use App\Actions\Procurement\CreatePurchaseOrder;
use App\Actions\Procurement\UpdatePurchaseOrder;
use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\UpdatePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementActionsTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;
    private Branch $branch;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create([
            'name' => 'Yamaha Supplier',
            'phone' => '08123456780',
            'address' => 'Jakarta',
            'outstanding_debt' => 0,
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Yamaha Guitar C40',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-C40-BLK',
            'barcode' => '888123456',
            'name' => 'Hitam',
            'price' => 1500000,
            'cost_price' => 1000000,
            'hpp' => 1000000,
            'is_active' => true,
        ]);
    }

    public function test_it_can_create_and_update_purchase_order_via_actions(): void
    {
        // 1. Test Create
        $createData = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TEST-001',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'notes' => 'Some notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'price' => 1000000,
                ]
            ]
        ];

        $po = app(CreatePurchaseOrder::class)->execute($createData);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals(10000000, $po->total_amount); // 10 * 1,000,000
        $this->assertCount(1, $po->items);

        // 2. Test Update
        $updateData = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TEST-001-REV',
            'order_date' => '2026-06-28',
            'status' => 'approved',
            'notes' => 'Updated notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 15,
                    'price' => 900000,
                ]
            ]
        ];

        $updatedPo = app(UpdatePurchaseOrder::class)->execute($po, $updateData);
        $updatedPo->refresh();

        $this->assertEquals('PO-TEST-001-REV', $updatedPo->po_number);
        $this->assertEquals(13500000, $updatedPo->total_amount); // 15 * 900,000
        $this->assertEquals('approved', $updatedPo->status);
        $this->assertCount(1, $updatedPo->items);
        $this->assertEquals(15, $updatedPo->items->first()->quantity);
    }

    public function test_it_calculates_weighted_average_hpp_when_transaction_is_posted(): void
    {
        // Setup PO approved
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TEST-002',
            'order_date' => '2026-06-28',
            'status' => 'approved',
            'total_amount' => 10000000,
        ]);

        $po->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 10,
            'price' => 1000000, // buying price is 1M
        ]);

        // Pre-fill initial branch stock and HPP
        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
            'hpp' => 800000, // 5 units at HPP of 800k
        ]);

        // Create transaction (status draft)
        $txData = [
            'po_id' => $po->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'purchase_type' => 'Tunai',
            'transaction_date' => '2026-06-28',
            'shipping_cost' => 100000, // total shipping cost is 100k
            'status' => 'draft',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_po' => 10,
                    'qty_received' => 10, // received 10 units
                    'price' => 1000000,
                    'discount' => 0,
                    'tax_rate' => 0,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($txData);

        $this->assertInstanceOf(PurchaseTransaction::class, $pt);
        $this->assertEquals('draft', $pt->status);

        // Stock and HPP should not change yet
        $stockRecord = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();
        $this->assertEquals(5, $stockRecord->stock);
        $this->assertEquals(800000, $stockRecord->hpp);

        // Post transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        $this->assertEquals('posted', $pt->fresh()->status);

        // Verify stock incremented
        $stockRecord->refresh();
        $this->assertEquals(15, $stockRecord->stock); // 5 old + 10 received = 15

        // Verify HPP calculation
        // Shipping cost per unit = 100k / 10 = 10k
        // Effective buying cost = 1M + 10k = 1.01M
        // Total cost old = 5 * 800k = 4M
        // Total cost new = 10 * 1.01M = 10.1M
        // New HPP = (4M + 10.1M) / 15 = 14.1M / 15 = 940k
        $this->assertEquals(940000, $stockRecord->hpp);

        // Verify StockMovement logged
        $movement = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'reference_type' => 'Purchase',
            'reference_id' => $pt->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->quantity);
    }
}
