<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_render_the_pos_login_page()
    {
        $response = $this->get(route('pos.login'));

        $response->assertStatus(200);
        $response->assertSee('Diego Music POS');
    }

    /** @test */
    public function it_redirects_unauthenticated_user_accessing_pos_to_login()
    {
        $response = $this->get(route('pos'));

        $response->assertRedirect(route('pos.login'));
    }

    /** @test */
    public function it_redirects_to_pos_if_already_logged_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.login'));

        $response->assertRedirect('/pos');
    }

    /** @test */
    public function it_authenticates_user_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'cashier@diegomusic.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test('App\Livewire\POSLogin')
            ->set('email', 'cashier@diegomusic.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/pos');

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_authenticates_user_with_correct_username_credentials()
    {
        $user = User::factory()->create([
            'username' => 'cashier_diego',
            'email' => 'cashier@diegomusic.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test('App\Livewire\POSLogin')
            ->set('email', 'cashier_diego')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/pos');

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_fails_authentication_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'cashier@diegomusic.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test('App\Livewire\POSLogin')
            ->set('email', 'cashier@diegomusic.com')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
