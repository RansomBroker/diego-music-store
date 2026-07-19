<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Kiki',
            'username' => 'kiki_admin',
            'email' => 'kiki@example.com',
        ]);
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);

        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_can_render_the_pos_users_page()
    {
        $response = $this->get(route('pos.users'));

        $response->assertStatus(200);
        $response->assertSee('Data User');
    }

    /** @test */
    public function it_lists_users()
    {
        Livewire::test('App\Livewire\PosUsers')
            ->assertSee('Kiki')
            ->assertSee('kiki_admin')
            ->assertSee('kiki@example.com');
    }

    /** @test */
    public function it_can_search_users()
    {
        User::factory()->create([
            'name' => 'Asep',
            'username' => 'asep_cashier',
            'email' => 'asep@example.com',
        ]);
        User::factory()->create([
            'name' => 'Budi',
            'username' => 'budi_sales',
            'email' => 'budi@example.com',
        ]);

        Livewire::test('App\Livewire\PosUsers')
            ->set('search', 'Asep')
            ->assertSee('Asep')
            ->assertDontSee('Budi');
    }

    /** @test */
    public function it_can_sort_users()
    {
        User::factory()->create(['name' => 'Zack', 'username' => 'zack_sales', 'email' => 'zack@example.com']);
        User::factory()->create(['name' => 'Abdi', 'username' => 'abdi_sales', 'email' => 'abdi@example.com']);

        // Default sort is by name asc -> Abdi, Kiki, Zack
        Livewire::test('App\Livewire\PosUsers')
            ->assertSeeInOrder(['Abdi', 'Kiki', 'Zack'])
            ->call('sortBy', 'name') // Sort desc by name -> Zack, Kiki, Abdi
            ->assertSeeInOrder(['Zack', 'Kiki', 'Abdi']);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        Livewire::test('App\Livewire\PosUsers')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Budi Hartono')
            ->set('username', 'budi_hartono')
            ->set('email', 'budi.h@example.com')
            ->set('password', 'password123')
            ->set('is_active', true)
            ->set('selectedBranches', [(string)$this->branch->id])
            ->set('selectedRoles', [(string)$this->adminRole->id])
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Hartono',
            'username' => 'budi_hartono',
            'email' => 'budi.h@example.com',
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'budi.h@example.com')->first();
        $this->assertTrue($createdUser->branches->contains($this->branch->id));
        $this->assertTrue($createdUser->hasRole('admin'));
    }

    /** @test */
    public function it_can_update_a_user()
    {
        $targetUser = User::factory()->create([
            'name' => 'Danang',
            'username' => 'danang_sales',
            'email' => 'danang@example.com',
            'is_active' => true,
        ]);

        Livewire::test('App\Livewire\PosUsers')
            ->call('openEdit', $targetUser->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Danang')
            ->assertSet('is_active', true)
            ->set('name', 'Danang Hermawan')
            ->set('username', 'danang_new')
            ->set('is_active', false)
            ->set('password', '') // Biarkan kosong (tidak update password)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Danang Hermawan',
            'username' => 'danang_new',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_cannot_delete_self()
    {
        Livewire::test('App\Livewire\PosUsers')
            ->call('confirmDelete', $this->user->id)
            ->assertSet('showDeleteModal', false); // Tidak boleh membuka modal delete untuk diri sendiri
    }

    /** @test */
    public function it_can_delete_another_user()
    {
        $targetUser = User::factory()->create([
            'name' => 'Eko',
            'username' => 'eko_sales',
            'email' => 'eko@example.com',
        ]);

        Livewire::test('App\Livewire\PosUsers')
            ->call('confirmDelete', $targetUser->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }
}
