<?php

namespace Tests\Feature\Actions;

use App\Actions\Supplier\ImportSupplierDebts;
use App\Actions\Supplier\ImportSuppliers;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Helpers\SpreadsheetImportHelper;
use App\Livewire\Backoffice\SpreadsheetImporter;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportSuppliersTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_four_column_supplier_format_from_tsv_and_templates(): void
    {
        $tsv = <<<'TSV'
NO	NAMA	ALAMAT	STATUS
1	AANK PICK	BANDUNG	SUPPLIER
2	ACE HARDWARE PONTIANAK	PONTIANAK	VENDOR
3	ADE ETMUS	JAKARTA	SUPPLIER
TSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'sup_import_') . '.csv';
        file_put_contents($tempFile, $tsv);

        try {
            $inspect = SpreadsheetImportHelper::inspectFile($tempFile, null, ImportSuppliers::REQUIRED_HEADERS);
            $this->assertTrue($inspect['validation']['is_valid']);
            $this->assertEquals(['NO'], $inspect['ignored_headers']);

            $rows = SpreadsheetImportHelper::readRows($tempFile, null, 0, 50, $inspect['header_row'], $inspect['col_map']);
            $result = app(ImportSuppliers::class)->execute($rows);

            $this->assertSame(3, $result['imported']);
            $this->assertSame(0, $result['skipped']);
            $this->assertEmpty($result['errors']);

            $this->assertDatabaseHas('suppliers', [
                'name' => 'AANK PICK',
                'address' => 'BANDUNG',
                'status' => 'SUPPLIER',
            ]);
            $this->assertDatabaseHas('suppliers', [
                'name' => 'ACE HARDWARE PONTIANAK',
                'address' => 'PONTIANAK',
                'status' => 'VENDOR',
            ]);
            $this->assertDatabaseHas('suppliers', [
                'name' => 'ADE ETMUS',
                'address' => 'JAKARTA',
                'status' => 'SUPPLIER',
            ]);
        } finally {
            @unlink($tempFile);
        }

        // Also validate official .xlsx template end-to-end
        $xlsxPath = public_path('templates/template_import_supplier.xlsx');
        if (file_exists($xlsxPath)) {
            $xlsxInspect = SpreadsheetImportHelper::inspectFile($xlsxPath, null, ImportSuppliers::REQUIRED_HEADERS);
            $this->assertTrue($xlsxInspect['validation']['is_valid']);
            $xlsxRows = SpreadsheetImportHelper::readRows($xlsxPath, null, 0, 50, $xlsxInspect['header_row'], $xlsxInspect['col_map']);
            $xlsxResult = app(ImportSuppliers::class)->execute($xlsxRows);

            $this->assertSame(3, $xlsxResult['imported']);
            $this->assertSame(3, Supplier::count());
        }
    }

    public function test_upserting_four_column_format_preserves_existing_contact_bank_and_debt(): void
    {
        $existing = Supplier::create([
            'name' => 'ACE HARDWARE PONTIANAK',
            'status' => 'SUPPLIER',
            'contact_person' => 'Budi',
            'phone' => '0561777888',
            'email' => 'ace@pontianak.co.id',
            'address' => 'Alamat Lama',
            'bank_name' => 'BCA',
            'bank_account_number' => '111222333',
            'bank_account_name' => 'Ace Pontianak',
            'outstanding_debt' => 1500000,
        ]);

        $result = app(ImportSuppliers::class)->execute([
            [
                'no' => '2',
                'nama' => 'ace hardware pontianak',
                'alamat' => 'PONTIANAK',
                'status' => 'vendor',
            ],
        ]);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, Supplier::count());

        $existing->refresh();
        $this->assertSame('ace hardware pontianak', $existing->name);
        $this->assertSame('VENDOR', $existing->status);
        $this->assertSame('PONTIANAK', $existing->address);
        $this->assertSame('Budi', $existing->contact_person);
        $this->assertSame('0561777888', $existing->phone);
        $this->assertSame('ace@pontianak.co.id', $existing->email);
        $this->assertSame('BCA', $existing->bank_name);
        $this->assertSame('111222333', $existing->bank_account_number);
        $this->assertSame('Ace Pontianak', $existing->bank_account_name);
        $this->assertEquals(1500000, (float) $existing->outstanding_debt);
    }

    public function test_spreadsheet_importer_mounts_for_supplier_with_new_required_headers(): void
    {
        Livewire::test(SpreadsheetImporter::class, ['type' => 'supplier'])
            ->assertSet('requiredHeaders', ImportSuppliers::REQUIRED_HEADERS);
    }

    public function test_contextual_header_aliases_resolve_without_conflicting_with_supplier_debts(): void
    {
        $this->assertSame('nama', SpreadsheetImportHelper::resolveHeaderForRequired('NAMA VENDOR', ImportSuppliers::REQUIRED_HEADERS));
        $this->assertSame('alamat', SpreadsheetImportHelper::resolveHeaderForRequired('KOTA', ImportSuppliers::REQUIRED_HEADERS));
        $this->assertSame('status', SpreadsheetImportHelper::resolveHeaderForRequired('TIPE', ImportSuppliers::REQUIRED_HEADERS));

        // Supplier Debt context must still map VENDOR -> supplier
        $this->assertSame('supplier', SpreadsheetImportHelper::resolveHeaderForRequired('VENDOR', ImportSupplierDebts::REQUIRED_HEADERS));
    }

    public function test_backoffice_list_suppliers_renders_status_address_and_filter(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $this->actingAs($owner);

        $sup = Supplier::create([
            'name' => 'AANK PICK',
            'address' => 'BANDUNG',
            'status' => Supplier::STATUS_SUPPLIER,
        ]);

        $vendor = Supplier::create([
            'name' => 'ACE HARDWARE PONTIANAK',
            'address' => 'PONTIANAK',
            'status' => Supplier::STATUS_VENDOR,
        ]);

        Livewire::test(ListSuppliers::class)
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('address')
            ->assertCanSeeTableRecords([$sup, $vendor])
            ->filterTable('status', Supplier::STATUS_VENDOR)
            ->assertCanSeeTableRecords([$vendor])
            ->assertCanNotSeeTableRecords([$sup]);
    }

    public function test_backoffice_crud_create_and_edit_supplier_persists_status(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $this->actingAs($owner);

        Livewire::test(ListSuppliers::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Vendor Audio Kalimantan',
                'status' => Supplier::STATUS_VENDOR,
                'address' => 'PONTIANAK',
            ])
            ->assertHasNoActionErrors();

        $created = Supplier::where('name', 'Vendor Audio Kalimantan')->first();
        $this->assertNotNull($created);
        $this->assertSame(Supplier::STATUS_VENDOR, $created->status);
        $this->assertSame('PONTIANAK', $created->address);

        Livewire::test(ListSuppliers::class)
            ->callTableAction(EditAction::class, $created, data: [
                'name' => 'Vendor Audio Kalimantan',
                'status' => Supplier::STATUS_SUPPLIER,
                'address' => 'BANDUNG',
            ])
            ->assertHasNoTableActionErrors();

        $created->refresh();
        $this->assertSame(Supplier::STATUS_SUPPLIER, $created->status);
        $this->assertSame('BANDUNG', $created->address);
    }

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
        $this->assertEquals('SUPPLIER', $sup1->status);
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
        $this->assertStringContainsString("Baris 2: Kolom 'NAMA' (Nama Supplier) wajib diisi.", $result['errors'][0]);
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
