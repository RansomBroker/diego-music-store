<?php

namespace Tests\Feature;

use App\Actions\StockOpname\ApproveStockOpnameAssignment;
use App\Models\Branch;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApproveStockOpnameAssignmentTest extends TestCase
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

    public function test_can_approve_submitted_assignment(): void
    {
        $this->actingAs($this->adminUser);

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

        $approved = app(ApproveStockOpnameAssignment::class)->execute($assignment, 'Hasil hitungan valid');

        $this->assertSame(StockOpnameAssignment::STATUS_COMPLETED, $approved->status);
        $this->assertSame('Hasil hitungan valid', $approved->review_notes);
        $this->assertSame($this->adminUser->id, $approved->reviewed_by);
        $this->assertNotNull($approved->reviewed_at);
    }

    public function test_cannot_approve_unsubmitted_assignment(): void
    {
        $this->actingAs($this->adminUser);

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
            'status' => StockOpnameAssignment::STATUS_ASSIGNED,
            'assigned_by' => $this->adminUser->id,
        ]);

        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

        $this->expectException(ValidationException::class);

        app(ApproveStockOpnameAssignment::class)->execute($assignment);
    }
}
