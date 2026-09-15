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
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employees', [
            'name' => 'Karyawan Baru',
            'phone' => '081299998888',
        ]);
    }

    public function test_pos_employees_component_can_update_and_delete_with_toast(): void
    {
        $user = User::create([
            'name' => 'Admin Test 2',
            'username' => 'admintest2',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $employee = Employee::create([
            'nik' => 'EMP-001',
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'monthly_off_days_quota' => 4,
            'basic_salary' => 3000000,
            'is_active' => true,
        ]);

        // Test Update
        Livewire::test(PosEmployees::class)
            ->call('openEdit', $employee->id)
            ->set('name', 'Budi Santoso Updated')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Budi Santoso Updated',
        ]);

        // Test Delete
        Livewire::test(PosEmployees::class)
            ->call('confirmDelete', $employee->id)
            ->call('delete')
            ->assertDispatched('toast');

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
}

