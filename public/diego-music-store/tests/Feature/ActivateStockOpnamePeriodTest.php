<?php

namespace Tests\Feature;

use App\Actions\StockOpname\ActivateStockOpnamePeriod;
use App\Models\Branch;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ActivateStockOpnamePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_activate_draft_period_with_assignments(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->employee()->update([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $preset = StockOpnamePreset::create([
            'branch_id' => $branch->id,
            'name' => 'Lantai 1',
            'is_active' => true,
        ]);

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $user->id,
            'assigned_by' => $user->id,
        ]);

        $activated = app(ActivateStockOpnamePeriod::class)->execute($period);

        $this->assertSame(StockOpnamePeriod::STATUS_ACTIVE, $activated->status);
        $this->assertDatabaseHas('stock_opname_periods', [
            'id' => $period->id,
            'status' => StockOpnamePeriod::STATUS_ACTIVE,
        ]);
    }

    public function test_cannot_activate_period_without_assignments(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        $this->expectException(ValidationException::class);

        app(ActivateStockOpnamePeriod::class)->execute($period);
    }

    public function test_cannot_activate_non_draft_period(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $period = StockOpnamePeriod::create([
            'branch_id' => $branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_ACTIVE,
            'created_by' => $user->id,
        ]);

        $this->expectException(ValidationException::class);

        app(ActivateStockOpnamePeriod::class)->execute($period);
    }
}
