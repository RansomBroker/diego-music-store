<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\ProductBranchStock;
use App\Actions\Sales\CreateSalesReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnPartialTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Retur', 'store_name' => 'Diego Retur']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_process_partial_sales_return()
    {
        $customer = Customer::create(['name' => 'Budi Sudarsono']);

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-YMH-001',
            'price' => 1500000,
            'cost_price' => 1000000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 10,
            'hpp' => 1000000,
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-20260803-0099',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 4500000,
            'grand_total' => 4500000,
            'payment_method' => 'Tunai',
            'status' => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3, // Sold 3 units
            'unit_price' => 1500000,
            'total_price' => 4500000,
        ]);

        // Execute partial return of 1 unit out of 3 sold
        $salesReturn = app(CreateSalesReturn::class)->execute([
            'sale_id' => $sale->id,
            'reason' => 'Cacat finishing 1 unit',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1, // Only return 1 unit!
                ]
            ]
        ]);

        $this->assertDatabaseHas('sales_returns', [
            'id' => $salesReturn->id,
            'sale_id' => $sale->id,
            'total_refund' => 1500000,
        ]);

        $this->assertDatabaseHas('sales_return_items', [
            'sales_return_id' => $salesReturn->id,
            'sale_item_id' => $saleItem->id,
            'quantity' => 1,
            'refund_amount' => 1500000,
        ]);

        // Verify stock increased by 1 unit (10 + 1 = 11)
        $this->assertDatabaseHas('product_branch_stocks', [
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 11,
        ]);
    }
}
