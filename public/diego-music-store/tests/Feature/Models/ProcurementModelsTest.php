<?php

namespace Tests\Feature\Models;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
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

    public function test_it_can_create_purchase_transaction_and_details(): void
    {
        $supplier = Supplier::create([
            'name' => 'Yamaha Music Indonesia',
            'phone' => '08123456780',
            'address' => 'Jakarta',
        ]);

        $branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'address' => 'Denpasar',
            'phone' => '0361-12345',
            'is_active' => true,
        ]);

        $pt = PurchaseTransaction::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->id,
            'transaction_no' => 'PT-20260628-0001',
            'transaction_date' => '2026-06-28',
            'purchase_type' => 'Kredit',
            'status' => 'draft',
            'subtotal' => 10000000,
            'grand_total' => 10000000,
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

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_received' => 5,
            'price' => 1200000,
            'subtotal' => 6000000,
        ]);

        $this->assertInstanceOf(PurchaseTransaction::class, $pt);
        $this->assertEquals('PT-20260628-0001', $pt->transaction_no);
        $this->assertCount(1, $pt->details);
        $this->assertEquals(5, $pt->details->first()->qty_received);
    }
}
