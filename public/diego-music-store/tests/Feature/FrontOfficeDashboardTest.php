<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FrontOfficeDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_unauthenticated_user_accessing_front_office_to_login()
    {
        $response = $this->get(route('pos.front-office'));

        $response->assertRedirect(route('pos.login'));
    }

    /** @test */
    public function it_renders_session_inactive_when_no_active_session()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Tidak Aktif');
    }

    /** @test */
    public function it_renders_session_active_when_session_is_open()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $branch = \App\Models\Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);

        \App\Models\CashSession::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Aktif');
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Clock In (Masuk)');
    }

    /** @test */
    public function it_renders_presensi_karyawan_cabang_hari_ini_section_on_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        $response = $this->actingAs($user)->get(route('pos.front-office'));

        $response->assertStatus(200);
        $response->assertSee('Presensi Karyawan Cabang Hari Ini');
        $response->assertSee('Clock In (Masuk)');
        $response->assertSee('Komisi:');
    }
}
