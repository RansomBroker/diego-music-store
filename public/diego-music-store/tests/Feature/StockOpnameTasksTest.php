<?php

namespace Tests\Feature;

use App\Livewire\StockOpnameTasks;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_employee_can_count_and_submit_without_changing_branch_stock(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'address' => 'Pontianak',
            'is_active' => true,
        ]);

        $employeeUser = User::factory()->create();
        $employeeUser->employee()->update([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $employeeUser->id,
        ]);

        $preset = StockOpnamePreset::create([
            'branch_id' => $branch->id,
            'name' => 'Lantai 1',
            'is_active' => true,
        ]);

        $assignment = StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $employeeUser->id,
            'assigned_by' => $employeeUser->id,
            'status' => StockOpnameAssignment::STATUS_ASSIGNED,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        $product = Product::create([
            'name' => 'Keyboard Yamaha PSR',
            'type' => 'physical',
            'category' => 'Keyboard',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hitam',
            'sku' => 'YMH-PSR-001',
            'barcode' => '89910001',
            'price' => 2500000,
            'cost_price' => 2000000,
            'hpp' => 2000000,
            'is_active' => true,
        ]);

        $branchStock = ProductBranchStock::create([
            'branch_id' => $branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 10,
            'hpp' => 2000000,
        ]);

        $test = Livewire::actingAs($employeeUser)
            ->test(StockOpnameTasks::class)
            ->call('startTask', $assignment->id)
            ->assertSet('activeAssignmentId', $assignment->id)
            ->set('search', 'YMH-PSR-001')
            ->assertSee('Keyboard Yamaha PSR')
            ->call('chooseProduct', $variant->id)
            ->set('selectedPhysicalQty', '8')
            ->call('saveCount')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stock_opname_items', [
            'product_variant_id' => $variant->id,
            'system_qty' => 10,
            'physical_qty' => 8,
            'difference' => -2,
        ]);

        $test->call('submitTask')
            ->assertHasNoErrors();

        $this->assertSame(10, (int) $branchStock->fresh()->stock);
        $this->assertDatabaseHas('stock_opname_assignments', [
            'id' => $assignment->id,
            'status' => StockOpnameAssignment::STATUS_SUBMITTED,
        ]);
        $this->assertDatabaseHas('stock_opname_periods', [
            'id' => $period->id,
            'status' => StockOpnamePeriod::STATUS_REVIEW,
        ]);
    }

    public function test_employee_cannot_open_another_employees_assignment(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'is_active' => true,
        ]);

        $owner = User::factory()->create();
        $employeeUser = User::factory()->create();
        $otherAssignedUser = User::factory()->create();
        $employeeUser->employee()->update(['branch_id' => $branch->id, 'is_active' => true]);
        $otherAssignedUser->employee()->update(['branch_id' => $branch->id, 'is_active' => true]);

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $owner->id,
        ]);

        $preset = StockOpnamePreset::create([
            'branch_id' => $branch->id,
            'name' => 'Lantai 2',
            'is_active' => true,
        ]);

        $otherAssignment = StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $otherAssignedUser->id,
            'assigned_by' => $owner->id,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        Livewire::actingAs($employeeUser)
            ->test(StockOpnameTasks::class)
            ->call('startTask', $otherAssignment->id)
            ->assertStatus(404);
    }
}
