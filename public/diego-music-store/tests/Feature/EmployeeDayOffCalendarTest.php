<?php

namespace Tests\Feature;

use App\Livewire\EmployeeDayOffCalendar;
use App\Models\EmployeeDayOff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeDayOffCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_register_themselves_for_a_valid_day_off_date(): void
    {
        $user = User::factory()->create();
        $employee = $user->employee;

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openDate', now()->addDays(2)->toDateString())
            ->call('registerMyself')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_day_offs', [
            'employee_id' => $employee->id,
            'off_date' => now()->addDays(2)->toDateString(),
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    public function test_employee_cannot_register_for_a_date_outside_the_allowed_window(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->set('selectedDate', now()->addMonths(2)->addDays(2)->toDateString())
            ->call('registerMyself')
            ->assertHasErrors('selectedDate');

        $this->assertDatabaseCount('employee_day_offs', 0);
    }

    public function test_employee_cannot_register_twice_for_the_same_active_date(): void
    {
        $user = User::factory()->create();
        $employee = $user->employee;
        $date = now()->addDays(3)->toDateString();

        EmployeeDayOff::create([
            'employee_id' => $employee->id,
            'off_date' => $date,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->set('selectedDate', $date)
            ->call('registerMyself')
            ->assertHasErrors('selectedDate');

        $this->assertDatabaseCount('employee_day_offs', 1);
    }

    public function test_owner_can_cancel_only_the_selected_employee_day_off(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $employeeA = User::factory()->create()->employee;
        $employeeB = User::factory()->create()->employee;
        $date = now()->addDays(4)->toDateString();

        $dayOffA = EmployeeDayOff::create([
            'employee_id' => $employeeA->id,
            'off_date' => $date,
            'status' => 'active',
            'created_by' => $employeeA->user_id,
        ]);
        $dayOffB = EmployeeDayOff::create([
            'employee_id' => $employeeB->id,
            'off_date' => $date,
            'status' => 'active',
            'created_by' => $employeeB->user_id,
        ]);

        $this->actingAs($owner);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openCancelModal', $dayOffA->id)
            ->set('cancellationReason', 'Penyesuaian jadwal toko')
            ->call('cancelSelectedDayOff')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOffA->id,
            'status' => 'cancelled',
            'cancelled_by' => $owner->id,
            'cancellation_reason' => 'Penyesuaian jadwal toko',
        ]);
        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOffB->id,
            'status' => 'active',
            'cancelled_by' => null,
        ]);
    }

    public function test_non_owner_cannot_cancel_a_day_off(): void
    {
        $user = User::factory()->create();
        $otherEmployee = User::factory()->create()->employee;
        $dayOff = EmployeeDayOff::create([
            'employee_id' => $otherEmployee->id,
            'off_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'created_by' => $otherEmployee->user_id,
        ]);

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openCancelModal', $dayOff->id)
            ->assertForbidden();

        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOff->id,
            'status' => 'active',
            'cancelled_by' => null,
        ]);
    }
}
