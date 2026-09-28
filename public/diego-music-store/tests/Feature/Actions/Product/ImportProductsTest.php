<?php

namespace Tests\Feature\Actions\Product;

use App\Actions\Product\ImportProducts;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportProductsTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            [
                'address' => 'Jl. Gajah Mada',
                'phone' => '0561-123456',
                'is_active' => true,
            ]
        );

        Unit::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Pieces', 'is_active' => true]
        );
    }

    public function test_can_import_products_from_csv_file(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL,JLH.STOK
1,BRG2122,MVAVE CHOCOLATE PLUS,EFFECTS,383000,389740,0,535000,535000,5 Pcs
2,BRG2143,STICK DRUM FABULOUS 5A CHN,STICK DRUM,21450,21879,0,45000,45000,24 Pcs
CSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            $action = app(ImportProducts::class);
            $result = $action->execute($tempFile, $this->branch->id);

            $this->assertEquals(2, $result['imported']);
            $this->assertEquals(0, $result['updated']);
            $this->assertEquals(0, $result['failed']);
            $this->assertEmpty($result['errors']);

            // 1. Verify Product 1
            $product1 = Product::where('name', 'MVAVE CHOCOLATE PLUS')->first();
            $this->assertNotNull($product1);
            $this->assertEquals('EFFECTS', $product1->category);
            $this->assertEquals('physical', $product1->type);
            $this->assertNotNull($product1->unit_id);

            $variant1 = ProductVariant::where('sku', 'BRG2122')->first();
            $this->assertNotNull($variant1);
            $this->assertEquals(535000, $variant1->price);
            $this->assertEquals(383000, $variant1->cost_price);
            $this->assertEquals(389740, $variant1->hpp);

            // Barcode must NOT be the same as SKU and must be EAN-13
            $this->assertNotEquals($variant1->sku, $variant1->barcode);
            $this->assertStringStartsWith('899', $variant1->barcode);
            $this->assertEquals(13, strlen($variant1->barcode));

            // Verify Branch Stock
            $branchStock1 = ProductBranchStock::where([
                'product_variant_id' => $variant1->id,
                'branch_id' => $this->branch->id,
            ])->first();
            $this->assertNotNull($branchStock1);
            $this->assertEquals(5, $branchStock1->stock);
            $this->assertEquals(389740, $branchStock1->hpp);

            // Verify Stock Movement
            $movement1 = StockMovement::where([
                'product_variant_id' => $variant1->id,
                'branch_id' => $this->branch->id,
                'type' => 'in',
                'reference_type' => 'InitialStock',
            ])->first();
            $this->assertNotNull($movement1);
            $this->assertEquals(5, $movement1->quantity);

            // 2. Verify Product 2
            $variant2 = ProductVariant::where('sku', 'BRG2143')->first();
            $this->assertNotNull($variant2);
            $this->assertEquals('STICK DRUM FABULOUS 5A CHN', $variant2->product->name);
            $this->assertEquals(45000, $variant2->price);
            $this->assertEquals(21450, $variant2->cost_price);
            $this->assertEquals(21879, $variant2->hpp);

            $this->assertNotEquals($variant2->sku, $variant2->barcode);
            $this->assertStringStartsWith('899', $variant2->barcode);
            $this->assertNotEquals($variant1->barcode, $variant2->barcode);

            $branchStock2 = ProductBranchStock::where([
                'product_variant_id' => $variant2->id,
                'branch_id' => $this->branch->id,
            ])->first();
            $this->assertEquals(24, $branchStock2->stock);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_updates_existing_product_without_overwriting_valid_barcode(): void
    {
        $csvContent1 = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL,JLH.STOK
1,BRG2145,8 CHANNEL UNIVERSAL MIXER,MIXER,281250,286875,0,420000,420000,2 Pcs
CSV;

        $tempFile1 = tempnam(sys_get_temp_dir(), 'import_test_1_') . '.csv';
        file_put_contents($tempFile1, $csvContent1);

        $action = app(ImportProducts::class);
        $action->execute($tempFile1, $this->branch->id);

        $variant = ProductVariant::where('sku', 'BRG2145')->first();
        $originalBarcode = $variant->barcode;

        // Second import with price and stock update
        $csvContent2 = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL,JLH.STOK
1,BRG2145,8 CHANNEL UNIVERSAL MIXER,MIXER,290000,295000,0,450000,450000,10 Pcs
CSV;

        $tempFile2 = tempnam(sys_get_temp_dir(), 'import_test_2_') . '.csv';
        file_put_contents($tempFile2, $csvContent2);

        try {
            $result = $action->execute($tempFile2, $this->branch->id);

            $this->assertEquals(0, $result['imported']);
            $this->assertEquals(1, $result['updated']);
            $this->assertEquals(0, $result['failed']);

            $variant->refresh();
            $this->assertEquals(450000, $variant->price);
            $this->assertEquals(290000, $variant->cost_price);
            $this->assertEquals(295000, $variant->hpp);
            // Barcode should remain the same
            $this->assertEquals($originalBarcode, $variant->barcode);

            $stock = ProductBranchStock::where([
                'product_variant_id' => $variant->id,
                'branch_id' => $this->branch->id,
            ])->first();
            $this->assertEquals(10, $stock->stock);
        } finally {
            if (file_exists($tempFile1)) unlink($tempFile1);
            if (file_exists($tempFile2)) unlink($tempFile2);
        }
    }

    public function test_handles_missing_sku_gracefully(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL,JLH.STOK
1,,BARANG TANPA KODE,EFFECTS,100000,100000,0,150000,150000,1 Pcs
CSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_invalid_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            $action = app(ImportProducts::class);
            $result = $action->execute($tempFile, $this->branch->id);

            $this->assertEquals(0, $result['imported']);
            $this->assertEquals(1, $result['failed']);
            $this->assertStringContainsString('KODE BARANG (SKU) tidak boleh kosong', $result['errors'][0]);
        } finally {
            if (file_exists($tempFile)) unlink($tempFile);
        }
    }

    public function test_records_initial_stock_journal_entry_with_contra_account(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL,JLH.STOK
1,BRG9991,ROLAND KC-200 KEYBOARD AMP,AMPLIFIER,4000000,4000000,0,5500000,5500000,2 Pcs
CSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'import_journal_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            $action = app(ImportProducts::class);
            $result = $action->execute($tempFile, $this->branch->id);

            $this->assertEquals(1, $result['imported']);
            $this->assertEquals(8000000, $result['total_value']);
            $this->assertNotEmpty($result['journal_entry_no']);

            $this->assertDatabaseHas('journal_entries', [
                'id' => $result['journal_entry_id'],
                'reference_type' => 'InitialStock',
                'status' => 'posted',
            ]);

            $journal = \App\Models\JournalEntry::find($result['journal_entry_id']);
            $this->assertNotNull($journal);

            // Total debit = 8,000,000 to Persediaan Barang Dagang
            $debitItem = $journal->items()->where('debit', '>', 0)->first();
            $this->assertEquals(8000000, $debitItem->debit);

            // Total credit = 8,000,000 to Modal Disetor
            $creditItem = $journal->items()->where('credit', '>', 0)->first();
            $this->assertEquals(8000000, $creditItem->credit);
        } finally {
            if (file_exists($tempFile)) unlink($tempFile);
        }
    }

    public function test_can_import_products_with_harga_jual_cash_header(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL CASH,JLH.STOK
1,BRG8888,YAMAHA PACIFICA 112V,GUITAR,2950000,2950000,0,3850000,3850000,3 Pcs
CSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'import_cash_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            $action = app(ImportProducts::class);
            $result = $action->execute($tempFile, $this->branch->id);

            $this->assertEquals(1, $result['imported']);
            $variant = ProductVariant::where('sku', 'BRG8888')->first();
            $this->assertNotNull($variant);
            $this->assertEquals(3850000, $variant->price);
            $this->assertEquals(2950000, $variant->hpp);
        } finally {
            if (file_exists($tempFile)) unlink($tempFile);
        }
    }
}
