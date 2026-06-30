<?php

namespace Tests\Feature\Actions;

use App\Actions\StockOpname\CreateStockOpname;
use App\Actions\StockOpname\UpdateStockOpname;
use App\Actions\StockOpname\ProcessStockOpnameComplete;
use App\Models\Branch;
use App\Models\StockOpname;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameActionsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Surabaya',
            'address' => 'Surabaya',
            'phone' => '031-33333',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Kibor Roland XPS-30',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'RLD-XPS30-BLK',
            'barcode' => '999111333',
            'name' => 'Hitam',
            'price' => 14000000,
            'cost_price' => 10000000,
            'hpp' => 10000000,
            'is_active' => true,
        ]);
    }

    public function test_it_can_create_and_update_stock_opname_drafts_with_correct_differences(): void
    {
        // 1. Setup stock to 10
        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 10000000,
        ]);

        $createData = [
            'branch_id' => $this->branch->id,
            'opname_date' => '2026-06-30',
            'status' => 'draft',
            'notes' => 'Stock opname notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'system_qty' => 10,
                    'physical_qty' => 8, // discrepancy is -2
                ]
            ]
        ];

        $opname = app(CreateStockOpname::class)->execute($createData);

        $this->assertInstanceOf(StockOpname::class, $opname);
        $this->assertEquals('draft', $opname->status);
        $this->assertCount(1, $opname->items);
        $this->assertEquals(-2, $opname->fresh()->items->first()->difference);

        $updateData = [
            'branch_id' => $this->branch->id,
            'opname_date' => '2026-06-30',
            'status' => 'draft',
            'notes' => 'Updated notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'system_qty' => 10,
                    'physical_qty' => 13, // discrepancy is +3
                ]
            ]
        ];

        $updatedOpname = app(UpdateStockOpname::class)->execute($opname, $updateData);
        $this->assertEquals('Updated notes', $updatedOpname->notes);
        $this->assertEquals(3, $updatedOpname->fresh()->items->first()->difference);
    }

    public function test_it_adjusts_stock_up_and_logs_in_movement_when_completed_with_surplus(): void
    {
        // Setup initial stock to 10
        $stockRecord = ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 10000000,
        ]);

        $opname = StockOpname::create([
            'branch_id' => $this->branch->id,
            'opname_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        $opname->items()->create([
            'product_variant_id' => $this->variant->id,
            'system_qty' => 10,
            'physical_qty' => 12, // surplus +2
            'difference' => 2,
            'cost_price' => 10000000,
        ]);

        // Process completion
        app(ProcessStockOpnameComplete::class)->execute($opname);

        $this->assertEquals('completed', $opname->fresh()->status);

        // System stock should become 12
        $stockRecord->refresh();
        $this->assertEquals(12, $stockRecord->stock);

        // Assert StockMovement IN logged
        $movement = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'reference_type' => 'Opname',
            'reference_id' => $opname->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(2, $movement->quantity);
    }

    public function test_it_adjusts_stock_down_and_logs_out_movement_when_completed_with_deficit(): void
    {
        // Setup initial stock to 10
        $stockRecord = ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 10000000,
        ]);

        $opname = StockOpname::create([
            'branch_id' => $this->branch->id,
            'opname_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        $opname->items()->create([
            'product_variant_id' => $this->variant->id,
            'system_qty' => 10,
            'physical_qty' => 7, // deficit -3
            'difference' => -3,
            'cost_price' => 10000000,
        ]);

        // Process completion
        app(ProcessStockOpnameComplete::class)->execute($opname);

        $this->assertEquals('completed', $opname->fresh()->status);

        // System stock should become 7
        $stockRecord->refresh();
        $this->assertEquals(7, $stockRecord->stock);

        // Assert StockMovement OUT logged
        $movement = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'out',
            'reference_type' => 'Opname',
            'reference_id' => $opname->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(3, $movement->quantity);
    }

    public function test_it_does_not_log_movement_if_physical_equals_system(): void
    {
        // Setup initial stock to 10
        $stockRecord = ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 10000000,
        ]);

        $opname = StockOpname::create([
            'branch_id' => $this->branch->id,
            'opname_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        $opname->items()->create([
            'product_variant_id' => $this->variant->id,
            'system_qty' => 10,
            'physical_qty' => 10, // difference = 0
            'difference' => 0,
            'cost_price' => 10000000,
        ]);

        // Process completion
        app(ProcessStockOpnameComplete::class)->execute($opname);

        $this->assertEquals('completed', $opname->fresh()->status);

        // Stock remains 10
        $stockRecord->refresh();
        $this->assertEquals(10, $stockRecord->stock);

        // Assert NO StockMovement logged
        $movementCount = StockMovement::where([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'reference_type' => 'Opname',
            'reference_id' => $opname->id,
        ])->count();
        $this->assertEquals(0, $movementCount);
    }
}
