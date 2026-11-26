<?php

namespace Tests\Feature\Actions\Product;

use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\RegenerateBranchProductSkus;
use App\Actions\Branch\SyncBranchProductSkusFromMaster;
use App\Actions\Branch\UpdateBranch;
use App\Actions\Product\CreateProduct;
use App\Helpers\ProductHelper;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SkuSequence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SkuSequenceTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchPontianak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchPontianak = Branch::create([
            'name' => 'Diego Music Store Pontianak',
            'sku_prefix' => 'DMS-P',
            'is_active' => true,
        ]);
    }

    public function test_peek_next_sku_does_not_advance_sequence_counter(): void
    {
        $this->assertSame('DMS-P-0001', ProductHelper::peekNextSku('DMS-P'));
        $this->assertSame('DMS-P-0001', ProductHelper::peekNextSku('DMS-P'));
        $this->assertSame('DMS-P-0001', ProductHelper::peekNextSku());
        $this->assertDatabaseCount('sku_sequences', 0);
    }

    public function test_generate_unique_sku_increments_per_prefix_and_ignores_non_numeric_skus(): void
    {
        $branchBandung = Branch::create([
            'name' => 'Diego Music Store Bandung',
            'sku_prefix' => 'DMS-B',
            'is_active' => true,
        ]);

        // Pre-seed non-numeric and branded SKUs that must be ignored when finding max numeric suffix
        $product = Product::create(['name' => 'Existing Catalog', 'type' => 'physical', 'is_active' => true]);
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DMS-P-D0642',
            'barcode' => '200000000101',
            'name' => 'Standard',
            'price' => 100000,
            'is_active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DMS-P-FW-1PK-XL',
            'barcode' => '200000000102',
            'name' => 'Standard 2',
            'price' => 100000,
            'is_active' => true,
        ]);

        $this->assertSame('DMS-P-0001', ProductHelper::generateUniqueSku(null, $this->branchPontianak->id));
        $this->assertSame('DMS-P-0002', ProductHelper::generateUniqueSku('dms-p'));
        $this->assertSame('DMS-B-0001', ProductHelper::generateUniqueSku(null, $branchBandung->id));
        $this->assertSame('DMS-P-0003', ProductHelper::generateUniqueSku(null, $this->branchPontianak->id));

        $this->assertSame(3, SkuSequence::where('prefix', 'DMS-P')->value('last_number'));
        $this->assertSame(1, SkuSequence::where('prefix', 'DMS-B')->value('last_number'));

        $seqP = SkuSequence::where('prefix', 'DMS-P')->firstOrFail();
        $seqB = SkuSequence::where('prefix', 'DMS-B')->firstOrFail();
        $this->assertSame($this->branchPontianak->id, $seqP->branch_id);
        $this->assertSame($branchBandung->id, $seqB->branch_id);
        $this->assertTrue($seqP->branch->is($this->branchPontianak));
        $this->assertTrue($this->branchPontianak->skuSequences()->whereKey($seqP->id)->exists());

        // Standalone non-branch custom prefix works cleanly with nullable branch_id
        $this->assertSame('CUSTOM-TEMP-0001', ProductHelper::generateUniqueSku('CUSTOM-TEMP'));
        $this->assertNull(SkuSequence::where('prefix', 'CUSTOM-TEMP')->value('branch_id'));
    }

    public function test_deleting_last_created_variant_does_not_reuse_its_sku_number(): void
    {
        $p1 = app(CreateProduct::class)->execute([
            'name' => 'Barang Pertama',
            'type' => 'physical',
            'price' => 100000,
            'cost_price' => 50000,
        ]);
        $p2 = app(CreateProduct::class)->execute([
            'name' => 'Barang Kedua',
            'type' => 'physical',
            'price' => 100000,
            'cost_price' => 50000,
        ]);

        $v1 = $p1->variants()->firstOrFail();
        $v2 = $p2->variants()->firstOrFail();

        $this->assertSame('DMS-P-0001', $v1->sku);
        $this->assertSame('DMS-P-0002', $v2->sku);

        $v2->delete();

        $p3 = app(CreateProduct::class)->execute([
            'name' => 'Barang Ketiga',
            'type' => 'physical',
            'price' => 100000,
            'cost_price' => 50000,
        ]);

        $this->assertSame('DMS-P-0003', $p3->variants()->firstOrFail()->sku);
    }

    public function test_creating_product_with_three_blank_sku_variants_assigns_unique_sequential_skus(): void
    {
        $product = app(CreateProduct::class)->execute([
            'name' => 'Senar Gitar Elixir',
            'type' => 'physical',
            'has_variants' => true,
            'variants' => [
                ['name' => '09-42', 'sku' => '', 'price' => 210000, 'cost_price' => 150000],
                ['name' => '10-46', 'sku' => null, 'price' => 210000, 'cost_price' => 150000],
                ['name' => '11-52', 'sku' => '   ', 'price' => 220000, 'cost_price' => 155000],
            ],
        ]);

        $skus = $product->variants()->orderBy('id')->pluck('sku')->all();
        $this->assertSame(['DMS-P-0001', 'DMS-P-0002', 'DMS-P-0003'], $skus);
        $this->assertSame(3, SkuSequence::where('prefix', 'DMS-P')->value('last_number'));
    }

    public function test_manual_higher_branch_sku_advances_sequence_while_custom_non_branch_sku_is_ignored(): void
    {
        app(CreateProduct::class)->execute([
            'name' => 'Produk Manual Lebih Tinggi',
            'type' => 'physical',
            'sku' => 'DMS-P-2250',
            'price' => 120000,
            'cost_price' => 70000,
        ]);

        $this->assertSame(2250, SkuSequence::where('prefix', 'DMS-P')->value('last_number'));
        $this->assertSame('DMS-P-2251', ProductHelper::peekNextSku('DMS-P'));

        app(CreateProduct::class)->execute([
            'name' => 'Produk Kode Kustom Non Cabang',
            'type' => 'physical',
            'sku' => 'SKU-CUSTOM-999',
            'price' => 120000,
            'cost_price' => 70000,
        ]);

        $this->assertNull(SkuSequence::where('prefix', 'SKU-CUSTOM')->first());
        $this->assertSame('DMS-P-2251', ProductHelper::generateUniqueSku('DMS-P'));
    }

    public function test_sync_from_master_updates_sequence_and_reflected_on_existing_sale_items(): void
    {
        $user = User::factory()->create();

        $vNut = $this->createCatalogVariant('HOSCO BONE NUT', 'DMS-P-RND10001', '899000001001');
        $vAmp = $this->createCatalogVariant('ROLAND CUBE STREET', 'DMS-P-RND10002', '899000001002');
        $vCowboy = $this->createCatalogVariant('COWBOY GWC 235 EQ NS', 'DMS-P-RND10003', '899000001003');
        $vPick = $this->createCatalogVariant('FENDER PICK EXTRA HEAVY', 'DMS-P-RND10004', '899000001004');

        // Historical POS sale created while SKU was still random
        $sale = Sale::create([
            'branch_id' => $this->branchPontianak->id,
            'sales_rep_id' => $user->id,
            'invoice_number' => 'INV-TEST-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 250000,
            'paid_amount' => 250000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
            'sale_category' => 'Umum',
            'created_by' => $user->id,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $vAmp->id,
            'quantity' => 1,
            'unit_price' => 250000,
            'discount_amount' => 0,
            'total_price' => 250000,
            'sales_rep_id' => $user->id,
        ]);

        $csvContent = implode("\n", [
            'NO,KODE BARANG,NAMA STOK',
            '1,BRG2122,HOSCO BONE NUT',
            '2,BRG2229,ROLAND CUBE STREET',
            '3,DG0642,COWBOY GWC 235 EQ NS',
            '4,FW-1PK-XL,FENDER PICK EXTRA HEAVY',
        ]);

        $tempPath = sys_get_temp_dir() . '/sku_seq_master_' . uniqid('', true) . '.csv';
        file_put_contents($tempPath, $csvContent);

        try {
            $result = app(SyncBranchProductSkusFromMaster::class)->execute(
                branch: $this->branchPontianak,
                filePath: $tempPath,
                prefixOverride: 'DMS-P',
                savePrefixToBranch: true,
            );

            $this->assertSame(4, $result['updated']);
            $this->assertSame('DMS-P-2122', $vNut->fresh()->sku);
            $this->assertSame('DMS-P-2229', $vAmp->fresh()->sku);
            $this->assertSame('DMS-P-D0642', $vCowboy->fresh()->sku);
            $this->assertSame('DMS-P-FW-1PK-XL', $vPick->fresh()->sku);

            // Relational check: sale_items reflects the restored SKU automatically
            $this->assertSame('DMS-P-2229', $saleItem->fresh()->variant->sku);

            // Counter automatically synced to 2229 so next new product becomes DMS-P-2230
            $this->assertSame(2229, SkuSequence::where('prefix', 'DMS-P')->value('last_number'));
            $this->assertSame($this->branchPontianak->id, SkuSequence::where('prefix', 'DMS-P')->value('branch_id'));
            $nextProduct = app(CreateProduct::class)->execute([
                'name' => 'Gitar Baru Setelah Restore',
                'type' => 'physical',
                'price' => 1800000,
                'cost_price' => 1200000,
            ]);
            $this->assertSame('DMS-P-2230', $nextProduct->variants()->firstOrFail()->sku);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_regenerate_branch_skus_and_branch_floor_sync_sequence_counter(): void
    {
        $this->createCatalogVariant('Ampli Baru', 'DMS-P-2229', '899000002001');

        app(RegenerateBranchProductSkus::class)->execute(
            branch: $this->branchPontianak,
            prefixOverride: 'DMS-B',
            mode: RegenerateBranchProductSkus::MODE_ALL_OVERWRITE,
            format: RegenerateBranchProductSkus::FORMAT_KEEP_SUFFIX,
            savePrefixToBranch: true,
        );

        $this->assertSame(2229, SkuSequence::where('prefix', 'DMS-B')->value('last_number'));
        $this->assertSame($this->branchPontianak->id, SkuSequence::where('prefix', 'DMS-B')->value('branch_id'));
        $this->assertSame('DMS-B-2230', ProductHelper::generateUniqueSku('DMS-B'));

        // Create a fresh branch with explicit starting floor number and bump via UpdateBranch
        $newBranch = CreateBranch::execute([
            'name' => 'Cabang Singkawang',
            'sku_prefix' => 'DMS-S',
            'sku_last_number' => 100,
        ]);

        $this->assertSame(100, SkuSequence::where('prefix', 'DMS-S')->value('last_number'));
        $this->assertSame($newBranch->id, SkuSequence::where('prefix', 'DMS-S')->value('branch_id'));
        $this->assertSame('DMS-S-0101', ProductHelper::generateUniqueSku(null, $newBranch->id));

        UpdateBranch::execute($newBranch, [
            'sku_prefix' => 'DMS-S',
            'sku_last_number' => 250,
        ]);

        $this->assertSame(250, SkuSequence::where('prefix', 'DMS-S')->value('last_number'));
        $this->assertSame($newBranch->id, SkuSequence::where('prefix', 'DMS-S')->value('branch_id'));
        $this->assertSame('DMS-S-0251', ProductHelper::generateUniqueSku(null, $newBranch->id));

        // Direct DB insert into migration backfill consistency check: schema table exists cleanly
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('sku_sequences'));
    }

    private function createCatalogVariant(string $name, string $sku, string $barcode): ProductVariant
    {
        $product = Product::create([
            'name' => $name,
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'barcode' => $barcode,
            'name' => 'Standard',
            'price' => 250000,
            'cost_price' => 150000,
            'hpp' => 150000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branchPontianak->id,
            'stock' => 5,
            'hpp' => 150000,
        ]);

        return $variant;
    }
}

