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
}
