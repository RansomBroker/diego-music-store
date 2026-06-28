<?php

namespace Tests\Feature\Actions;

use App\Actions\DeliveryOrder\CreateDeliveryOrder;
use App\Actions\DeliveryOrder\UpdateDeliveryOrder;
use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOrderActionsTest extends TestCase
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

    public function test_it_can_create_and_update_delivery_order_and_calculate_hpp(): void
    {
        // 1. Setup Purchase Order (must be approved status for DO to refer)
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TEST-100',
            'order_date' => '2026-06-28',
            'status' => 'approved',
            'total_amount' => 10000000,
        ]);

        $po->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 10,
            'price' => 1000000,
        ]);

        // Pre-fill initial branch stock and HPP
        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
            'hpp' => 800000,
        ]);

        // 2. Test Create DO with draft status (stock shouldn't change)
        $createData = [
            'purchase_order_id' => $po->id,
            'branch_id' => $this->branch->id,
            'received_date' => '2026-06-28',
            'shipping_cost' => 100000,
            'status' => 'draft',
            'notes' => 'DO notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                ]
            ]
        ];

        $do = app(CreateDeliveryOrder::class)->execute($createData);

        $this->assertInstanceOf(DeliveryOrder::class, $do);
        $this->assertEquals('draft', $do->status);
        $this->assertCount(1, $do->items);

        // Verify stock is still 5
        $stockRecord = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();
        $this->assertEquals(5, $stockRecord->stock);
        $this->assertEquals(800000, $stockRecord->hpp);

        // 3. Test Update DO status to received (stock and HPP should be updated)
        $updateData = [
            'purchase_order_id' => $po->id,
            'branch_id' => $this->branch->id,
            'received_date' => '2026-06-28',
            'shipping_cost' => 100000,
            'status' => 'received',
            'notes' => 'DO received notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                ]
            ]
        ];

        $updatedDo = app(UpdateDeliveryOrder::class)->execute($do, $updateData);

        $this->assertEquals('received', $updatedDo->status);

        // Verify stock is now 15
        $stockRecord->refresh();
        $this->assertEquals(15, $stockRecord->stock);

        // Verify HPP calculation
        // Shipping cost per unit = 100k / 10 = 10k
        // Effective unit cost = 1M + 10k = 1.01M
        // Total cost old = 5 * 800k = 4M
        // Total cost new = 10 * 1.01M = 10.1M
        // New HPP = (4M + 10.1M) / 15 = 14.1M / 15 = 940k
        $this->assertEquals(940000, $stockRecord->hpp);
    }
}
