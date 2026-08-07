<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\ProductBranchStock;
use App\Actions\Purchases\CreatePurchaseReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Pembelian', 'store_name' => 'Diego Store']);
        $this->supplier = Supplier::create(['name' => 'PT Distributor Musik Utama']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_process_partial_purchase_return_to_supplier()
    {
        $product = Product::create([
            'name' => 'Amplifier Roland JC-120',
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'AMP-RLD-120',
            'price' => 15000000,
            'cost_price' => 12000000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 5,
            'hpp' => 12000000,
        ]);

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-20260803-0001',
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'subtotal' => 60000000,
            'grand_total' => 60000000,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => 5,
            'qty_received' => 5,
            'price' => 12000000,
            'subtotal' => 60000000,
        ]);

        // Execute partial return of 2 units back to supplier
        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason' => 'Barang rusak kemasan dari supplier',
            'items' => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity' => 2, // Return 2 units out of 5 received
                ]
            ]
        ]);

        $this->assertDatabaseHas('purchase_returns', [
            'id' => $purchaseReturn->id,
            'purchase_transaction_id' => $pt->id,
            'total_amount' => 24000000,
        ]);

        $this->assertDatabaseHas('purchase_return_items', [
            'purchase_return_id' => $purchaseReturn->id,
            'purchase_transaction_detail_id' => $detail->id,
            'quantity' => 2,
            'total_price' => 24000000,
        ]);

        // Stock decreased from 5 to 3
        $this->assertDatabaseHas('product_branch_stocks', [
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 3,
        ]);
    }
}
