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
        $user = User::factory()->create();
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
        Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
        $karyawanUser = User::factory()->create();
        $karyawanUser->assignRole('karyawan');
        $karyawanUser->branches()->attach($this->branch->id);

        $response = $this->actingAs($karyawanUser)->get(route('pos.front-office'));

        $response->assertStatus(200);
        // Non-owner must NOT see the quick navigation shortcut tiles
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
}
