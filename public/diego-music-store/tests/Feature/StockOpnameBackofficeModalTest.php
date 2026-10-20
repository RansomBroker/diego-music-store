<?php

namespace Tests\Feature;

use App\Filament\Resources\StockOpnamePeriods\Pages\ListStockOpnamePeriods;
use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use App\Filament\Resources\StockOpnamePresets\Pages\ListStockOpnamePresets;
use App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource;
use App\Models\Branch;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameBackofficeModalTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $adminUser;

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
        $this->adminUser->employee()->update([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_resources_only_have_index_route_for_modal_crud(): void
    {
        $presetPages = StockOpnamePresetResource::getPages();
        $this->assertCount(1, $presetPages);
        $this->assertArrayHasKey('index', $presetPages);
        $this->assertArrayNotHasKey('create', $presetPages);
        $this->assertArrayNotHasKey('edit', $presetPages);

        $periodPages = StockOpnamePeriodResource::getPages();
        $this->assertCount(1, $periodPages);
        $this->assertArrayHasKey('index', $periodPages);
        $this->assertArrayNotHasKey('create', $periodPages);
        $this->assertArrayNotHasKey('edit', $periodPages);
    }

    public function test_can_render_preset_list_page_and_create_preset_via_modal(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        Livewire::actingAs($this->adminUser)
            ->test(ListStockOpnamePresets::class)
            ->assertSuccessful()
            ->callAction(CreateAction::class, [
                'branch_id' => $this->branch->id,
                'name' => 'Gudang Belakang',
                'description' => 'Area stok utama',
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('stock_opname_presets', [
            'branch_id' => $this->branch->id,
            'name' => 'Gudang Belakang',
        ]);
    }

    public function test_can_activate_and_cancel_period_from_table_actions(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        $preset = StockOpnamePreset::create([
            'branch_id' => $this->branch->id,
            'name' => 'Lantai 1',
            'is_active' => true,
        ]);

        $period = StockOpnamePeriod::create([
            'branch_id' => $this->branch->id,
            'period' => '2026-10-01',
            'name' => 'SO Oktober 2026',
            'status' => StockOpnamePeriod::STATUS_DRAFT,
            'created_by' => $this->adminUser->id,
        ]);

        StockOpnameAssignment::create([
            'stock_opname_period_id' => $period->id,
            'stock_opname_preset_id' => $preset->id,
            'assigned_user_id' => $this->adminUser->id,
            'assigned_by' => $this->adminUser->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListStockOpnamePeriods::class)
            ->assertSuccessful()
            ->callTableAction('activate', $period)
            ->assertHasNoTableActionErrors();

        $this->assertSame(StockOpnamePeriod::STATUS_ACTIVE, $period->fresh()->status);

        Livewire::actingAs($this->adminUser)
            ->test(ListStockOpnamePeriods::class)
            ->callTableAction('cancel', $period)
            ->assertHasNoTableActionErrors();

        $this->assertSame(StockOpnamePeriod::STATUS_CANCELLED, $period->fresh()->status);
    }
}
