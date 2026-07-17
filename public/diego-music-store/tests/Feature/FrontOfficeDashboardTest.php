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
        $response->assertSee('Sesi Belum Dibuka');
    }

    /** @test */
    public function it_renders_session_active_when_session_is_open()
    {
        $user = User::factory()->create();
        $branch = \App\Models\Branch::factory()->create();

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
    }
}
