<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Service',
            'username' => 'admin_service',
            'email' => 'admin_service@example.com',
        ]);
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Diego Service',
            'address' => 'Jl. Service No. 10',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch);
    }

    /** @test */
    public function it_can_render_the_pos_service_management_page()
    {
        $response = $this->get(route('pos.service-management'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Barang Service & Reparasi');
    }

    /** @test */
    public function it_lists_service_orders()
    {
        $customer = Customer::create([
            'name' => 'Budi Customer',
            'phone' => '081299998888',
        ]);

        $order = ServiceOrder::create([
            'ticket_code' => 'SVC-2026-0001',
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'device_name' => 'Yamaha Keyboard PSR-SX900',
            'serial_number' => 'SN-123456',
            'complaint' => 'Mati total setelah kena air',
            'status' => 'received',
            'estimated_cost' => 500000,
        ]);

        Livewire::test(\App\Livewire\PosServiceManagement::class)
            ->assertSee('SVC-2026-0001')
            ->assertSee('Yamaha Keyboard PSR-SX900')
            ->assertSee('Budi Customer');
    }

    /** @test */
    public function it_can_update_service_order_and_dispatch_toast()
    {
        $customer = Customer::create([
            'name' => 'Doni Customer',
            'phone' => '081277776666',
        ]);

        $order = ServiceOrder::create([
            'ticket_code' => 'SVC-2026-0002',
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'device_name' => 'Fender Stratocaster',
            'status' => 'received',
            'estimated_cost' => 200000,
        ]);

        Livewire::test(\App\Livewire\PosServiceManagement::class)
            ->call('openEditModal', $order->id)
            ->set('editStatus', 'in_progress')
            ->set('editNotes', 'Sedang diganti pickup-nya')
            ->call('saveServiceOrder')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'status' => 'in_progress',
            'notes' => 'Sedang diganti pickup-nya',
        ]);
    }

    /** @test */
    public function it_can_filter_and_reset_service_orders()
    {
        $customer = Customer::create([
            'name' => 'Ferry Customer',
            'phone' => '081233334444',
        ]);

        ServiceOrder::create([
            'ticket_code' => 'SVC-2026-0003',
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'device_name' => 'Roland Drum V-Drums',
            'status' => 'completed',
            'estimated_cost' => 750000,
        ]);

        Livewire::test(\App\Livewire\PosServiceManagement::class)
            ->set('selectedStatus', 'completed')
            ->assertSee('SVC-2026-0003')
            ->set('selectedStatus', 'diagnosing')
            ->assertDontSee('SVC-2026-0003')
            ->call('resetFilters')
            ->assertSet('selectedStatus', null)
            ->assertSee('SVC-2026-0003');
    }
}
