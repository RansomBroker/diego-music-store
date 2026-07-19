<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\SaleCategory;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosSaleCategoriesTest extends TestCase
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
    }

    /** @test */
    public function it_can_render_the_pos_sale_categories_page()
    {
        $response = $this->get(route('pos.sale-categories'));

        $response->assertStatus(200);
        $response->assertSee('Kategori Penjualan');
    }

    /** @test */
    public function it_lists_sale_categories()
    {
        // Delete seeded ones to control the listing
        SaleCategory::query()->delete();

        SaleCategory::create(['name' => 'OfflineExhibition']);
        SaleCategory::create(['name' => 'InternetSales']);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->assertSee('OfflineExhibition')
            ->assertSee('InternetSales');
    }

    /** @test */
    public function it_can_search_sale_categories()
    {
        SaleCategory::query()->delete();

        SaleCategory::create(['name' => 'OfflineExhibition']);
        SaleCategory::create(['name' => 'InternetSales']);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->set('search', 'InternetSales')
            ->assertSee('InternetSales')
            ->assertDontSee('OfflineExhibition');
    }

    /** @test */
    public function it_can_sort_sale_categories()
    {
        SaleCategory::query()->delete();

        SaleCategory::create(['name' => 'Zack Category']);
        SaleCategory::create(['name' => 'Abdi Category']);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->assertSeeInOrder(['Abdi Category', 'Zack Category'])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Zack Category', 'Abdi Category']);
    }

    /** @test */
    public function it_can_create_a_sale_category()
    {
        Livewire::test('App\Livewire\PosSaleCategories')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'WhatsApp')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('sale_categories', [
            'name' => 'WhatsApp',
        ]);
    }

    /** @test */
    public function it_can_update_a_sale_category()
    {
        $category = SaleCategory::create(['name' => 'Bazar']);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->call('openEdit', $category->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Bazar')
            ->set('name', 'Event Bazar')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('sale_categories', [
            'id' => $category->id,
            'name' => 'Event Bazar',
        ]);
    }

    /** @test */
    public function it_can_delete_a_sale_category()
    {
        $category = SaleCategory::create(['name' => 'Event']);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->call('confirmDelete', $category->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('sale_categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_sale_category_used_by_sales()
    {
        $category = SaleCategory::create(['name' => 'Exhibition']);

        // Create a cash session and a sale
        $session = \App\Models\CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 100000,
        ]);

        Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-TEST-123',
            'invoice_date' => now(),
            'sale_category' => 'Exhibition',
            'payment_method' => 'cash',
            'status' => 'completed',
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1000,
            'created_by' => $this->user->id,
        ]);

        Livewire::test('App\Livewire\PosSaleCategories')
            ->call('confirmDelete', $category->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        // Should still exist in database
        $this->assertDatabaseHas('sale_categories', [
            'id' => $category->id,
        ]);
    }
}
