<?php

namespace Tests\Feature;

use App\Actions\CashManagement\CreateCashTransaction;
use App\Actions\Sales\CreatePOSSale;
use App\Helpers\BranchHelper;
use App\Helpers\PermissionHelper;
use App\Livewire\POS;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->actingAs($karyawan)->get('/pos/session')->assertStatus(200);
        $this->actingAs($karyawan)->get('/pos/customers')->assertStatus(200);
        $this->actingAs($karyawan)->get('/pos/attendances')->assertStatus(200);
    }

    public function test_karyawan_forbidden_from_sensitive_admin_pos_routes(): void
    {
        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);

        foreach (['/pos/payroll', '/pos/privileges', '/pos/users', '/pos/branches'] as $path) {
            $this->actingAs($karyawan)->get($path)->assertStatus(403);
        }
    }

    public function test_super_admin_without_explicit_permissions_cannot_bypass_rbac(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->branches()->attach($this->branch->id);

        $this->assertFalse($superAdmin->canAccessPanel(Filament::getPanel('backoffice')));
        $this->assertFalse(PermissionHelper::canAccessFrontoffice($superAdmin));
        $this->actingAs($superAdmin)->get('/backoffice/user-privileges')->assertForbidden();
        $this->actingAs($superAdmin)->get('/pos/privileges')->assertForbidden();
    }

    public function test_owner_can_access_sensitive_pos_routes(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        foreach (['/pos/payroll', '/pos/privileges', '/pos/users', '/pos/branches'] as $path) {
            $this->actingAs($owner)->get($path)->assertStatus(200);
        }
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
            '/pos/receipt-draft',
            '/pos/barcode-print/sheet',
            '/pos/customers',
            '/pos/session',
            '/pos/daily-cash',
            '/pos/customer-deposits',
            '/pos/customer-payments',
            '/pos/reports/sales',
            '/pos/reports/ar-aging',
            '/pos/reports/ar-settlement',
            '/pos/reports/daily-cash',
            '/pos/reports/stock-prices',
            '/pos/attendances',
            '/pos/day-off-calendar',
            '/pos/stock-opname-tasks',
            '/pos/service-management',
            '/pos/barcode-print',
            '/pos/commissions',
            '/pos/kpi-performance',
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

    public function test_regular_user_cannot_create_sale_in_unassigned_branch_via_action(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Tidak Ditugaskan',
            'is_active' => true,
        ]);

        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);
        $this->actingAs($karyawan);
        $this->expectException(HttpException::class);

        app(CreatePOSSale::class)->execute([
            'branch_id' => $otherBranch->id,
            'items' => [],
        ]);
    }

    public function test_regular_user_cannot_create_cash_transaction_in_unassigned_branch_via_action(): void
    {
        $otherBranch = Branch::create([
            'name' => 'Cabang Kas Tidak Ditugaskan',
            'is_active' => true,
        ]);

        $karyawan = User::factory()->create(['is_active' => true]);
        $karyawan->assignRole('karyawan');
        $karyawan->branches()->attach($this->branch->id);
        $this->actingAs($karyawan);
        $this->expectException(HttpException::class);

        app(CreateCashTransaction::class)->execute([
            'branch_id' => $otherBranch->id,
        ]);
    }

    public function test_seeder_does_not_recreate_retired_admin_role(): void
    {
        $this->assertDatabaseMissing('roles', [
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }

    public function test_seeder_assigns_canonical_fo_and_bo_permissions_to_roles(): void
    {
        $ownerRole = Role::findByName('owner');
        $this->assertTrue($ownerRole->hasPermissionTo('fo.access'));
        $this->assertTrue($ownerRole->hasPermissionTo('bo.access'));
        $this->assertTrue($ownerRole->hasPermissionTo('bo.user_privileges.manage'));
        $this->assertTrue($ownerRole->hasPermissionTo('fo.privileges.manage'));

        $karyawanRole = Role::findByName('karyawan');
        $this->assertTrue($karyawanRole->hasPermissionTo('fo.access'));
        $this->assertFalse($karyawanRole->hasPermissionTo('bo.access'));
        $this->assertTrue($karyawanRole->hasPermissionTo('fo.daily_cash.create'));
        $this->assertFalse($karyawanRole->hasPermissionTo('fo.privileges.view'));
        $this->assertFalse($karyawanRole->hasPermissionTo('fo.users.view'));

        $salesRole = Role::findByName('sales');
        $this->assertTrue($salesRole->hasPermissionTo('fo.access'));
        $this->assertFalse($salesRole->hasPermissionTo('bo.access'));
        $this->assertTrue($salesRole->hasPermissionTo('fo.catalog.view'));
        $this->assertFalse($salesRole->hasPermissionTo('fo.pos.view'));
    }

    public function test_user_privileges_page_form_populates_panel_toggles_and_checked_groups(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $karyawanRole = Role::findByName('karyawan');

        Livewire::actingAs($owner)
            ->test(\App\Filament\Pages\UserPrivileges::class)
            ->mountTableAction('editPermissions', $karyawanRole)
            ->assertTableActionDataSet(function (array $data): bool {
                $posPerms = data_get($data, 'perm_groups.frontoffice__fo_main__fo_pos', []);
                $privPerms = data_get($data, 'perm_groups.frontoffice__fo_utility__fo_privileges', []);

                return data_get($data, 'access_fo') === true
                    && data_get($data, 'access_bo') === false
                    && in_array('fo.pos.view', $posPerms, true)
                    && $privPerms === [];
            });
    }

    public function test_custom_role_with_bo_access_only_cannot_access_frontoffice(): void
    {
        $panel = Filament::getPanel('backoffice');

        $gudangRole = Role::create(['name' => 'gudang_bo_only', 'guard_name' => 'web']);
        $gudangRole->givePermissionTo(['bo.access', 'bo.products.view']);

        $staffGudang = User::factory()->create(['is_active' => true]);
        $staffGudang->assignRole($gudangRole);
        $staffGudang->branches()->attach($this->branch->id);

        $this->assertTrue($staffGudang->canAccessPanel($panel));
        $this->assertFalse(PermissionHelper::canAccessFrontoffice($staffGudang));
        $this->actingAs($staffGudang)->get('/backoffice/products')->assertStatus(200);
        $this->actingAs($staffGudang)->get('/backoffice/accounts')->assertStatus(403);
        $this->actingAs($staffGudang)->get('/pos/front-office')->assertStatus(403);
    }

    public function test_custom_role_without_pos_sub_permissions_cannot_discount_hold_or_print(): void
    {
        $kasirBasic = Role::create(['name' => 'kasir_dasar', 'guard_name' => 'web']);
        $kasirBasic->givePermissionTo(['fo.access', 'fo.dashboard.view', 'fo.pos.view', 'fo.pos.checkout']);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($kasirBasic);
        $user->branches()->attach($this->branch->id);

        CashSession::create([
            'branch_id' => $this->branch->id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $this->actingAs($user);

        Livewire::test(POS::class)
            ->assertStatus(200)
            ->call('toggleGlobalDiscountType')
            ->assertForbidden();

        Livewire::test(POS::class)
            ->call('holdTransaction')
            ->assertForbidden();

        Livewire::test(POS::class)
            ->call('printDraft', 'bill')
            ->assertForbidden();
    }

    public function test_employee_salary_field_is_preserved_when_edited_without_hr_view_salary(): void
    {
        $hrLimited = Role::create(['name' => 'hr_tanpa_gaji', 'guard_name' => 'web']);
        $hrLimited->givePermissionTo(['fo.access', 'fo.employees.view', 'fo.employees.edit']);

        $hrUser = User::factory()->create(['is_active' => true]);
        $hrUser->assignRole($hrLimited);
        $hrUser->branches()->attach($this->branch->id);

        $employee = Employee::create([
            'nik' => 'EMP-9001',
            'name' => 'Budi Santoso',
            'branch_id' => $this->branch->id,
            'basic_salary' => 5500000,
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        Livewire::actingAs($hrUser)
            ->test(\App\Livewire\PosEmployees::class)
            ->call('openEdit', $employee->id)
            ->assertSet('basic_salary', 0)
            ->set('name', 'Budi Santoso Updated')
            ->call('save')
            ->assertHasNoErrors();

        $employee->refresh();
        $this->assertSame('Budi Santoso Updated', $employee->name);
        $this->assertEquals(5500000, (float) $employee->basic_salary);
    }

    public function test_unauthenticated_guest_can_access_backoffice_login_page_without_403(): void
    {
        $response = $this->get('/backoffice/login');

        $response->assertStatus(200);
        $response->assertSee('Diego Music Backoffice');
    }

    public function test_unauthenticated_guest_accessing_backoffice_is_redirected_to_backoffice_login(): void
    {
        $response = $this->get('/backoffice');

        $response->assertStatus(302);
        $response->assertRedirect('/backoffice/login');
    }
}
