<?php

namespace Tests\Feature\Actions\FocusProduct;

use App\Actions\FocusProduct\ApplyFocusRulesToBranch;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\FocusProductRule;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use Database\Seeders\FocusProductRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyFocusRulesToBranchTest extends TestCase
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

    public function test_applies_selected_rules_and_creates_active_focus_products(): void
    {
        // Create 2 dead stock products
        $var1 = $this->createProductWithStock('Gitar Dead 1', 'DG-D1', 4);
        $var2 = $this->createProductWithStock('Gitar Dead 2', 'DG-D2', 6);

        $action = app(ApplyFocusRulesToBranch::class);
        $result = $action->execute(
            $this->branch->id,
            ['dead_stock'],
            now()->addMonth()->toDateTimeString(),
            'Fokus cuci gudang massal'
        );

        $this->assertGreaterThanOrEqual(2, $result['focused_count']);

        $focus1 = FocusProduct::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $var1->id)
            ->first();

        $this->assertNotNull($focus1);
        $this->assertEquals('ACTIVE', $focus1->status);
        $this->assertEquals('RULE', $focus1->source);
        $this->assertEquals('Fokus cuci gudang massal', $focus1->note);

        $focus2 = FocusProduct::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $var2->id)
            ->first();

        $this->assertNotNull($focus2);
        $this->assertEquals('ACTIVE', $focus2->status);
    }
}
