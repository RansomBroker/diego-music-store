<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use App\Models\SaleCategory;
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

        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole($ownerRole);
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
        $saleCategory = SaleCategory::firstOrCreate(
            ['name' => 'Online'],
            ['prefix' => 'ONL', 'start_alphabet' => 'A', 'end_alphabet' => 'Z', 'digit_length' => 4]
        );

        Livewire::test('App\Livewire\PosCustomers')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Candra')
            ->set('phone', '083333')
            ->set('email', 'candra@example.com')
            ->set('address', 'Jl. Cempaka')
            ->set('customer_label_id', $label->id)
            ->set('pricing_tier_id', $tier->id)
            ->set('sale_category_id', $saleCategory->id)
            ->set('is_loyalty_member', true)
            ->set('loyalty_points', 50)
            ->call('save')
            ->assertSet('showModal', false)
            ->assertSee('Online');

        $this->assertDatabaseHas('customers', [
            'name' => 'Candra',
            'phone' => '083333',
            'email' => 'candra@example.com',
            'address' => 'Jl. Cempaka',
            'customer_label_id' => $label->id,
            'pricing_tier_id' => $tier->id,
            'sale_category_id' => $saleCategory->id,
            'is_loyalty_member' => true,
            'loyalty_points' => 50,
        ]);
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $storeCategory = SaleCategory::firstOrCreate(
            ['name' => 'Store'],
            ['prefix' => 'STR', 'start_alphabet' => 'A', 'end_alphabet' => 'Z', 'digit_length' => 4]
        );
        $onlineCategory = SaleCategory::firstOrCreate(
            ['name' => 'Online'],
            ['prefix' => 'ONL', 'start_alphabet' => 'A', 'end_alphabet' => 'Z', 'digit_length' => 4]
        );

        $customer = Customer::create([
            'name' => 'Dedi',
            'phone' => '084444',
            'sale_category_id' => $storeCategory->id,
        ]);

        Livewire::test('App\Livewire\PosCustomers')
            ->call('openEdit', $customer->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Dedi')
            ->assertSet('sale_category_id', $storeCategory->id)
            ->set('name', 'Dedi Hermawan')
            ->set('phone', '084445')
            ->set('sale_category_id', $onlineCategory->id)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Dedi Hermawan',
            'phone' => '084445',
            'sale_category_id' => $onlineCategory->id,
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
