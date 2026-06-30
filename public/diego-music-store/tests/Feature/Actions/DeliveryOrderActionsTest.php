<?php

namespace Tests\Feature\Actions;

use App\Actions\DeliveryOrder\CreateDeliveryOrder;
use App\Actions\DeliveryOrder\UpdateDeliveryOrder;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOrderActionsTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private Branch $branch;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'John Doe Customer',
            'phone' => '08123456780',
            'address' => 'Denpasar',
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

    public function test_it_can_create_and_update_delivery_order_and_reduce_stock(): void
    {
        // Pre-fill initial branch stock
        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 15,
            'hpp' => 800000,
        ]);

        // 1. Test Create DO with draft status (stock shouldn't change)
        $createData = [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'shipping_date' => '2026-06-28',
            'shipping_cost' => 100000,
            'status' => 'draft',
            'notes' => 'DO notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                ]
            ]
        ];

        $do = app(CreateDeliveryOrder::class)->execute($createData);

        $this->assertInstanceOf(DeliveryOrder::class, $do);
        $this->assertEquals('draft', $do->status);
        $this->assertCount(1, $do->items);

        // Verify stock is still 15
        $stockRecord = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();
        $this->assertEquals(15, $stockRecord->stock);

        // 2. Test Update DO status to shipped (stock should decrease)
        $updateData = [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'shipping_date' => '2026-06-28',
            'shipping_cost' => 100000,
            'status' => 'shipped',
            'notes' => 'DO shipped notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                ]
            ]
        ];

        $updatedDo = app(UpdateDeliveryOrder::class)->execute($do, $updateData);

        $this->assertEquals('shipped', $updatedDo->status);

        // Verify stock decreases to 5
        $stockRecord->refresh();
        $this->assertEquals(5, $stockRecord->stock);

        // Verify StockMovement is logged
        $movement = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'out',
            'reference_type' => 'DO',
            'reference_id' => $updatedDo->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->quantity);
    }
}
