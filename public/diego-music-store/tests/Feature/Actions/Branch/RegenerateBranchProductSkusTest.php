<?php

namespace Tests\Feature\Actions\Branch;

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\RegenerateBranchProductSkus;
use App\Actions\Branch\UpdateBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegenerateBranchProductSkusTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchA = Branch::create([
            'name' => 'Cabang Pontianak',
            'store_name' => 'Diego Music PTK',
            'sku_prefix' => 'PTK',
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'name' => 'Cabang Singkawang',
            'store_name' => 'Diego Music SKW',
            'sku_prefix' => 'SKW',
            'is_active' => true,
        ]);
    }

    public function test_regenerates_missing_prefix_skus_with_random_format_and_preserves_barcode(): void
    {
        $vLegacy = $this->createVariant('Gitar Akustik', 'BRG2122', '8990000000011', [$this->branchA->id => 5]);
        $vOldDg = $this->createVariant('Pick Gitar', 'DG0642', '8990000000022', [$this->branchA->id => 0]);
        $vAlreadyPrefixed = $this->createVariant('Senar Gitar', 'PTK-EXISTING1', '8990000000033', [$this->branchA->id => 2]);

        $result = app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchA,
            prefixOverride: 'ptk ',
            scope: RegenerateBranchProductSkus::SCOPE_ALL,
            mode: RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
            format: RegenerateBranchProductSkus::FORMAT_RANDOM,
            savePrefixToBranch: true,
        );

        $this->assertSame('PTK', $result['prefix']);
        $this->assertSame(2, $result['updated']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(3, $result['total']);
        $this->assertCount(2, $result['sample_skus']);

        $vLegacy->refresh();
        $vOldDg->refresh();
        $vAlreadyPrefixed->refresh();

        $this->assertMatchesRegularExpression('/^PTK-[A-Z0-9]{8}$/', $vLegacy->sku);
        $this->assertMatchesRegularExpression('/^PTK-[A-Z0-9]{8}$/', $vOldDg->sku);
        $this->assertNotSame($vLegacy->sku, $vOldDg->sku);
        $this->assertSame('8990000000011', $vLegacy->barcode);
        $this->assertSame('8990000000022', $vOldDg->barcode);
        $this->assertSame('PTK-EXISTING1', $vAlreadyPrefixed->sku);
        $this->assertSame('8990000000033', $vAlreadyPrefixed->barcode);
    }

    public function test_regenerates_all_overwrite_with_sequential_format_without_unique_collision(): void
    {
        // Reverse existing sequential SKUs to trigger a collision if updated naively in a single phase
        $v1 = $this->createVariant('Produk 1', 'PTK-0002', '8990000000101', [$this->branchA->id => 1]);
        $v2 = $this->createVariant('Produk 2', 'PTK-0001', '8990000000102', [$this->branchA->id => 1]);
        $v3 = $this->createVariant('Produk 3', 'BRG9999', '8990000000103', [$this->branchA->id => 1]);

        $result = app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchA,
            prefixOverride: 'PTK',
            scope: RegenerateBranchProductSkus::SCOPE_ALL,
            mode: RegenerateBranchProductSkus::MODE_ALL_OVERWRITE,
            format: RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        );

        $this->assertSame(3, $result['updated']);
        $this->assertSame(0, $result['skipped']);

        $v1->refresh();
        $v2->refresh();
        $v3->refresh();

        $this->assertSame('PTK-0001', $v1->sku);
        $this->assertSame('PTK-0002', $v2->sku);
        $this->assertSame('PTK-0003', $v3->sku);
        $this->assertSame('8990000000101', $v1->barcode);
    }

    public function test_respects_scope_filtering_for_branch_stock_positive_stock_and_all(): void
    {
        $vPos = $this->createVariant('Stok Positif A', 'BRG0001', '8990000000201', [$this->branchA->id => 4]);
        $vZero = $this->createVariant('Stok Nol A', 'BRG0002', '8990000000202', [$this->branchA->id => 0]);
        $vOtherBranch = $this->createVariant('Milik Cabang B', 'BRG0003', '8990000000203', [$this->branchB->id => 8]);
        $vServiceNoStock = $this->createVariant('Jasa Service', 'BRG0004', '8990000000204', [], 'service');

        // 1. Positive stock only in branchA
        $resPos = app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchA,
            prefixOverride: 'POS',
            scope: RegenerateBranchProductSkus::SCOPE_BRANCH_POSITIVE_STOCK,
            mode: RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
            format: RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        );

        $this->assertSame(1, $resPos['updated']);
        $this->assertSame('POS-0001', $vPos->fresh()->sku);
        $this->assertSame('BRG0002', $vZero->fresh()->sku);
        $this->assertSame('BRG0003', $vOtherBranch->fresh()->sku);
        $this->assertSame('BRG0004', $vServiceNoStock->fresh()->sku);

        // 2. Branch stock in branchA (includes zero stock, preserves POS-0001 when mode is missing_prefix on same prefix or overwrites on new prefix)
        $resBranch = app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchA,
            prefixOverride: 'BRCA',
            scope: RegenerateBranchProductSkus::SCOPE_BRANCH_STOCK,
            mode: RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
            format: RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        );

        $this->assertSame(2, $resBranch['updated']);
        $this->assertSame('BRCA-0001', $vPos->fresh()->sku);
        $this->assertSame('BRCA-0002', $vZero->fresh()->sku);
        $this->assertSame('BRG0003', $vOtherBranch->fresh()->sku);
        $this->assertSame('BRG0004', $vServiceNoStock->fresh()->sku);

        // 3. Scope ALL includes branchB and service without branchStocks
        $resAll = app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchA,
            prefixOverride: 'ALL',
            scope: RegenerateBranchProductSkus::SCOPE_ALL,
            mode: RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
            format: RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        );

        $this->assertSame(4, $resAll['updated']);
        $this->assertSame('ALL-0001', $vPos->fresh()->sku);
        $this->assertSame('ALL-0002', $vZero->fresh()->sku);
        $this->assertSame('ALL-0003', $vOtherBranch->fresh()->sku);
        $this->assertSame('ALL-0004', $vServiceNoStock->fresh()->sku);
    }

    public function test_create_branch_and_update_branch_only_regenerate_when_toggle_is_enabled(): void
    {
        $variant = $this->createVariant('Amplifier', 'BRG7777', '8990000000301', [$this->branchA->id => 3]);

        // CreateBranch with regenerate_skus_on_save = false must keep existing SKU intact
        $noRegenResult = null;
        $branchNoRegen = CreateBranch::execute([
            'name' => 'Cabang Baru 1',
            'sku_prefix' => 'CB1',
            'regenerate_skus_on_save' => false,
        ], $noRegenResult);

        $this->assertNull($noRegenResult);
        $this->assertSame('BRG7777', $variant->fresh()->sku);

        // CreateBranch with regenerate_skus_on_save = true regenerates SKUs
        $createResult = null;
        $branchWithRegen = CreateBranch::execute([
            'name' => 'Cabang Baru 2',
            'sku_prefix' => 'CB2',
            'regenerate_skus_on_save' => true,
            'sku_regenerate_scope' => RegenerateBranchProductSkus::SCOPE_ALL,
            'sku_regenerate_mode' => RegenerateBranchProductSkus::MODE_MISSING_PREFIX,
            'sku_regenerate_format' => RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        ], $createResult);

        $this->assertNotNull($createResult);
        $this->assertSame(1, $createResult['updated']);
        $this->assertSame('CB2-0001', $variant->fresh()->sku);

        // UpdateBranch with regenerate_skus_on_save = true overwrites to new prefix
        $updateResult = null;
        UpdateBranch::execute($branchWithRegen, [
            'name' => 'Cabang Baru 2 Revisi',
            'sku_prefix' => 'CB2R',
            'regenerate_skus_on_save' => true,
            'sku_regenerate_scope' => RegenerateBranchProductSkus::SCOPE_ALL,
            'sku_regenerate_mode' => RegenerateBranchProductSkus::MODE_ALL_OVERWRITE,
            'sku_regenerate_format' => RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
        ], $updateResult);

        $this->assertNotNull($updateResult);
        $this->assertSame(1, $updateResult['updated']);
        $this->assertSame('CB2R-0001', $variant->fresh()->sku);
        $this->assertSame('CB2R', $branchWithRegen->fresh()->sku_prefix);
        $this->assertSame('CB1', $branchNoRegen->fresh()->sku_prefix);
    }

    public function test_backoffice_list_branches_table_action_regenerates_skus(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $this->actingAs($owner);

        $v1 = $this->createVariant('Gitar Listrik', 'BRG3001', '8990000000401', [$this->branchA->id => 2]);
        $v2 = $this->createVariant('Efek Gitar', 'BRG3002', '8990000000402', [$this->branchA->id => 1]);

        Livewire::test(ListBranches::class)
            ->assertTableActionVisible('regenerate_skus', $this->branchA)
            ->callTableAction('regenerate_skus', $this->branchA, data: [
                'sku_prefix' => 'DG-PTK',
                'scope' => RegenerateBranchProductSkus::SCOPE_BRANCH_STOCK,
                'mode' => RegenerateBranchProductSkus::MODE_ALL_OVERWRITE,
                'format' => RegenerateBranchProductSkus::FORMAT_SEQUENTIAL,
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified();

        $this->assertSame('DG-PTK', $this->branchA->fresh()->sku_prefix);
        $this->assertSame('DG-PTK-0001', $v1->fresh()->sku);
        $this->assertSame('DG-PTK-0002', $v2->fresh()->sku);
    }

    /**
     * Create a test product variant with optional per-branch stock rows.
     *
     * @param  array<int, int>  $branchStocks
     */
    private function createVariant(
        string $name,
        string $sku,
        string $barcode,
        array $branchStocks = [],
        string $type = 'physical'
    ): ProductVariant {
        $product = Product::create([
            'name' => $name,
            'type' => $type,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'barcode' => $barcode,
            'name' => 'Standard',
            'price' => 100000,
            'cost_price' => 50000,
            'hpp' => 50000,
            'is_active' => true,
        ]);

        foreach ($branchStocks as $branchId => $qty) {
            ProductBranchStock::create([
                'product_variant_id' => $variant->id,
                'branch_id' => $branchId,
                'stock' => $qty,
                'hpp' => 50000,
            ]);
        }

        return $variant;
    }
}

