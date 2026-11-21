<?php

namespace Tests\Feature\Actions\Employee;

use App\Actions\Employee\ImportEmployees;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Helpers\SpreadsheetImportHelper;
use App\Livewire\Backoffice\SpreadsheetImporter;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportEmployeesTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Pusat',
            'address' => 'Jl. Merdeka',
            'phone' => '081111',
            'is_active' => true,
        ]);

        // Pre-seed 'sales' role in lowercase to test case-insensitive reuse
        Role::create(['name' => 'sales', 'guard_name' => 'web']);
    }

    public function test_imports_employees_creates_users_reuses_existing_roles_and_creates_missing_roles(): void
    {
        $csv = <<<'CSV'
NO	NAMA	USER	ROLE	PASSWORD
1	RAMADHAN SAPUTRA	madan	MANAGER TOKO	madan
2	BAGAS SATRIO	bagas	SALES	bagas
3	RIA NURDIANTI	rianurdianti	SALES	rianurdianti
4	NAILA MAULIDIA	naila	KASIR UTAMA	naila
5	MAR'IE MUHAMMAD	marie	SALES	marie
6	DESLA	desla	ADMIN	desla
CSV;

        $tempFile = tempnam(sys_get_temp_dir(), 'emp_import_') . '.csv';
        file_put_contents($tempFile, $csv);

        try {
            $inspect = SpreadsheetImportHelper::inspectFile($tempFile, null, ImportEmployees::REQUIRED_HEADERS);
            $this->assertTrue($inspect['validation']['is_valid']);

            $rows = SpreadsheetImportHelper::readRows($tempFile, null, 0, 50, $inspect['header_row'], $inspect['col_map']);
            $result = app(ImportEmployees::class)->execute($rows, $this->branch->id);

            $this->assertSame(6, $result['imported']);
            $this->assertSame(0, $result['skipped']);
            $this->assertEmpty($result['errors']);

            // 'sales' existed beforehand; only MANAGER TOKO, KASIR UTAMA, ADMIN should be created
            $this->assertEqualsCanonicalizing(['MANAGER TOKO', 'KASIR UTAMA', 'ADMIN'], $result['created_roles']);
            $this->assertSame(1, Role::whereRaw('LOWER(name) = ?', ['sales'])->count());
            $this->assertDatabaseHas('roles', ['name' => 'MANAGER TOKO', 'guard_name' => 'web']);
            $this->assertDatabaseHas('roles', ['name' => 'KASIR UTAMA', 'guard_name' => 'web']);
            $this->assertDatabaseHas('roles', ['name' => 'ADMIN', 'guard_name' => 'web']);

            // Verify user accounts, passwords, branch assignments, roles, and Employee records
            $madan = User::where('username', 'madan')->firstOrFail();
            $this->assertSame('RAMADHAN SAPUTRA', $madan->name);
            $this->assertTrue(Hash::check('madan', $madan->password));
            $this->assertTrue($madan->hasRole('MANAGER TOKO'));
            $this->assertTrue($madan->branches()->where('branches.id', $this->branch->id)->exists());
            $this->assertNotNull($madan->employee);
            $this->assertSame('RAMADHAN SAPUTRA', $madan->employee->name);
            $this->assertSame($this->branch->id, $madan->employee->branch_id);
            $this->assertStringStartsWith('EMP-', $madan->employee->nik);

            $bagas = User::where('username', 'bagas')->firstOrFail();
            $this->assertTrue($bagas->hasRole('sales'));
            $this->assertTrue(Hash::check('bagas', $bagas->password));

            $marie = User::where('username', 'marie')->firstOrFail();
            $this->assertSame("MAR'IE MUHAMMAD", $marie->name);
            $this->assertSame(6, Employee::count());
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_reimporting_existing_username_updates_employee_and_role_without_duplicates(): void
    {
        $first = app(ImportEmployees::class)->execute([
            [
                'nama' => 'DESLA LAMA',
                'user' => 'desla',
                'role' => 'KASIR UTAMA',
                'password' => 'desla',
            ],
        ], $this->branch->id);

        $this->assertSame(1, $first['imported']);
        $this->assertSame(['KASIR UTAMA'], $first['created_roles']);

        // Re-import with updated name, password, and role
        $second = app(ImportEmployees::class)->execute([
            [
                'nama' => 'DESLA BARU',
                'user' => 'DESLA',
                'role' => 'ADMIN',
                'password' => 'desla2026',
            ],
        ], $this->branch->id);

        $this->assertSame(1, $second['imported']);
        $this->assertSame(['ADMIN'], $second['created_roles']);
        $this->assertSame(1, User::where('username', 'desla')->count());
        $this->assertSame(1, Employee::count());

        $desla = User::where('username', 'desla')->firstOrFail();
        $this->assertSame('DESLA BARU', $desla->name);
        $this->assertTrue(Hash::check('desla2026', $desla->password));
        $this->assertTrue($desla->hasRole('ADMIN'));
        $this->assertFalse($desla->hasRole('KASIR UTAMA'));
        $this->assertSame('DESLA BARU', $desla->employee->fresh()->name);
    }

    public function test_non_owner_cannot_assign_protected_owner_role_during_import(): void
    {
        Permission::findOrCreate('bo.access', 'web');
        Permission::findOrCreate('bo.employees.view', 'web');
        Permission::findOrCreate('bo.employees.create', 'web');

        $hrRole = Role::firstOrCreate(['name' => 'hr_importer_test', 'guard_name' => 'web']);
        $hrRole->syncPermissions(['bo.access', 'bo.employees.view', 'bo.employees.create']);

        $hrUser = User::factory()->create();
        $hrUser->branches()->attach($this->branch->id);
        $hrUser->assignRole($hrRole);

        $this->actingAs($hrUser);
        session(['pos_active_branch_id' => $this->branch->id]);

        $result = app(ImportEmployees::class)->execute([
            [
                'nama' => 'Penerobos',
                'user' => 'hacker',
                'role' => 'owner',
                'password' => 'secret',
            ],
        ], $this->branch->id);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertNull(User::where('username', 'hacker')->first());

        // Also confirm ListEmployees header import action is visible and SpreadsheetImporter mounts cleanly
        Livewire::test(ListEmployees::class)
            ->assertActionVisible('import');

        Livewire::test(SpreadsheetImporter::class, ['type' => 'employee'])
            ->assertSet('requiredHeaders', ImportEmployees::REQUIRED_HEADERS)
            ->assertSet('branchId', $this->branch->id);
    }
}

