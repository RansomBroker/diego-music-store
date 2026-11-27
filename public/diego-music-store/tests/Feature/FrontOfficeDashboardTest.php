<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FrontOfficeDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::firstOrCreate(['name' => 'Cabang Test'], [
            'code' => 'CBG-DASH-01',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
    }

    public function test_it_redirects_unauthenticated_user_accessing_front_office_to_login(): void
    {
        $response = $this->get(route('pos.front-office'));

        $response->assertRedirect(route('pos.login'));
    }

    public function test_it_renders_session_inactive_when_no_active_session(): void
    {
        $role = Role::firstOrCreate(['name' => 'fo_viewer', 'guard_name' => 'web']);
        foreach (['fo.access', 'fo.dashboard.view'] as $perm) {
            \Spatie\Permission\Models\Permission::findOrCreate($perm, 'web');
        }
        $role->givePermissionTo(['fo.access', 'fo.dashboard.view']);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $user->branches()->attach($this->branch->id);

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Nonaktif');
    }

    public function test_it_renders_session_active_when_session_is_open(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('owner');

        CashSession::create([
            'user_id' => $user->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertDontSee('Sesi Nonaktif');
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Status Otoritas Pemilik');
        $response->assertDontSee('Clock In (Masuk)');
        $response->assertSee($user->name);
    }

    public function test_it_renders_presensi_karyawan_cabang_hari_ini_section_on_dashboard(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Status Otoritas Pemilik');
    }

    public function test_it_renders_clock_in_button_for_regular_employee(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('karyawan');
        $user->branches()->attach($this->branch->id);

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Clock In (Masuk)');
        $response->assertSee('Komisi:');
    }

    public function test_it_hides_shortcut_navigation_menu_for_non_owner(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $basicRole = Role::firstOrCreate(['name' => 'fo_basic', 'guard_name' => 'web']);
        $basicRole->syncPermissions(['fo.access', 'fo.dashboard.view']);
        $basicUser = User::factory()->create();
        $basicUser->assignRole($basicRole);
        $basicUser->branches()->attach($this->branch->id);

        $response = $this->actingAs($basicUser)->get(route('pos.front-office'));

        $response->assertStatus(200);
        // Role without tile permissions must NOT see the quick navigation shortcut tiles
        $response->assertDontSee('Pintasan Menu POS');
        $response->assertDontSee('Buka halaman kasir');
        $response->assertDontSee('pantau tugas');
        $response->assertDontSee('Panel admin');

        // Owner CAN see the quick navigation shortcut menu
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('owner');

        $ownerResponse = $this->actingAs($ownerUser)->get(route('pos.front-office'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('Pintasan Menu POS');
        $ownerResponse->assertSee('Buka halaman kasir');
        $ownerResponse->assertSee('pantau tugas');
    }

    public function test_it_hides_deleted_and_inactive_users_from_today_branch_attendances(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $ownerUser = User::factory()->create(['name' => 'Owner Cabang']);
        $ownerUser->assignRole('owner');
        $ownerUser->branches()->attach($this->branch->id);

        $activeStaff = User::factory()->create(['name' => 'Karyawan Aktif', 'is_active' => true]);
        $activeStaff->assignRole('karyawan');
        $activeStaff->branches()->attach($this->branch->id);
        $activeStaff->employee->update(['branch_id' => $this->branch->id]);

        $deletedUser = User::factory()->create(['name' => 'User Dihapus', 'is_active' => true]);
        $deletedUser->assignRole('karyawan');
        $deletedUser->branches()->attach($this->branch->id);
        $deletedEmp = $deletedUser->employee;
        $deletedEmp->update(['branch_id' => $this->branch->id]);
        $deletedUser->delete();

        $inactiveUser = User::factory()->create(['name' => 'User Nonaktif', 'is_active' => false]);
        $inactiveUser->assignRole('karyawan');
        $inactiveUser->branches()->attach($this->branch->id);
        $inactiveUser->employee->update(['branch_id' => $this->branch->id, 'is_active' => true]);

        \App\Models\Employee::create([
            'user_id' => null,
            'branch_id' => $this->branch->id,
            'nik' => 'EMP-ORPHAN-01',
            'name' => 'Orphaned Deleted User',
            'is_active' => true,
        ]);

        $response = $this->actingAs($ownerUser)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Karyawan Aktif');
        $response->assertDontSee('User Dihapus');
        $response->assertDontSee('User Nonaktif');
        $response->assertDontSee('Orphaned Deleted User');
        $this->assertFalse((bool) $deletedEmp->fresh()->is_active);
    }

    public function test_only_owner_can_view_performance_analysis_widgets_on_dashboard(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $staff = User::factory()->create(['is_active' => true]);
        $staff->assignRole('karyawan');
        $staff->givePermissionTo('fo.dashboard.owner_widgets');
        $staff->branches()->attach($this->branch->id);

        $staffResponse = $this->actingAs($staff)->get(route('pos.front-office'));
        $staffResponse->assertStatus(200);
        $staffResponse->assertSee('Clock In (Masuk)');
        $staffResponse->assertDontSee('Analisis Performa &amp; Keuangan Bisnis', false);
        $staffResponse->assertSee('Performa Sales & Indikator Target', false);
        $staffResponse->assertDontSee('Pilih Staf:');
        $staffResponse->assertSee('Presensi Karyawan Cabang Hari Ini');

        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($this->branch->id);

        $ownerResponse = $this->actingAs($owner)->get(route('pos.front-office'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('Analisis Performa &amp; Keuangan Bisnis', false);
        $ownerResponse->assertSee('Performa Sales & Indikator Target', false);
    }

    public function test_regular_employee_only_sees_own_attendance_row_in_today_branch_attendances(): void
    {
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $ownStaff = User::factory()->create(['name' => 'Karyawan Sendiri', 'is_active' => true]);
        $ownStaff->assignRole('karyawan');
        $ownStaff->branches()->attach($this->branch->id);
        $ownStaff->employee->update(['branch_id' => $this->branch->id, 'is_active' => true]);

        $otherStaff = User::factory()->create(['name' => 'Karyawan Rekan Lain', 'is_active' => true]);
        $otherStaff->assignRole('karyawan');
        $otherStaff->branches()->attach($this->branch->id);
        $otherStaff->employee->update(['branch_id' => $this->branch->id, 'is_active' => true]);

        $response = $this->actingAs($ownStaff)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Karyawan Sendiri');
        $response->assertSee('Total: 1 Staf');

        $html = $response->getContent();
        $attendanceSection = substr($html, strpos($html, 'Presensi Karyawan Cabang Hari Ini'));
        $this->assertStringContainsString('Karyawan Sendiri', $attendanceSection);
        $this->assertStringNotContainsString('Karyawan Rekan Lain', $attendanceSection);
    }
}
