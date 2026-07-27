<?php

namespace Tests\Feature\Actions;

use App\Actions\Sales\CreateSalesInvoice;
use App\Actions\Sales\CreateSalesQuotation;
use App\Actions\Sales\PostSalesInvoice;
use App\Actions\Sales\UpdateSalesInvoice;
use App\Actions\Sales\UpdateSalesQuotation;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\SalesInvoice;
use App\Models\SalesQuotation;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Branch $branch;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'name' => 'Toko Alat Musik Jaya',
            'phone' => '081122334455',
        ]);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'store_name' => 'Diego Music Central',
            'is_active' => true,
        ]);

        $unit = Unit::create(['name' => 'Pcs', 'code' => 'PCS', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Gitar Listrik Ibanez',
            'type' => 'physical',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'IBNZ-RG-01',
            'price' => 7500000,
            'cost_price' => 5000000,
            'hpp' => 5000000,
            'is_active' => true,
        ]);

        // Seed stock for branch
        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 20,
            'hpp' => 5000000,
        ]);
    }

    public function test_it_can_create_and_update_sales_quotation_action(): void
    {
        $createData = [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'quotation_number' => 'SQ-ACTION-001',
            'quotation_date' => '2026-07-27',
            'discount_type' => 'fixed',
            'discount_value' => 500000,
            'tax_rate' => 11,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'price' => 7500000,
                ]
            ]
        ];

        $sq = app(CreateSalesQuotation::class)->execute($createData);

        $this->assertInstanceOf(SalesQuotation::class, $sq);
        $this->assertEquals(15000000, $sq->subtotal); // 2 * 7,500,000
        $this->assertEquals(500000, $sq->discount_amount);
        $this->assertEquals(16095000, $sq->grand_total); // (15,000,000 - 500,000) * 1.11 = 14,500,000 + 1,595,000 = 16,095,000

        // Test Update Action
        $updateData = $createData;
        $updateData['status'] = 'approved';
        $updateData['items'][0]['quantity'] = 3;

        $updatedSq = app(UpdateSalesQuotation::class)->execute($sq, $updateData);
        $this->assertEquals('approved', $updatedSq->status);
        $this->assertEquals(22500000, $updatedSq->subtotal); // 3 * 7,500,000
    }

    public function test_it_can_create_and_post_sales_invoice_action(): void
    {
        $createData = [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'invoice_number' => 'INV-ACTION-001',
            'invoice_date' => '2026-07-27',
            'payment_type' => 'Kredit',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'tax_rate' => 0,
            'shipping_cost' => 100000,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 3,
                    'price' => 7500000,
                ]
            ]
        ];

        $invoice = app(CreateSalesInvoice::class)->execute($createData);

        $this->assertInstanceOf(SalesInvoice::class, $invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(22600000, $invoice->grand_total); // 22,500,000 + 100,000 shipping

        // Post Sales Invoice
        app(PostSalesInvoice::class)->execute($invoice);

        $invoice->refresh();
        $this->assertEquals('posted', $invoice->status);
        $this->assertNotNull($invoice->posted_at);

        // Assert stock deducted by 3 (20 -> 17)
        $stock = ProductBranchStock::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(17, $stock->stock);

        // Assert StockMovement OUT created
        $movement = StockMovement::where('reference_type', 'SalesInvoice')
            ->where('reference_id', $invoice->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(3, $movement->quantity);

        // Assert automatic JournalEntry created
        $journal = JournalEntry::where('reference_type', 'SalesInvoice')
            ->where('reference_id', $invoice->id)
            ->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals(37600000, $journal->items()->sum('debit'));
        $this->assertEquals($journal->items()->sum('debit'), $journal->items()->sum('credit'));
        $this->assertEquals($journal->items()->sum('debit'), $journal->items()->sum('credit'));
    }
}
