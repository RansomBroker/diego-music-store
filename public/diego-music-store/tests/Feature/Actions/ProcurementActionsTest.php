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
use App\Models\Unit;
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

    public function test_it_handles_different_shipping_borne_by_options_and_calculates_hpp_proportionately(): void
    {
        // Setup PO approved
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-SHIPPING-TEST',
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
            'stock' => 0,
            'hpp' => 0,
        ]);

        $txData = [
            'po_id' => $po->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'purchase_type' => 'Kredit',
            'transaction_date' => '2026-06-28',
            'shipping_cost' => 100000,
            'shipping_borne_by' => 'third_party',
            'shipping_carrier_name' => 'JNE',
            'status' => 'draft',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_po' => 10,
                    'qty_received' => 10,
                    'price' => 1000000,
                    'discount' => 0,
                    'tax_rate' => 0,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($txData);

        // Under third_party, grand_total should NOT include shipping_cost
        $this->assertEquals(10000000, $pt->grand_total);
        $this->assertEquals('third_party', $pt->shipping_borne_by);
        $this->assertEquals('JNE', $pt->shipping_carrier_name);

        // Post transaction to verify journal entry and HPP
        app(PostPurchaseTransaction::class)->execute($pt);

        $stockRecord = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();

        // New HPP should still include shipping cost (capitalized!)
        // 10 units * 1M + 100k shipping = 10.1M total cost. HPP = 1.01M
        $this->assertEquals(1010000, $stockRecord->hpp);

        // Verify Journal Entry created
        $journal = \App\Models\JournalEntry::where('reference_type', 'Purchase')
            ->where('reference_id', $pt->id)
            ->first();

        $this->assertNotNull($journal);

        // Credit to Hutang Dagang should be grand_total (10,000,000)
        $payableItem = $journal->items()->where('account_id', \App\Models\Account::where('code', '2-1000')->first()->id)->first();
        $this->assertEquals(10000000, $payableItem->credit);

        // Credit to Hutang Biaya Kirim Belum Ditagih (2-1500) should be 100,000
        $accruedShippingItem = $journal->items()->where('account_id', \App\Models\Account::where('code', '2-1500')->first()->id)->first();
        $this->assertNotNull($accruedShippingItem);
        $this->assertEquals(100000, $accruedShippingItem->credit);
    }

    public function test_it_handles_multi_uom_conversions_properly_when_transaction_is_posted(): void
    {
        // 1. Create base unit (Pcs)
        $pcs = Unit::create([
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_active' => true,
        ]);

        // 2. Create conversion unit (Karton = 12 Pcs)
        $karton = Unit::create([
            'name' => 'Karton',
            'code' => 'karton',
            'base_unit_id' => $pcs->id,
            'conversion_factor' => 12,
            'is_active' => true,
        ]);

        // Assign pcs as unit for the product
        $this->variant->product->update(['unit_id' => $pcs->id]);

        // Setup PO approved
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-UOM-TEST',
            'order_date' => '2026-06-28',
            'status' => 'approved',
            'total_amount' => 2400000,
        ]);

        $po->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 2, // 2 Karton
            'unit_id' => $karton->id,
            'price' => 1200000, // Rp 1.200.000 per Karton
        ]);

        // Initial branch stock: 0 stock, 0 HPP
        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 0,
            'hpp' => 0,
        ]);

        // Create transaction draft receiving 2 Karton
        $txData = [
            'po_id' => $po->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'purchase_type' => 'Tunai',
            'transaction_date' => '2026-06-28',
            'shipping_cost' => 0,
            'status' => 'draft',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_po' => 2,
                    'qty_received' => 2,
                    'unit_id' => $karton->id,
                    'price' => 1200000,
                    'discount' => 0,
                    'tax_rate' => 0,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($txData);

        // Post transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        // Verify stock is incremented by 24 (2 Karton * 12 conversion_factor)
        $stockRecord = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();
        $this->assertEquals(24, $stockRecord->stock);

        // Verify HPP per base unit (pcs)
        // Subtotal = 2 * 1.2M = 2.4M
        // Total base qty = 24
        // HPP = 2.4M / 24 = 100k per pcs
        $this->assertEquals(100000, $stockRecord->hpp);

        // Verify StockMovement logs original qty and unit
        $movement = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'reference_type' => 'Purchase',
            'reference_id' => $pt->id,
        ])->first();

        $this->assertNotNull($movement);
        $this->assertEquals(24, $movement->quantity); // in base unit
        $this->assertEquals(2, $movement->original_quantity); // original qty in Karton
        $this->assertEquals($karton->id, $movement->unit_id);
    }

    public function test_it_handles_enable_tax_flag_in_actions(): void
    {
        // 1. PO with enable_tax => true
        $poDataEnabled = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TAX-001',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'enable_tax' => true,
            'tax_mode' => 'GLOBAL',
            'tax_rate' => 10,
            'discount_value' => 50000,
            'discount_type' => 'fixed',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'price' => 100000,
                ]
            ]
        ];
        $poEnabled = app(CreatePurchaseOrder::class)->execute($poDataEnabled);
        $this->assertTrue($poEnabled->enable_tax);
        $this->assertEquals('GLOBAL', $poEnabled->tax_mode);
        $this->assertEquals(10, $poEnabled->tax_rate);
        $this->assertEquals(50000, $poEnabled->discount_amount);
        $this->assertEquals(1050000, $poEnabled->grand_total);

        // 2. PO with enable_tax => false
        $poDataDisabled = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-TAX-002',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'enable_tax' => false,
            'tax_mode' => 'GLOBAL',
            'tax_rate' => 10,
            'discount_value' => 0,
            'discount_type' => 'fixed',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'price' => 100000,
                ]
            ]
        ];
        $poDisabled = app(CreatePurchaseOrder::class)->execute($poDataDisabled);
        $this->assertFalse($poDisabled->enable_tax);
        $this->assertEquals('GLOBAL', $poDisabled->tax_mode);
        $this->assertEquals(0, $poDisabled->tax_rate);
        $this->assertEquals(0, $poDisabled->discount_amount);
        $this->assertEquals(0, $poDisabled->tax_amount);
        $this->assertEquals(1000000, $poDisabled->grand_total);

        // 3. PT with enable_tax => true
        $ptDataEnabled = [
            'transaction_no' => 'PT-TAX-001',
            'transaction_date' => '2026-06-28',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'status' => 'draft',
            'enable_tax' => true,
            'tax_rate' => 10,
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'other_cost' => 10000,
            'pph_amount' => 5000,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 5,
                    'price' => 100000,
                ]
            ]
        ];
        $ptEnabled = app(CreatePurchaseTransaction::class)->execute($ptDataEnabled);
        $this->assertTrue($ptEnabled->enable_tax);
        $this->assertEquals(20000, $ptEnabled->discount);
        $this->assertEquals(10000, $ptEnabled->other_cost);
        $this->assertEquals(5000, $ptEnabled->pph_amount);
        $this->assertEquals(50000, $ptEnabled->tax_amount);
        $this->assertEquals(535000, $ptEnabled->grand_total);

        // 4. PT with enable_tax => false
        $ptDataDisabled = [
            'transaction_no' => 'PT-TAX-002',
            'transaction_date' => '2026-06-28',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'status' => 'draft',
            'enable_tax' => false,
            'tax_rate' => 10,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'other_cost' => 10000,
            'pph_amount' => 5000,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 5,
                    'price' => 100000,
                ]
            ]
        ];
        $ptDisabled = app(CreatePurchaseTransaction::class)->execute($ptDataDisabled);
        $this->assertFalse($ptDisabled->enable_tax);
        $this->assertEquals(0, $ptDisabled->discount);
        $this->assertEquals(0, $ptDisabled->other_cost);
        $this->assertEquals(0, $ptDisabled->pph_amount);
        $this->assertEquals(0, $ptDisabled->tax_amount);
        $this->assertEquals(500000, $ptDisabled->grand_total);
    }

    public function test_it_handles_nominal_and_percentage_discounts(): void
    {
        // 1. PO with percentage discounts (global 10%, item 5%)
        $poData = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-DISC-PERCENT',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'enable_tax' => true,
            'tax_rate' => 10,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'item_discount_type' => 'percent',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'price' => 100000, // total 1,000,000 before discount
                    'discount_type' => 'percent',
                    'discount_value' => 5, // 5% of 1,000,000 = 50,000
                ]
            ]
        ];

        $po = app(CreatePurchaseOrder::class)->execute($poData);

        // Assertions for PO percentage discount
        $this->assertEquals('percent', $po->discount_type);
        $this->assertEquals(10, $po->discount_value);
        // Item total_amount is sum of (qty * price) - discItem = 950,000
        $this->assertEquals(950000, $po->total_amount);
        // Header discount is 10% of 950,000 = 95,000
        $this->assertEquals(95000, $po->discount_amount);
        // Tax is 95,000
        $this->assertEquals(95000, $po->tax_amount);
        // Grand total = total_amount (950,000) - discount_amount (95,000) + tax_amount (95,000) = 950,000
        $this->assertEquals(950000, $po->grand_total);

        // Verify the item
        $poItem = $po->items->first();
        $this->assertEquals('percent', $poItem->discount_type);
        $this->assertEquals(5, $poItem->discount_value);
        $this->assertEquals(50000, $poItem->discount_amount);
        $this->assertEquals(95000, $poItem->tax_amount);
        $this->assertEquals(1045000, $poItem->subtotal); // 950,000 + 95,000

        // 2. PT with percentage discounts (global 10%, item 5%)
        $ptData = [
            'transaction_no' => 'PT-DISC-PERCENT',
            'transaction_date' => '2026-06-28',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'status' => 'draft',
            'enable_tax' => true,
            'tax_rate' => 10,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'item_discount_type' => 'percent',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 10,
                    'price' => 100000,
                    'discount_type' => 'percent',
                    'discount_value' => 5,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($ptData);

        // Assertions for PT percentage discount
        $this->assertEquals('percent', $pt->discount_type);
        $this->assertEquals(10, $pt->discount_value);
        $this->assertEquals(950000, $pt->subtotal);
        $this->assertEquals(95000, $pt->discount);
        $this->assertEquals(95000, $pt->tax_amount);
        $this->assertEquals(950000, $pt->grand_total);

        // Verify detail
        $ptDetail = $pt->details->first();
        $this->assertEquals('percent', $ptDetail->discount_type);
        $this->assertEquals(5, $ptDetail->discount_value);
        $this->assertEquals(50000, $ptDetail->discount);
    }

    public function test_it_handles_enable_item_discount_and_item_discount_type_settings(): void
    {
        // PO with item_discount_type => 'percent'
        $poData = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-DISC-ITEM-TEST',
            'order_date' => '2026-06-28',
            'status' => 'draft',
            'enable_tax' => true,
            'tax_rate' => 10,
            'item_discount_type' => 'percent',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'price' => 100000,
                    'discount_value' => 5, // 5%
                ]
            ]
        ];

        $po = app(CreatePurchaseOrder::class)->execute($poData);
        $this->assertTrue($po->enable_item_discount);
        $this->assertEquals('percent', $po->item_discount_type);
        $poItem = $po->items->first();
        $this->assertEquals('percent', $poItem->discount_type);
        $this->assertEquals(5, $poItem->discount_value);
        $this->assertEquals(50000, $poItem->discount_amount);
    }

    public function test_it_updates_master_cost_price_and_pricing_tiers_if_checked_on_post(): void
    {
        // 1. Create a dynamic pricing tier that follows HPP
        $pricingTier = \App\Models\PricingTier::create([
            'name' => 'Grosir A',
            'description' => 'Grosir Tier',
            'price_follows_hpp' => true,
        ]);

        // Create transaction draft with update_cost_price = true
        $txData = [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'purchase_type' => 'Tunai',
            'transaction_date' => '2026-06-28',
            'status' => 'draft',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 5,
                    'price' => 1200000, // new buying price is 1.2M
                    'discount' => 0,
                    'tax_rate' => 0,
                    'update_cost_price' => true, // update master!
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($txData);
        
        $this->assertEquals(1000000, $this->variant->fresh()->cost_price); // still old cost price

        // Post transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        // Verify master cost price updated
        $this->variant->refresh();
        $this->assertEquals(1200000, $this->variant->cost_price);
        $this->assertEquals(1200000, $this->variant->hpp);

        // Verify pricing tier price updated
        $tierPrice = \App\Models\ProductTierPrice::where('product_variant_id', $this->variant->id)
            ->where('pricing_tier_id', $pricingTier->id)
            ->first();
        $this->assertNotNull($tierPrice);
        $this->assertEquals(1200000, $tierPrice->price);
    }

    public function test_it_handles_qty_bonus_without_affecting_financials_but_increasing_stock(): void
    {
        // Reset stock to 0 for a clean test
        \App\Models\ProductBranchStock::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $this->variant->id)
            ->delete();

        // Create transaction draft with qty_received = 10, qty_bonus = 2
        $txData = [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'purchase_type' => 'Tunai',
            'transaction_date' => '2026-06-28',
            'status' => 'draft',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 10,
                    'qty_bonus' => 2, // 2 free items!
                    'price' => 100000, // Rp 100.000 per item
                    'discount' => 0,
                    'tax_rate' => 0,
                    'update_cost_price' => false,
                ]
            ]
        ];

        $pt = app(CreatePurchaseTransaction::class)->execute($txData);

        // Verify financials (only 10 items counted in subtotal = 10 * 100,000 = 1,000,000)
        $this->assertEquals(1000000, $pt->subtotal);
        $this->assertEquals(1000000, $pt->grand_total);
        $this->assertEquals(2, $pt->details->first()->qty_bonus);

        // Post transaction
        app(PostPurchaseTransaction::class)->execute($pt);

        // Verify stock increased by 10 + 2 = 12
        $stock = \App\Models\ProductBranchStock::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        
        $this->assertNotNull($stock);
        $this->assertEquals(12, $stock->stock);

        // Verify StockMovement logged original quantity as 12
        $movement = \App\Models\StockMovement::where('product_variant_id', $this->variant->id)
            ->where('reference_id', $pt->id)
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(12, $movement->original_quantity);
    }
}
