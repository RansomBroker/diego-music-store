<?php

namespace Tests\Feature\Models;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_purchase_order_and_items(): void
    {
        $supplier = Supplier::create([
            'name' => 'Yamaha Music Indonesia',
            'contact_person' => 'Budi',
            'phone' => '08123456780',
            'email' => 'yamaha@example.com',
            'address' => 'Jakarta',
            'outstanding_debt' => 0,
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-2026-0001',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'total_amount' => 15000000,
            'notes' => 'Pemesanan Gitar Yamaha',
        ]);

        $product = Product::create([
            'name' => 'Yamaha Guitar C40',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-C40-BLK',
            'barcode' => '888123456',
            'name' => 'Hitam',
            'price' => 1500000,
            'cost_price' => 1200000,
            'hpp' => 1200000,
            'is_active' => true,
        ]);

        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'price' => 1200000,
        ]);

        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals('PO-2026-0001', $po->po_number);
        $this->assertEquals('draft', $po->status);
        $this->assertCount(1, $po->items);
        $this->assertEquals(1200000, $po->items->first()->price);
        $this->assertEquals($variant->id, $po->items->first()->product_variant_id);
    }

    public function test_it_can_create_delivery_order_and_items(): void
    {
        $supplier = Supplier::create([
            'name' => 'Yamaha Music Indonesia',
            'phone' => '08123456780',
            'address' => 'Jakarta',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-2026-0001',
            'order_date' => '2026-06-28',
            'status' => 'approved',
            'total_amount' => 15000000,
        ]);

        $branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $do = DeliveryOrder::create([
            'purchase_order_id' => $po->id,
            'branch_id' => $branch->id,
            'do_number' => 'DO-YMH-12345',
            'received_date' => '2026-06-28',
            'status' => 'draft',
            'shipping_cost' => 150000,
            'notes' => 'Barang mulus',
        ]);

        $product = Product::create([
            'name' => 'Yamaha Guitar C40',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-C40-BLK',
            'barcode' => '888123456',
            'name' => 'Hitam',
            'price' => 1500000,
            'cost_price' => 1200000,
            'hpp' => 1200000,
            'is_active' => true,
        ]);

        $item = DeliveryOrderItem::create([
            'delivery_order_id' => $do->id,
            'product_variant_id' => $variant->id,
            'quantity_ordered' => 10,
            'quantity_received' => 10,
        ]);

        $this->assertInstanceOf(DeliveryOrder::class, $do);
        $this->assertEquals('DO-YMH-12345', $do->do_number);
        $this->assertEquals(150000, $do->shipping_cost);
        $this->assertCount(1, $do->items);
        $this->assertEquals(10, $do->items->first()->quantity_received);
    }

    public function test_product_branch_stock_can_have_hpp(): void
    {
        $branch = Branch::create([
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

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-C40-BLK',
            'barcode' => '888123456',
            'name' => 'Hitam',
            'price' => 1500000,
            'cost_price' => 1200000,
            'hpp' => 1200000,
            'is_active' => true,
        ]);

        $stock = ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
            'stock' => 5,
            'hpp' => 1150000,
        ]);

        $this->assertInstanceOf(ProductBranchStock::class, $stock);
        $this->assertEquals(5, $stock->stock);
        $this->assertEquals(1150000, $stock->hpp);
    }
}
