<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosCustomersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);
    }

    /** @test */
    public function it_can_render_the_pos_customers_page()
    {
        $response = $this->get(route('pos.customers'));

        $response->assertStatus(200);
        $response->assertSee('Data Pelanggan');
    }

    /** @test */
    public function it_lists_customers()
    {
        Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
        ]);

        Livewire::test('App\Livewire\PosCustomers')
            ->assertSee('Budi Santoso')
            ->assertSee('081234567890')
            ->assertSee('budi@example.com');
    }

    /** @test */
    public function it_can_search_customers()
    {
        Customer::create(['name' => 'Andi Wijaya', 'phone' => '081111']);
        Customer::create(['name' => 'Budi Santoso', 'phone' => '082222']);

        Livewire::test('App\Livewire\PosCustomers')
            ->set('search', 'Andi')
            ->assertSee('Andi Wijaya')
            ->assertDontSee('Budi Santoso');
    }

    /** @test */
    public function it_can_sort_customers()
    {
        Customer::create(['name' => 'Budi', 'loyalty_points' => 10]);
        Customer::create(['name' => 'Andi', 'loyalty_points' => 20]);

        // Default sort is by name asc -> Andi, Budi
        Livewire::test('App\Livewire\PosCustomers')
            ->assertSeeInOrder(['Andi', 'Budi'])
            ->call('sortBy', 'name') // Sort desc by name -> Budi, Andi
            ->assertSeeInOrder(['Budi', 'Andi']);
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        $label = CustomerLabel::create(['key' => 'vip', 'name' => 'VIP']);
        $tier = PricingTier::create(['name' => 'Grosir', 'price_follows_hpp' => false]);

        Livewire::test('App\Livewire\PosCustomers')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Candra')
            ->set('phone', '083333')
            ->set('email', 'candra@example.com')
            ->set('address', 'Jl. Cempaka')
            ->set('customer_label_id', $label->id)
            ->set('pricing_tier_id', $tier->id)
            ->set('is_loyalty_member', true)
            ->set('loyalty_points', 50)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('customers', [
            'name' => 'Candra',
            'phone' => '083333',
            'email' => 'candra@example.com',
            'address' => 'Jl. Cempaka',
            'customer_label_id' => $label->id,
            'pricing_tier_id' => $tier->id,
            'is_loyalty_member' => true,
            'loyalty_points' => 50,
        ]);
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $customer = Customer::create([
            'name' => 'Dedi',
            'phone' => '084444',
        ]);

        Livewire::test('App\Livewire\PosCustomers')
            ->call('openEdit', $customer->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Dedi')
            ->set('name', 'Dedi Hermawan')
            ->set('phone', '084445')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Dedi Hermawan',
            'phone' => '084445',
        ]);
    }

    /** @test */
    public function it_can_delete_a_customer()
    {
        $customer = Customer::create([
            'name' => 'Eko',
            'phone' => '085555',
        ]);

        Livewire::test('App\Livewire\PosCustomers')
            ->call('confirmDelete', $customer->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
