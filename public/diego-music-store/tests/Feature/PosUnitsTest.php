<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosUnitsTest extends TestCase
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
    public function it_can_render_the_pos_units_page()
    {
        $response = $this->get(route('pos.units'));

        $response->assertStatus(200);
        $response->assertSee('Satuan Barang');
    }

    /** @test */
    public function it_lists_units()
    {
        Unit::create([
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_active' => true,
        ]);

        Livewire::test('App\Livewire\PosUnits')
            ->assertSee('Pieces')
            ->assertSee('pcs');
    }

    /** @test */
    public function it_can_search_units()
    {
        $base = Unit::create(['name' => 'BaseUnit', 'code' => 'bs', 'is_active' => true]);
        Unit::create(['name' => 'UniqueItemUnit', 'code' => 'uiu', 'base_unit_id' => $base->id, 'is_active' => true]);
        Unit::create(['name' => 'Box', 'code' => 'box', 'base_unit_id' => $base->id, 'is_active' => true]);

        Livewire::test('App\Livewire\PosUnits')
            ->set('search', 'Box')
            ->assertSee('Box')
            ->assertDontSee('UniqueItemUnit');
    }

    /** @test */
    public function it_can_sort_units()
    {
        Unit::create(['name' => 'Zack', 'code' => 'zck']);
        Unit::create(['name' => 'Abdi', 'code' => 'abd']);

        Livewire::test('App\Livewire\PosUnits')
            ->assertSeeInOrder(['Abdi', 'Zack'])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Zack', 'Abdi']);
    }

    /** @test */
    public function it_can_create_a_unit()
    {
        $baseUnit = Unit::create(['name' => 'Pieces', 'code' => 'pcs', 'is_active' => true]);

        Livewire::test('App\Livewire\PosUnits')
            ->call('openCreate')
            ->assertSet('showModal', true)
            ->set('name', 'Lusin')
            ->set('code', 'lsn')
            ->set('base_unit_id', (string)$baseUnit->id)
            ->set('conversion_factor', 12)
            ->set('is_active', true)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('units', [
            'name' => 'Lusin',
            'code' => 'lsn',
            'base_unit_id' => $baseUnit->id,
            'conversion_factor' => 12,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_update_a_unit()
    {
        $unit = Unit::create([
            'name' => 'Pieces',
            'code' => 'pcs',
            'is_active' => true,
        ]);

        Livewire::test('App\Livewire\PosUnits')
            ->call('openEdit', $unit->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Pieces')
            ->set('name', 'Pieces New')
            ->set('is_active', false)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Pieces New',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_can_delete_a_unit()
    {
        $unit = Unit::create([
            'name' => 'Box',
            'code' => 'box',
            'is_active' => true,
        ]);

        Livewire::test('App\Livewire\PosUnits')
            ->call('confirmDelete', $unit->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_unit_used_as_base_unit()
    {
        $baseUnit = Unit::create(['name' => 'Pieces', 'code' => 'pcs', 'is_active' => true]);
        $subUnit = Unit::create(['name' => 'Lusin', 'code' => 'lsn', 'base_unit_id' => $baseUnit->id, 'conversion_factor' => 12]);

        Livewire::test('App\Livewire\PosUnits')
            ->call('confirmDelete', $baseUnit->id)
            ->assertSet('showDeleteModal', true)
            ->call('destroy')
            ->assertSet('showDeleteModal', false);

        // Base unit must still exist in the database
        $this->assertDatabaseHas('units', [
            'id' => $baseUnit->id,
        ]);
    }
}
