<?php

namespace Tests\Feature;

use App\Livewire\StockOpnameReview;
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
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameReviewPosTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $adminUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'is_active' => true,
        ]);

        $this->adminUser = User::factory()->create(['is_active' => true]);
        $this->adminUser->assignRole('admin');
        $this->adminUser->branches()->attach($this->branch->id);

        $this->employeeUser = User::factory()->create(['is_active' => true]);
        $this->employeeUser->assignRole('karyawan');
        $this->employeeUser->branches()->attach($this->branch->id);
    }

    public function test_karyawan_is_forbidden_from_stock_opname_review_in_pos(): void
    {
        Livewire::actingAs($this->employeeUser)
            ->test(StockOpnameReview::class)
            ->assertForbidden();
    }

    public function test_admin_can_access_stock_opname_review_in_pos(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class)
            ->assertSuccessful();
    }

    public function test_admin_can_open_and_close_finalization_modal_in_pos(): void
    {
        $period = StockOpnamePeriod::create([
            'branch_id' => $this->branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Test Modal',
            'status' => StockOpnamePeriod::STATUS_ACTIVE,
            'created_by' => $this->adminUser->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class)
            ->call('selectPeriod', $period->id)
            ->assertSet('selectedPeriodId', $period->id)
            ->assertSet('showFinalizeModal', false)
            ->call('openFinalizeModal', $period->id)
            ->assertSet('showFinalizeModal', true)
            ->assertSet('periodIdToFinalize', $period->id)
            ->call('closeFinalizeModal')
            ->assertSet('showFinalizeModal', false)
            ->assertSet('periodIdToFinalize', null);
    }

    public function test_admin_can_finalize_period_via_modal_in_pos(): void
    {
        $period = StockOpnamePeriod::create([
            'branch_id' => $this->branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Finalize POS Test',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $this->adminUser->id,
        ]);

        $preset = StockOpnamePreset::create([
            'branch_id' => $this->branch->id,
            'name' => 'Lantai 1',
            'is_active' => true,
        ]);

        $assignment = StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $this->employeeUser->id,
            'status' => StockOpnameAssignment::STATUS_COMPLETED,
            'assigned_by' => $this->adminUser->id,
            'submitted_at' => now(),
        ]);

        $product = Product::create([
            'name' => 'Gitar Fender',
            'type' => 'physical',
            'category' => 'Gitar',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Sunburst',
            'sku' => 'FEN-001',
            'price' => 2000000,
            'cost_price' => 1500000,
            'hpp' => 1500000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 5,
            'hpp' => 1500000,
        ]);

        $opname = StockOpname::create([
            'branch_id' => $this->branch->id,
            'opname_date' => now(),
            'status' => 'draft',
            'notes' => 'Hitungan Lantai 1',
        ]);

        $assignment->update(['stock_opname_id' => $opname->id]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_variant_id' => $variant->id,
            'system_qty' => 5,
            'physical_qty' => 5,
            'difference' => 0,
            'cost_price' => 1500000,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class)
            ->call('selectPeriod', $period->id)
            ->call('openFinalizeModal', $period->id)
            ->assertSet('showFinalizeModal', true)
            ->call('finalizePeriod')
            ->assertSet('showFinalizeModal', false)
            ->assertDispatched('toast');

        $this->assertSame(StockOpnamePeriod::STATUS_COMPLETED, $period->fresh()->status);
    }
}
