<?php

namespace Tests\Feature;

use App\Filament\Pages\StockOpnameReview;
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
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameReviewBackofficePageTest extends TestCase
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

    public function test_admin_can_access_stock_opname_review_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        $this->actingAs($this->adminUser);
        $this->assertTrue(StockOpnameReview::canAccess());

        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class)
            ->assertSuccessful();
    }

    public function test_karyawan_cannot_access_stock_opname_review_page(): void
    {
        $this->actingAs($this->employeeUser);
        $this->assertFalse(StockOpnameReview::canAccess());
    }

    public function test_can_approve_assignment_and_request_revision_in_backoffice(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        $period = StockOpnamePeriod::create([
            'branch_id' => $this->branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
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
            'status' => StockOpnameAssignment::STATUS_SUBMITTED,
            'assigned_by' => $this->adminUser->id,
            'submitted_at' => now(),
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        // Test request revision
        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class, ['period_id' => $period->id])
            ->assertSuccessful()
            ->call('selectAssignment', $assignment->id)
            ->set('reviewNotes', 'Tolong hitung ulang varian hitam')
            ->call('requestRevision', $assignment->id)
            ->assertHasNoErrors();

        $this->assertSame(StockOpnameAssignment::STATUS_NEEDS_REVISION, $assignment->fresh()->status);
        $this->assertSame('Tolong hitung ulang varian hitam', $assignment->fresh()->review_notes);

        // Employee resubmits
        $assignment->refresh()->update([
            'status' => StockOpnameAssignment::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Test approve assignment
        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class, ['period_id' => $period->id])
            ->call('selectAssignment', $assignment->id)
            ->set('reviewNotes', 'Sudah sesuai')
            ->call('approveAssignment', $assignment->id)
            ->assertHasNoErrors();

        $this->assertSame(StockOpnameAssignment::STATUS_COMPLETED, $assignment->fresh()->status);
        $this->assertSame('Sudah sesuai', $assignment->fresh()->review_notes);
    }

    public function test_can_finalize_period_in_backoffice_review_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        $period = StockOpnamePeriod::create([
            'branch_id' => $this->branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Finalize Test',
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
            'name' => 'Keyboard Yamaha',
            'type' => 'physical',
            'category' => 'Keyboard',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hitam',
            'sku' => 'KEY-001',
            'price' => 1000000,
            'cost_price' => 700000,
            'hpp' => 700000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 10,
            'hpp' => 700000,
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
            'system_qty' => 10,
            'physical_qty' => 10,
            'difference' => 0,
            'cost_price' => 700000,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        Livewire::actingAs($this->adminUser)
            ->test(StockOpnameReview::class, ['period_id' => $period->id])
            ->assertSuccessful()
            ->call('finalizePeriod', $period->id)
            ->assertDispatched('close-modal');

        $this->assertSame(StockOpnamePeriod::STATUS_COMPLETED, $period->fresh()->status);
    }
}
