<?php

namespace Tests\Feature;

use App\Actions\StockOpname\FinalizeStockOpnamePeriod;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnameItem;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinalizeStockOpnamePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_finalization_is_blocked_if_an_active_physical_variant_has_not_been_counted(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'address' => 'Pontianak',
            'is_active' => true,
        ]);

        $employee = User::factory()->create();
        $employee->employee()->update([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $employee->id,
        ]);

        $preset = StockOpnamePreset::create([
            'branch_id' => $branch->id,
            'name' => 'Lantai 1',
            'is_active' => true,
        ]);

        $productA = Product::create([
            'name' => 'Keyboard Yamaha',
            'type' => 'physical',
            'category' => 'Keyboard',
            'is_active' => true,
        ]);
        $variantA = ProductVariant::create([
            'product_id' => $productA->id,
            'name' => 'Hitam',
            'sku' => 'KEY-001',
            'price' => 1000000,
            'cost_price' => 700000,
            'hpp' => 700000,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'name' => 'Gitar Akustik',
            'type' => 'physical',
            'category' => 'Gitar',
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $productB->id,
            'name' => 'Natural',
            'sku' => 'GTR-001',
            'price' => 1500000,
            'cost_price' => 900000,
            'hpp' => 900000,
            'is_active' => true,
        ]);

        $branchStock = ProductBranchStock::create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variantA->id,
            'stock' => 10,
            'hpp' => 700000,
        ]);

        $opname = StockOpname::create([
            'branch_id' => $branch->id,
            'opname_date' => '2026-10-01',
            'status' => 'draft',
            'notes' => 'Hitungan tugas lokasi',
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_variant_id' => $variantA->id,
            'system_qty' => 10,
            'physical_qty' => 8,
            'difference' => -2,
            'cost_price' => 700000,
        ]);

        StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $employee->id,
            'stock_opname_id' => $opname->id,
            'status' => StockOpnameAssignment::STATUS_COMPLETED,
            'assigned_by' => $employee->id,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_REVIEW]);

        try {
            app(FinalizeStockOpnamePeriod::class)->execute($period);
            $this->fail('Finalization should be blocked until every active physical variant is counted.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('belum memiliki hitungan', strtolower($exception->getMessage()));
        }

        $this->assertSame(10, (int) $branchStock->fresh()->stock);
        $this->assertSame(1, StockOpname::query()->count(), 'No consolidated document should persist after a blocked finalization.');
        $this->assertDatabaseHas('stock_opname_periods', [
            'id' => $period->id,
            'status' => StockOpnamePeriod::STATUS_REVIEW,
        ]);
    }
}
