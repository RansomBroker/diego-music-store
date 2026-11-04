<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_renders_403_page_with_pos_navbar_and_logout_button_for_unassigned_user(): void
    {
        // User created with NO branches assigned
        $user = User::factory()->create([
            'name'  => 'Budi Kasir',
            'email' => 'budi@diegomusic.com',
        ]);
        $user->assignRole('cashier');

        $this->actingAs($user);

        // Accessing front-office when unassigned triggers 403
        $response = $this->get('/pos/front-office');

        $response->assertStatus(403);
        $response->assertSee('Budi Kasir');
        $response->assertSee('Belum Ditugaskan');
        $response->assertSee(route('pos.logout'));
        $response->assertSee('Keluar / Ganti Akun');
        $response->assertSee('Diego Music Store');

        // Verify operational widgets like Clock In, Off Day, Active Cash Session, and Back button are hidden in navbar
        $response->assertDontSee('Clock In');
        $response->assertDontSee('Clock Out');
        $response->assertDontSee('Off Day:');
        $response->assertDontSee('Sesi Nonaktif');
        $response->assertDontSee('Sesi Kasir Aktif');
        $response->assertDontSee('Kembali ke Dashboard');
    }

    /** @test */
    public function it_allows_user_to_logout_via_pos_logout_action(): void
    {
        $user = User::factory()->create([
            'name' => 'Karyawan Baru',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('pos.logout'));

        $response->assertRedirect(route('pos.login'));
        $this->assertGuest();
    }

    /** @test */
    public function it_renders_404_page_with_pos_navbar_for_authenticated_user(): void
    {
        $branch = Branch::create([
            'name'      => 'Cabang Jakarta',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'Staff Penjualan',
        ]);
        $user->branches()->attach($branch->id);

        $this->actingAs($user);

        $response = $this->get('/pos/halaman-tidak-ada-random-123');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Staff Penjualan');
        $response->assertSee(route('pos.logout'));
        $response->assertSee(route('pos.front-office'));
    }

    /** @test */
    public function it_renders_404_page_with_pos_navbar_for_guest_without_crashing(): void
    {
        $response = $this->get('/halaman-publik-palsu-456');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee(route('pos.login'));
    }
}

