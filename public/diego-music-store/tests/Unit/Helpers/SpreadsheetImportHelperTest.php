<?php

namespace Tests\Unit\Helpers;

use App\Actions\Customer\ImportCustomers;
use App\Actions\Supplier\ImportSuppliers;
use App\Helpers\SpreadsheetImportHelper;
use Tests\TestCase;

class SpreadsheetImportHelperTest extends TestCase
{
    public function test_normalizes_header_strings_properly(): void
    {
        $this->assertEquals('name', SpreadsheetImportHelper::normalizeHeader('Name'));
        $this->assertEquals('date_of_birth', SpreadsheetImportHelper::normalizeHeader('Date of Birth'));
        $this->assertEquals('bank_account_number', SpreadsheetImportHelper::normalizeHeader('Bank Account Number '));
        $this->assertEquals('outstanding_debt', SpreadsheetImportHelper::normalizeHeader(' outstanding-debt '));
        $this->assertEquals('customer_label', SpreadsheetImportHelper::normalizeHeader('customer_label'));
        $this->assertEquals('', SpreadsheetImportHelper::normalizeHeader(null));
    }

    public function test_validates_headers_detects_matched_missing_and_extra(): void
    {
        $required = ['name', 'phone', 'email'];
        $detected = ['Name', 'Phone', 'address'];

        $result = SpreadsheetImportHelper::validateHeaders($detected, $required);

        $this->assertFalse($result['is_valid']);
        $this->assertEquals(['name', 'phone'], $result['matched']);
        $this->assertEquals(['email'], $result['missing']);
        $this->assertEquals(['address'], $result['extra']);
    }

    public function test_validates_headers_passes_when_all_required_present(): void
    {
        $required = ['name', 'phone', 'email'];
        $detected = ['Email', 'NAME', 'Phone', 'extra_col'];

        $result = SpreadsheetImportHelper::validateHeaders($detected, $required);

        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['missing']);
        $this->assertEquals(['extra_col'], $result['extra']);
    }

    public function test_inspect_file_on_pelanggan_csv_template(): void
    {
        $path = public_path('templates/template_import_pelanggan.csv');
        if (!file_exists($path)) {
            $this->markTestSkipped('Customer CSV template not found');
        }

        $info = SpreadsheetImportHelper::inspectFile($path, null, ImportCustomers::REQUIRED_HEADERS);

        $this->assertTrue($info['validation']['is_valid']);
        $this->assertEmpty($info['validation']['missing']);
        $this->assertGreaterThan(0, $info['total_rows']);
        $this->assertNotEmpty($info['preview_rows']);
        $this->assertEquals('Budi Santoso', $info['preview_rows'][0]['name']);
    }

    public function test_inspect_file_on_supplier_csv_template(): void
    {
        $path = public_path('templates/template_import_supplier.csv');
        if (!file_exists($path)) {
            $this->markTestSkipped('Supplier CSV template not found');
        }

        $info = SpreadsheetImportHelper::inspectFile($path, null, ImportSuppliers::REQUIRED_HEADERS);

        $this->assertTrue($info['validation']['is_valid']);
        $this->assertEmpty($info['validation']['missing']);
        $this->assertGreaterThan(0, $info['total_rows']);
        $this->assertNotEmpty($info['preview_rows']);
        $this->assertEquals('PT Yamaha Musik Distributor', $info['preview_rows'][0]['name']);
    }
}
