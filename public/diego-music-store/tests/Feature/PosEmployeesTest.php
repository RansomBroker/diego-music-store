<?php

namespace Tests\Feature;

use App\Livewire\PosEmployees;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosEmployeesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_employees_component_can_render_and_create_employee(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(PosEmployees::class)
            ->assertStatus(200)
            ->set('name', 'Karyawan Baru')
            ->set('phone', '081299998888')
            ->set('monthly_off_days_quota', 4)
            ->set('basic_salary', 3500000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'name' => 'Karyawan Baru',
            'phone' => '081299998888',
        ]);
    }
}
