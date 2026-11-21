<?php

namespace Tests\Feature\Filament;

use App\Livewire\Backoffice\SpreadsheetImporter;
use App\Models\Customer;
use App\Models\PricingTier;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SpreadsheetImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PricingTier::firstOrCreate(['name' => 'Umum / Retail']);
    }

    public function test_component_mounts_correctly_for_customer_and_supplier(): void
    {
        Livewire::test(SpreadsheetImporter::class, ['type' => 'customer'])
            ->assertSet('type', 'customer')
            ->assertCount('requiredHeaders', 10)
            ->assertSee('Template Resmi Import Pelanggan');

        Livewire::test(SpreadsheetImporter::class, ['type' => 'supplier'])
            ->assertSet('type', 'supplier')
            ->assertCount('requiredHeaders', 9)
            ->assertSee('Template Resmi Import Supplier');
    }

    public function test_component_detects_invalid_columns_and_blocks_import(): void
    {
        // Create an invalid Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['nama_lengkap', 'no_telepon'], // Wrong headers
            ['Budi', '08123456789']
        ]);

        $filePath = storage_path('app/temp_test_invalid.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'customer'])
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Worksheet');

        $validation = $comp->get('validation');
        $this->assertFalse($validation['is_valid']);
        $this->assertContains('name', $validation['missing']);
        $this->assertContains('phone', $validation['missing']);

        // Attempting import when invalid should not import
        $comp->call('startImport')
            ->assertSet('isImporting', false);

        @unlink($filePath);
    }

    public function test_component_multi_sheet_selection_and_import(): void
    {
        // Create multi-sheet Excel
        $spreadsheet = new Spreadsheet();

        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Sheet Info');
        $sheet1->fromArray([
            ['judul', 'keterangan'],
            ['Data Toko', 'Cabang Utama']
        ]);

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Data Pelanggan');
        $sheet2->fromArray([
            ['name', 'phone', 'email', 'address', 'date_of_birth', 'customer_label', 'pricing_tier', 'is_loyalty_member', 'loyalty_points', 'outstanding_debt'],
            ['Ahmad Dhani', '0811999901', 'dhani@dewa19.com', 'Jl. Pinang Emas', '1972-05-26', 'VIP', 'Umum / Retail', 1, 100, 0],
            ['Andra Ramadhan', '0811999902', 'andra@dewa19.com', 'Jl. Kemang', '1972-06-17', 'Musisi', 'Umum / Retail', 1, 50, 0]
        ]);

        $filePath = storage_path('app/temp_test_multisheet.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'customer'])
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Sheet Info');

        // Sheet 1 is invalid for customer
        $this->assertFalse($comp->get('validation')['is_valid']);

        // Switch to Sheet 2
        $comp->call('selectSheet', 'Data Pelanggan');
        $this->assertTrue($comp->get('validation')['is_valid']);
        $this->assertEquals(2, $comp->get('totalRows'));

        // Start import and process batch
        $comp->call('startImport')
            ->assertSet('isImporting', true);

        $comp->call('processBatch')
            ->assertSet('importFinished', true)
            ->assertSet('importedCount', 2)
            ->assertSet('progressPercent', 100);

        $this->assertEquals(1, Customer::where('phone', '0811999901')->count());
        $this->assertEquals(1, Customer::where('phone', '0811999902')->count());

        @unlink($filePath);
    }

    public function test_component_supplier_import(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'contact_person', 'phone', 'email', 'address', 'bank_name', 'bank_account_number', 'bank_account_name', 'outstanding_debt'],
            ['PT Supplier Test', 'Pak Joko', '02199881122', 'joko@suppliertest.com', 'Jl. Industri', 'BCA', '11223344', 'PT Supplier Test', 10000000]
        ]);

        $filePath = storage_path('app/temp_test_supplier.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        Livewire::test(SpreadsheetImporter::class, ['type' => 'supplier'])
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Worksheet')
            ->assertSet('validation.is_valid', true)
            ->assertSet('totalRows', 1)
            ->call('startImport')
            ->call('processBatch')
            ->assertSet('importedCount', 1)
            ->assertSet('importFinished', true);

        $this->assertEquals(1, Supplier::where('phone', '02199881122')->count());

        @unlink($filePath);
    }

    public function test_component_mounts_and_imports_product_batches_with_progress_bar(): void
    {
        $branch = \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['kode_barang', 'nama_stok', 'kategori_barang', 'harga_beli', 'netto', 'disc', 'het', 'harga_jual', 'jlh_stok'],
            ['BRG2122', 'MVAVE CHOCOLATE PLUS', 'EFFECTS', 383000, 389740, 0, 535000, 535000, '5 Pcs'],
            ['BRG2143', 'STICK DRUM FABULOUS 5A CHN', 'STICK DRUM', 21450, 21879, 0, 45000, 45000, '24 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_product.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
            ->assertSet('type', 'product')
            ->assertCount('requiredHeaders', 9)
            ->assertSee('Template Resmi Import Produk & Stok Awal')
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Worksheet')
            ->assertSet('validation.is_valid', true)
            ->assertSet('totalRows', 2)
            ->call('startImport')
            ->assertSet('isImporting', true)
            ->assertSet('progressPercent', 0)
            ->call('processBatch')
            ->assertSet('importedCount', 2)
            ->assertSet('progressPercent', 100)
            ->assertSet('importFinished', true);

        $this->assertEquals(1, \App\Models\ProductVariant::where('sku', 'BRG2122')->count());
        $this->assertEquals(1, \App\Models\ProductVariant::where('sku', 'BRG2143')->count());

        $variant = \App\Models\ProductVariant::where('sku', 'BRG2122')->first();
        $this->assertNotEquals($variant->sku, $variant->barcode);
        $this->assertStringStartsWith('899', $variant->barcode);

        // Verify Automatic Initial Stock Journal Entry
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'InitialStock',
            'status' => 'posted',
        ]);

        $journal = \App\Models\JournalEntry::where('reference_type', 'InitialStock')->first();
        $this->assertNotNull($journal);
        $this->assertNotEmpty($journal->entry_no);
        $this->assertEquals(2, $journal->items()->count());

        $debitItem = $journal->items()->where('debit', '>', 0)->first();
        $this->assertNotNull($debitItem);
        $this->assertEquals(2473796, $debitItem->debit);
        $this->assertEquals(0, $debitItem->credit);

        $creditItem = $journal->items()->where('credit', '>', 0)->first();
        $this->assertNotNull($creditItem);
        $this->assertEquals(2473796, $creditItem->credit);
        $this->assertEquals(0, $creditItem->debit);

        @unlink($filePath);
    }

    public function test_component_supports_harga_jual_cash_column_alias(): void
    {
        \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NO', 'KODE BARANG', 'NAMA STOK', 'KATEGORI BARANG', 'Harga Beli', 'Netto', 'Disc', 'HET', 'HARGA JUAL CASH', 'JLH.STOK'],
            [1, 'BRG3001', 'FENDER STRATOCASTER', 'GUITAR', 12000000, 12500000, 0, 15000000, 15000000, '2 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_alias.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Worksheet')
            ->assertSet('validation.is_valid', true)
            ->assertSet('totalRows', 1)
            ->call('startImport')
            ->call('processBatch')
            ->assertSet('importedCount', 1)
            ->assertSet('importFinished', true);

        $variant = \App\Models\ProductVariant::where('sku', 'BRG3001')->first();
        $this->assertNotNull($variant);
        $this->assertEquals(15000000, $variant->price);
        $this->assertEquals(12500000, $variant->hpp);

        @unlink($filePath);
    }

    public function test_component_can_import_supplier_debts_from_excel_file_and_create_journal(): void
    {
        \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );
        \App\Helpers\AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability');
        \App\Helpers\AccountHelper::resolveAccountId('311101001', 'MODAL DISETOR', 'equity');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['No', 'Supplier', 'Tanggal', 'Nota', 'Project', 'Total Hutang', 'Total Pembayaran', 'Sisa Hutang'],
            [1, 'BORNEO MUSIKA JAYA', '25-07-2026', 'BMJ/250726', 'TOKO', 166.89, 0, 166.89],
            ['', '', '27-07-2026', 'BMJ/270726', 'TOKO', 163.9, 0, 163.9],
            ['', '', '', '', '', 'Total :', 330.79, ''],
            [2, 'CV ANEKA MUSIKA STUDIO', '25-09-2026', 'NP/26-00321', 'TOKO', '40.041.400', 0, '40.041.400'],
        ]);

        $filePath = storage_path('app/temp_test_supplier_debt.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $test = Livewire::test(SpreadsheetImporter::class, ['type' => 'supplier_debt'])
            ->set('filePath', $filePath)
            ->call('selectSheet', 'Worksheet')
            ->assertSet('validation.is_valid', true)
            ->call('startImport')
            ->call('processBatch')
            ->assertSet('importedCount', 3)
            ->assertSet('importFinished', true);

        // Verify transactions
        $pt1 = \App\Models\PurchaseTransaction::where('invoice_number', 'BMJ/250726')->first();
        $this->assertNotNull($pt1);
        $this->assertEquals(166890, $pt1->grand_total);
        $this->assertEquals('BORNEO MUSIKA JAYA', $pt1->supplier->name);

        $pt2 = \App\Models\PurchaseTransaction::where('invoice_number', 'BMJ/270726')->first();
        $this->assertNotNull($pt2);
        $this->assertEquals(163900, $pt2->grand_total);
        $this->assertEquals('BORNEO MUSIKA JAYA', $pt2->supplier->name);

        $pt3 = \App\Models\PurchaseTransaction::where('invoice_number', 'NP/26-00321')->first();
        $this->assertNotNull($pt3);
        $this->assertEquals(40041400, $pt3->grand_total);

        // Verify Journal Entry for Option 1
        $journal = \App\Models\JournalEntry::where('reference_type', 'InitialDebt')->first();
        $this->assertNotNull($journal);
        $this->assertEquals(40372190, $journal->items()->where('credit', '>', 0)->first()?->credit);

        @unlink($filePath);
    }

    public function test_smart_product_header_detection_with_title_rows_and_shorthand_headers(): void
    {
        \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['DAFTAR STOK BARANG DIEGO MUSIC'],
            ['Per Tanggal: 21-11-2026'],
            [],
            ['NO', 'KODE BARANG', 'NAMA STOK', 'KATEGORI BARANG', 'Harga Beli', 'Netto', 'Disc', 'HET', 'HARGA JUAL CASH', 'JLH.STOK'],
            [1, 'SMART-01', 'YAMAHA F310', 'GUITAR', 1100000, 1150000, 0, 1600000, 1550000, '4 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_smart_banner.xlsx');
        (new Xlsx($spreadsheet))->save($filePath);

        try {
            $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
                ->set('filePath', $filePath)
                ->call('selectSheet', 'Worksheet')
                ->assertSet('headerRowIndex', 4)
                ->assertSet('validation.is_valid', true)
                ->assertSet('totalRows', 1);

            $comp->call('startImport')
                ->call('processBatch')
                ->assertSet('importedCount', 1)
                ->assertSet('importFinished', true);

            $variant = \App\Models\ProductVariant::where('sku', 'SMART-01')->first();
            $this->assertNotNull($variant);
            $this->assertEquals(1550000, $variant->price);
            $this->assertEquals(1150000, $variant->hpp);
        } finally {
            @unlink($filePath);
        }
    }

    public function test_manual_column_mapping_override_resolves_custom_headers(): void
    {
        \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Kol A', 'Kol B', 'Kol C', 'Kol D', 'Kol E', 'Kol F', 'Kol G', 'Kol H', 'Kol I'],
            ['MAN-01', 'EFFECT BOSS DS1', 'PEDAL', 700000, 720000, 0, 990000, 950000, '2 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_manual_map.xlsx');
        (new Xlsx($spreadsheet))->save($filePath);

        try {
            $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
                ->set('filePath', $filePath)
                ->call('selectSheet', 'Worksheet')
                ->assertSet('validation.is_valid', false)
                ->set('manualMappings', [
                    'kode_barang' => 'A',
                    'nama_stok' => 'B',
                    'kategori_barang' => 'C',
                    'harga_beli' => 'D',
                    'netto' => 'E',
                    'disc' => 'F',
                    'het' => 'G',
                    'harga_jual' => 'H',
                    'jlh_stok' => 'I',
                ])
                ->assertSet('validation.is_valid', true)
                ->call('startImport')
                ->call('processBatch')
                ->assertSet('importedCount', 1)
                ->assertSet('importFinished', true);

            $variant = \App\Models\ProductVariant::where('sku', 'MAN-01')->first();
            $this->assertNotNull($variant);
            $this->assertEquals('EFFECT BOSS DS1', $variant->product->name);
            $this->assertEquals(950000, $variant->price);
        } finally {
            @unlink($filePath);
        }
    }

    public function test_reset_before_import_checkbox_deletes_old_products_and_recreates_fresh_data(): void
    {
        $branch = \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $oldProduct = \App\Models\Product::create([
            'name' => 'PRODUK LAMA AKAN DIHAPUS',
            'type' => 'physical',
            'is_active' => true,
        ]);
        \App\Models\ProductVariant::create([
            'product_id' => $oldProduct->id,
            'sku' => 'OLD-SKU-999',
            'price' => 100000,
            'cost_price' => 50000,
            'hpp' => 50000,
            'is_active' => true,
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NO', 'KODE BARANG', 'NAMA STOK', 'KATEGORI BARANG', 'Harga Beli', 'Netto', 'Disc', 'HET', 'HARGA JUAL CASH', 'JLH.STOK'],
            [1, 'NEW-SKU-001', 'PRODUK BARU HASIL RESET', 'GUITAR', 2000000, 2000000, 0, 2750000, 2700000, '3 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_reset_import.xlsx');
        (new Xlsx($spreadsheet))->save($filePath);

        try {
            $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
                ->set('filePath', $filePath)
                ->call('selectSheet', 'Worksheet')
                ->set('resetBeforeImport', true)
                ->call('startImport')
                ->assertSet('isImporting', true)
                ->call('processBatch')
                ->assertSet('importedCount', 1)
                ->assertSet('importFinished', true);

            $this->assertEquals(1, $comp->get('resetSummary')['deleted_products'] ?? 0);
            $this->assertNull(\App\Models\ProductVariant::where('sku', 'OLD-SKU-999')->first());

            $newVariant = \App\Models\ProductVariant::where('sku', 'NEW-SKU-001')->first();
            $this->assertNotNull($newVariant);
            $this->assertEquals('PRODUK BARU HASIL RESET', $newVariant->product->name);
            $this->assertEquals(2700000, $newVariant->price);
            $this->assertEquals(3, $newVariant->stockForBranch($branch->id));
        } finally {
            @unlink($filePath);
        }
    }

    public function test_livewire_product_import_excludes_ongkos_from_initial_stock_valuation(): void
    {
        $branch = \App\Models\Branch::firstOrCreate(
            ['name' => 'Cabang Pusat'],
            ['address' => 'Jl. Gajah Mada', 'phone' => '0561-123456', 'is_active' => true]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NO', 'KODE BARANG', 'NAMA STOK', 'KATEGORI BARANG', 'Harga Beli', 'Netto', 'Disc', 'HET', 'HARGA JUAL CASH', 'JLH.STOK'],
            [1, 'PHY-001', 'SENAR DADDARIO', 'STRINGS', 80000, 80000, 0, 120000, 115000, '5 Pcs'],
            [2, 'ONG-002', 'ONGKOS SETTING GITAR', 'ONGKOS', 50000, 50000, 0, 100000, 100000, '99 Pcs'],
        ]);

        $filePath = storage_path('app/temp_test_ongkos_batch.xlsx');
        (new Xlsx($spreadsheet))->save($filePath);

        try {
            $comp = Livewire::test(SpreadsheetImporter::class, ['type' => 'product'])
                ->set('filePath', $filePath)
                ->call('selectSheet', 'Worksheet')
                ->call('startImport')
                ->call('processBatch')
                ->assertSet('importedCount', 2)
                ->assertSet('totalStockValue', 400000)
                ->assertSet('importFinished', true);

            $ongkosVariant = \App\Models\ProductVariant::where('sku', 'ONG-002')->first();
            $this->assertNotNull($ongkosVariant);
            $this->assertEquals('service', $ongkosVariant->product->type);
            $this->assertDatabaseMissing('product_branch_stocks', [
                'product_variant_id' => $ongkosVariant->id,
                'branch_id' => $branch->id,
            ]);
            $this->assertDatabaseMissing('stock_movements', [
                'product_variant_id' => $ongkosVariant->id,
                'branch_id' => $branch->id,
            ]);
        } finally {
            @unlink($filePath);
        }
    }
}


