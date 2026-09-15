<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosVouchersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Voucher',
            'username' => 'admin_voucher',
            'email' => 'admin_voucher@example.com',
        ]);
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Diego',
            'address' => 'Jl. Musik No. 1',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);
    }

    /** @test */
    public function it_can_render_the_pos_vouchers_page()
    {
        $response = $this->get(route('pos.vouchers'));

        $response->assertStatus(200);
        $response->assertSee('Management Voucher Belanja');
    }

    /** @test */
    public function it_lists_vouchers()
    {
        Voucher::create([
            'code' => 'TEST50K',
            'name' => 'Diskon 50 Ribu',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\PosVouchers::class)
            ->assertSee('TEST50K')
            ->assertSee('Diskon 50 Ribu');
    }

    /** @test */
    public function it_can_create_a_voucher()
    {
        Livewire::test(\App\Livewire\PosVouchers::class)
            ->call('openCreate')
            ->set('code', 'NEWYEAR')
            ->set('name', 'Promo Tahun Baru')
            ->set('type', 'percent')
            ->set('value', 10)
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('vouchers', [
            'code' => 'NEWYEAR',
            'name' => 'Promo Tahun Baru',
            'value' => 10,
        ]);
    }

    /** @test */
    public function it_can_update_a_voucher()
    {
        $voucher = Voucher::create([
            'code' => 'OLDCODE',
            'name' => 'Nama Lama',
            'type' => 'fixed',
            'value' => 20000,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\PosVouchers::class)
            ->call('openEdit', $voucher->id)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'name' => 'Nama Baru',
        ]);
    }

    /** @test */
    public function it_can_delete_a_voucher()
    {
        $voucher = Voucher::create([
            'code' => 'DELCODE',
            'name' => 'Hapus Saya',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\PosVouchers::class)
            ->call('confirmDelete', $voucher->id)
            ->call('destroy')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('vouchers', [
            'id' => $voucher->id,
        ]);
    }
}
