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

    public function test_it_redirects_unauthenticated_user_accessing_front_office_to_login(): void
    {
        $response = $this->get(route('pos.front-office'));

        $response->assertRedirect(route('pos.login'));
    }

    public function test_it_renders_session_inactive_when_no_active_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Tidak Aktif');
    }

    public function test_it_renders_session_active_when_session_is_open(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('owner');

        $branch = Branch::create([
            'name' => 'Cabang Test',
            'code' => 'CBG-DASH-01',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);

        CashSession::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertDontSee('Sesi Tidak Aktif');
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Clock In (Masuk)');
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
        $response->assertSee('Clock In (Masuk)');
        $response->assertSee('Komisi:');
    }
}
