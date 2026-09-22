<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAndPermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'is_active' => true,
        ]);
    }

    public function test_owner_and_admin_can_access_backoffice_panel(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $panel = Filament::getPanel('backoffice');
        $this->assertTrue($owner->canAccessPanel($panel));

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');
        $admin->branches()->attach($this->branch->id);

        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_karyawan_and_sales_cannot_access_backoffice_panel(): void
    {
        $panel = Filament::getPanel('backoffice');

        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);

        $this->assertFalse($karyawan->canAccessPanel($panel));

        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole('sales');
        $sales->branches()->attach($this->branch->id);

        $this->assertFalse($sales->canAccessPanel($panel));
    }

    public function test_inactive_user_cannot_access_backoffice_panel(): void
    {
        $panel = Filament::getPanel('backoffice');

        $inactiveAdmin = User::factory()->create(['is_active' => false]);
        $inactiveAdmin->assignRole('admin');
        $inactiveAdmin->branches()->attach($this->branch->id);

        $this->assertFalse($inactiveAdmin->canAccessPanel($panel));
    }

    public function test_karyawan_can_access_general_pos(): void
    {
        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);

        $responseSession = $this->actingAs($karyawan)->get('/pos/session');
        $responseSession->assertStatus(200);

        $responseCustomers = $this->actingAs($karyawan)->get('/pos/customers');
        $responseCustomers->assertStatus(200);

        $responseAttendances = $this->actingAs($karyawan)->get('/pos/attendances');
        $responseAttendances->assertStatus(200);
    }

    public function test_karyawan_forbidden_from_sensitive_admin_pos_routes(): void
    {
        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);

        // Payroll
        $response = $this->actingAs($karyawan)->get('/pos/payroll');
        $response->assertStatus(403);

        // Setting Hak Akses (Privileges)
        $response = $this->actingAs($karyawan)->get('/pos/privileges');
        $response->assertStatus(403);

        // Kelola User
        $response = $this->actingAs($karyawan)->get('/pos/users');
        $response->assertStatus(403);

        // Manajemen Cabang
        $response = $this->actingAs($karyawan)->get('/pos/branches');
        $response->assertStatus(403);
    }

    public function test_admin_and_owner_can_access_sensitive_pos_routes(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $response = $this->actingAs($owner)->get('/pos/payroll');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->get('/pos/privileges');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->get('/pos/users');
        $response->assertStatus(200);

        $response = $this->actingAs($owner)->get('/pos/branches');
        $response->assertStatus(200);
    }

    public function test_seeder_assigns_correct_default_permissions_to_roles(): void
    {
        $ownerRole = Role::findByName('owner');
        $this->assertTrue($ownerRole->hasPermissionTo('pos.access'));
        $this->assertTrue($ownerRole->hasPermissionTo('utility.privileges'));
        $this->assertTrue($ownerRole->hasPermissionTo('master.users'));

        $karyawanRole = Role::findByName('karyawan');
        $this->assertTrue($karyawanRole->hasPermissionTo('pos.access'));
        $this->assertTrue($karyawanRole->hasPermissionTo('daily_cash.manage'));
        $this->assertFalse($karyawanRole->hasPermissionTo('utility.privileges'));
        $this->assertFalse($karyawanRole->hasPermissionTo('master.users'));

        $salesRole = Role::findByName('sales');
        $this->assertTrue($salesRole->hasPermissionTo('pos.access'));
        $this->assertFalse($salesRole->hasPermissionTo('utility.privileges'));
    }
}
