<?php

namespace Tests\Feature;

use App\Actions\StockOpname\RequestStockOpnameRevision;
use App\Models\Branch;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequestStockOpnameRevisionTest extends TestCase
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

    public function test_can_request_revision_on_submitted_assignment(): void
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

        $period->update(['status' => StockOpnamePeriod::STATUS_REVIEW]);

        $revision = app(RequestStockOpnameRevision::class)->execute($assignment, 'Mohon cek ulang gitar fender');

        $this->assertSame(StockOpnameAssignment::STATUS_NEEDS_REVISION, $revision->status);
        $this->assertSame('Mohon cek ulang gitar fender', $revision->review_notes);
        $this->assertSame($this->adminUser->id, $revision->reviewed_by);
        $this->assertSame(StockOpnamePeriod::STATUS_ACTIVE, $period->fresh()->status);
    }

    public function test_cannot_request_revision_without_notes(): void
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

        $this->expectException(ValidationException::class);

        app(RequestStockOpnameRevision::class)->execute($assignment, '   ');
    }
}
