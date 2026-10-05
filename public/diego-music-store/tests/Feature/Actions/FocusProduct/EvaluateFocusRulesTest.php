<?php

namespace Tests\Feature\Actions\FocusProduct;

use App\Actions\FocusProduct\EvaluateFocusRules;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\FocusProductRule;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Database\Seeders\FocusProductRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluateFocusRulesTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FocusProductRuleSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'store_name' => 'Diego Music Pontianak',
            'is_active' => true,
        ]);
    }

    protected function createProductWithStock(string $name, string $sku, int $stockQty, int $price = 1000000, int $hpp = 700000): ProductVariant
    {
        $product = Product::create([
            'name' => $name,
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'name' => 'Standard',
            'price' => $price,
            'cost_price' => $hpp,
            'hpp' => $hpp,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => $stockQty,
            'hpp' => $hpp,
        ]);

        return $variant;
    }

    public function test_detects_dead_stock_when_stock_positive_and_zero_sales(): void
    {
        $variant = $this->createProductWithStock('Gitar Akustik Dead', 'DG-DEAD-01', 5);

        $action = app(EvaluateFocusRules::class);
        $result = $action->execute($this->branch->id);

        $this->assertGreaterThan(0, $result['recommendations_generated']);

        $recommendation = FocusProductRecommendation::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
        ])
        ->whereHas('rule', fn ($q) => $q->where('code', 'dead_stock'))
        ->first();

        $this->assertNotNull($recommendation);
        $this->assertEquals('PENDING', $recommendation->status);
        $this->assertEquals(5, $recommendation->current_stock);
        $this->assertEquals(0, $recommendation->recent_sales_qty);
        $this->assertStringContainsString('tanpa ada transaksi penjualan', $recommendation->reason);
    }

    public function test_detects_slow_moving_when_sales_under_threshold(): void
    {
        $variant = $this->createProductWithStock('Bass Elektrik Slow', 'DG-SLOW-01', 4);
        $user = \App\Models\User::factory()->create();

        // Record a sale of 1 unit 2 months ago (less than max_sales_qty 2)
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $user->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->subMonths(2),
            'subtotal' => 1000000,
            'grand_total' => 1000000,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 1000000,
            'total_price' => 1000000,
        ]);

        $action = app(EvaluateFocusRules::class);
        $result = $action->execute($this->branch->id);

        $recommendation = FocusProductRecommendation::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
        ])
        ->whereHas('rule', fn ($q) => $q->where('code', 'slow_moving'))
        ->first();

        $this->assertNotNull($recommendation);
        $this->assertEquals(1, $recommendation->recent_sales_qty);
        $this->assertStringContainsString('hanya terjual 1 unit', $recommendation->reason);
    }

    public function test_detects_aging_stock_when_fifo_aging_exceeds_threshold(): void
    {
        $variant = $this->createProductWithStock('Keyboard Aging', 'DG-AGE-01', 3);

        // Mutasi masuk 210 hari lalu (threshold 180 hari)
        $movement = StockMovement::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'type' => 'in',
            'quantity' => 3,
            'hpp' => 700000,
            'reference_type' => 'DO',
            'reference_id' => 1,
        ]);
        $movement->timestamps = false;
        $movement->created_at = now()->subDays(210);
        $movement->save();

        $action = app(EvaluateFocusRules::class);
        $result = $action->execute($this->branch->id);

        $recommendation = FocusProductRecommendation::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
        ])
        ->whereHas('rule', fn ($q) => $q->where('code', 'aging_stock'))
        ->first();

        $this->assertNotNull($recommendation);
        $this->assertGreaterThanOrEqual(209, $recommendation->aging_days);
        $this->assertStringContainsString('mengendap selama', $recommendation->reason);
    }

    public function test_does_not_recommend_already_active_focus_products(): void
    {
        $variant = $this->createProductWithStock('Gitar Already Focus', 'DG-FOC-01', 5);

        // Set as active focus product
        FocusProduct::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'source' => 'MANUAL',
            'status' => 'ACTIVE',
            'reason' => 'Sudah difokuskan',
            'active_from' => now(),
            'focused_at' => now(),
        ]);

        $action = app(EvaluateFocusRules::class);
        $result = $action->execute($this->branch->id);

        $recommendation = FocusProductRecommendation::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
        ])->first();

        $this->assertNull($recommendation);
    }
}
