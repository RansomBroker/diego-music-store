<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Actions\Sales\CreatePOSSale;
use App\Helpers\BranchHelper;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function test_owner_can_access_backoffice_panel_and_legacy_admin_cannot(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $panel = Filament::getPanel('backoffice');
        $this->assertTrue($owner->canAccessPanel($panel));

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($adminRole);

        $this->assertFalse($admin->canAccessPanel($panel));
    }

    public function test_regular_user_without_branch_is_denied_instead_of_falling_back(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('karyawan');

        $this->actingAs($user);
        $this->expectException(HttpException::class);

        BranchHelper::getActiveBranchId();
    }

    public function test_regular_user_cannot_switch_active_branch_to_unassigned_branch(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Lain',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('karyawan');
        $user->branches()->attach($this->branch->id);

        session(['pos_active_branch_id' => $otherBranch->id]);
        $this->actingAs($user);

        $this->assertSame($this->branch->id, BranchHelper::getActiveBranchId());
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
        $inactiveAdmin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
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

    public function test_owner_can_access_sensitive_pos_routes(): void
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

    public function test_sales_can_view_read_only_catalog_but_cannot_open_transactional_pos(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole('sales');
        $sales->branches()->attach($this->branch->id);

        $this->actingAs($sales)->get('/pos/catalog')->assertStatus(200);
        $this->actingAs($sales)->get('/pos')->assertStatus(403);
    }

    public function test_sales_role_cannot_access_transaction_cash_and_payment_pages(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole('sales');
        $sales->branches()->attach($this->branch->id);

        foreach ([
            '/pos/transactions',
            '/pos/daily-cash',
            '/pos/customer-deposits',
            '/pos/customer-payments',
            '/pos/reports/sales',
        ] as $path) {
            $this->actingAs($sales)->get($path)->assertStatus(403);
        }
    }

    public function test_sales_role_cannot_create_pos_sale_even_by_calling_action_directly(): void
    {
        $sales = User::factory()->create(['is_active' => true]);
        $sales->assignRole('sales');

        $this->actingAs($sales);
        $this->expectException(HttpException::class);

        app(CreatePOSSale::class)->execute([]);
    }

    public function test_seeder_does_not_recreate_retired_admin_role(): void
    {
        $this->assertDatabaseMissing('roles', [
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
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

    public function test_user_privileges_page_form_populates_checked_permissions(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $karyawanRole = Role::findByName('karyawan');
        $this->assertTrue($karyawanRole->hasPermissionTo('pos.access'));

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Filament\Pages\UserPrivileges::class)
            ->mountTableAction('editPermissions', $karyawanRole)
            ->assertTableActionDataSet(function (array $data): bool {
                return data_get($data, 'permissions.pos.access') === true
                    && data_get($data, 'permissions.utility.privileges') === false;
            });
    }
}
