<?php

namespace Tests\Feature\Actions;

use App\Actions\Supplier\ImportSuppliers;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportSuppliersTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_supplier_rows_successfully(): void
    {
        $action = app(ImportSuppliers::class);

        $rows = [
            [
                'name' => 'PT Ibanez Indonesia',
                'contact_person' => 'Bambang',
                'phone' => '0215551234',
                'email' => 'sales@ibanez.co.id',
                'address' => 'Kawasan Industri MM2100',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'PT Ibanez Indonesia',
                'outstanding_debt' => '5000000',
            ],
            [
                'name' => 'CV Fender Parts',
                'contact_person' => 'Rina',
                'phone' => '0215555678',
                'email' => 'rina@fenderparts.com',
                'address' => 'Jl. Roxy Mas Blok C-4',
                'bank_name' => 'Mandiri',
                'bank_account_number' => '9876543210',
                'bank_account_name' => 'CV Fender Parts',
                'outstanding_debt' => '0',
            ]
        ];

        $result = $action->execute($rows);

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);

        $sup1 = Supplier::where('phone', '0215551234')->first();
        $this->assertNotNull($sup1);
        $this->assertEquals('PT Ibanez Indonesia', $sup1->name);
        $this->assertEquals('Bambang', $sup1->contact_person);
        $this->assertEquals('BCA', $sup1->bank_name);
        $this->assertEquals(5000000, (float)$sup1->outstanding_debt);

        $sup2 = Supplier::where('phone', '0215555678')->first();
        $this->assertNotNull($sup2);
        $this->assertEquals('CV Fender Parts', $sup2->name);
    }

    public function test_skips_row_with_empty_name(): void
    {
        $action = app(ImportSuppliers::class);

        $rows = [
            [
                'name' => '',
                'phone' => '0812345678',
                '_row_number' => 2,
            ],
            [
                'name' => 'Valid Supplier',
                'phone' => '0812345679',
                '_row_number' => 3,
            ]
        ];

        $result = $action->execute($rows);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString("Baris 2: Kolom 'name' (Nama Supplier) wajib diisi.", $result['errors'][0]);
    }

    public function test_upserts_existing_supplier(): void
    {
        $action = app(ImportSuppliers::class);

        $supplier = Supplier::create([
            'name' => 'Old Supplier Name',
            'phone' => '02199887766',
            'contact_person' => 'Old Contact',
            'outstanding_debt' => 0,
        ]);

        $rows = [
            [
                'name' => 'Updated Supplier Name',
                'phone' => '02199887766',
                'contact_person' => 'New Contact Person',
                'email' => 'updated@supplier.com',
                'address' => 'Updated Address',
                'bank_name' => 'BNI',
                'bank_account_number' => '555666777',
                'bank_account_name' => 'Updated Supplier Name',
                'outstanding_debt' => '2500000',
            ]
        ];

        $result = $action->execute($rows);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(1, Supplier::where('phone', '02199887766')->count());

        $supplier->refresh();
        $this->assertEquals('Updated Supplier Name', $supplier->name);
        $this->assertEquals('New Contact Person', $supplier->contact_person);
        $this->assertEquals('BNI', $supplier->bank_name);
        $this->assertEquals(2500000, (float)$supplier->outstanding_debt);
    }
}
