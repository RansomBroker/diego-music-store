<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\CustomerLabel;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosCustomerLabelsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

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
        CustomerLabel::query()->delete();
    }

    /** @test */
    public function it_can_render_the_pos_customer_labels_page()
    {
        $response = $this->get(route('pos.customer-labels'));

        $response->assertStatus(200);
        $response->assertSee('Kategori Penjualan');
    }

    /** @test */
    public function it_lists_customer_labels()
    {
        CustomerLabel::create([
            'key' => 'perorangan',
            'name' => 'Perorangan',
        ]);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->assertSee('perorangan')
            ->assertSee('Perorangan');
    }

    /** @test */
    public function it_can_search_customer_labels()
    {
        CustomerLabel::create(['key' => 'perorangan', 'name' => 'UniqueCustomerLabel']);
        CustomerLabel::create(['key' => 'instansi', 'name' => 'Instansi']);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->set('search', 'Instansi')
            ->assertSee('Instansi')
            ->assertDontSee('UniqueCustomerLabel');
    }

    /** @test */
    public function it_can_sort_customer_labels()
    {
        CustomerLabel::create(['key' => 'zack', 'name' => 'Zack Label']);
        CustomerLabel::create(['key' => 'abdi', 'name' => 'Abdi Label']);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->assertSeeInOrder(['Abdi Label', 'Zack Label'])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Zack Label', 'Abdi Label']);
    }

    /** @test */
    public function it_can_create_a_customer_label()
    {
        Livewire::test('App\Livewire\PosCustomerLabels')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('key', 'reseller')
            ->set('name', 'Reseller Utama')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('customer_labels', [
            'key' => 'reseller',
            'name' => 'Reseller Utama',
        ]);
    }

    /** @test */
    public function it_can_update_a_customer_label()
    {
        $label = CustomerLabel::create([
            'key' => 'reseller',
            'name' => 'Reseller',
        ]);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->call('openEdit', $label->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Reseller')
            ->set('name', 'Reseller VIP')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('customer_labels', [
            'id' => $label->id,
            'name' => 'Reseller VIP',
        ]);
    }

    /** @test */
    public function it_can_delete_a_customer_label()
    {
        $label = CustomerLabel::create([
            'key' => 'vip',
            'name' => 'VIP',
        ]);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->call('confirmDelete', $label->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('customer_labels', [
            'id' => $label->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_customer_label_used_by_customers()
    {
        $label = CustomerLabel::create(['key' => 'reseller', 'name' => 'Reseller']);
        Customer::create([
            'name' => 'Budi',
            'phone' => '08123456789',
            'email' => 'budi@example.com',
            'customer_label_id' => $label->id,
        ]);

        Livewire::test('App\Livewire\PosCustomerLabels')
            ->call('confirmDelete', $label->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        // Label must still exist in the database
        $this->assertDatabaseHas('customer_labels', [
            'id' => $label->id,
        ]);
    }
}
