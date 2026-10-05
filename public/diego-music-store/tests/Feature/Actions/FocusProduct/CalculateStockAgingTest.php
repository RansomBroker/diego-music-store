<?php

namespace Tests\Feature\Actions\FocusProduct;

use App\Actions\FocusProduct\CalculateStockAging;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateStockAgingTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'store_name' => 'Diego Music Pontianak',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Yamaha Pacifica 112V',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DG-YMH-112V',
            'name' => 'Standard',
            'price' => 3500000,
            'cost_price' => 2500000,
            'hpp' => 2500000,
        ]);
    }

    public function test_returns_zero_aging_when_stock_is_zero(): void
    {
        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 0,
            'hpp' => 2500000,
        ]);

        $action = app(CalculateStockAging::class);
        $result = $action->execute($this->branch->id, $this->variant->id);

        $this->assertEquals(0, $result['aging_days']);
        $this->assertEquals(0, $result['current_stock']);
        $this->assertNull($result['oldest_batch_date']);
    }

    public function test_calculates_aging_based_on_fifo_inbound_movements(): void
    {
        // Current stock = 7 units
        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 7,
            'hpp' => 2500000,
        ]);

        // Batch 1 (oldest): 200 days ago, In +10
        $movementOld = StockMovement::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'quantity' => 10,
            'hpp' => 2500000,
            'reference_type' => 'DO',
            'reference_id' => 1,
        ]);
        $movementOld->timestamps = false;
        $movementOld->created_at = now()->subDays(200);
        $movementOld->save();

        // Batch 2 (newest): 50 days ago, In +5
        $movementNew = StockMovement::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'quantity' => 5,
            'hpp' => 2500000,
            'reference_type' => 'DO',
            'reference_id' => 2,
        ]);
        $movementNew->timestamps = false;
        $movementNew->created_at = now()->subDays(50);
        $movementNew->save();

        // Under FIFO: remaining 7 units consist of 5 units from Batch 2 (50 days ago) and 2 units from Batch 1 (200 days ago)
        // Therefore, oldest remaining batch date is 200 days ago!
        $action = app(CalculateStockAging::class);
        $result = $action->execute($this->branch->id, $this->variant->id);

        $this->assertEquals(7, $result['current_stock']);
        $this->assertGreaterThanOrEqual(199, $result['aging_days']);
        $this->assertLessThanOrEqual(201, $result['aging_days']);
        $this->assertNotNull($result['oldest_batch_date']);
    }
}
