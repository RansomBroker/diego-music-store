<?php

namespace Tests\Feature;

use App\Livewire\PosEmployeeTasks;
use App\Livewire\PosNotificationDrawer;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Models\User;
use App\Notifications\NewEmployeeTaskNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_task_for_employees()
    {
        Notification::fake();

        // Arrange
        $branch = \App\Models\Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $employeeUser1 = User::factory()->create();
        $employeeUser2 = User::factory()->create();

        $employee1 = $employeeUser1->employee;
        $employee1->update(['branch_id' => $branch->id, 'is_active' => true, 'nik' => '123', 'name' => 'A']);
        $employee2 = $employeeUser2->employee;
        $employee2->update(['branch_id' => $branch->id, 'is_active' => true, 'nik' => '456', 'name' => 'B']);

        // Act
        Livewire::actingAs($owner)
            ->test(PosEmployeeTasks::class)
            ->set('title', 'Bersihkan Gudang')
            ->set('description', 'Tolong bersihkan gudang belakang.')
            ->set('employee_ids', [$employee1->id, $employee2->id])
            ->call('saveTask');

        // Assert Task Created
        $this->assertDatabaseHas('employee_tasks', [
            'owner_id' => $owner->id,
            'employee_id' => $employee1->id,
            'title' => 'Bersihkan Gudang',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('employee_tasks', [
            'owner_id' => $owner->id,
            'employee_id' => $employee2->id,
            'title' => 'Bersihkan Gudang',
            'status' => 'pending',
        ]);

        // Assert Notification Sent
        Notification::assertSentTo($employeeUser1, NewEmployeeTaskNotification::class);
        Notification::assertSentTo($employeeUser2, NewEmployeeTaskNotification::class);
    }

    public function test_employee_can_mark_task_as_completed_via_drawer()
    {
        // Arrange
        $owner = User::factory()->create();
        $employeeUser = User::factory()->create();
        $employee = Employee::create(['user_id' => $employeeUser->id, 'is_active' => true, 'nik' => '123', 'name' => 'A']);

        $task = EmployeeTask::create([
            'owner_id' => $owner->id,
            'employee_id' => $employee->id,
            'title' => 'Tugas A',
            'status' => 'pending',
        ]);

        $employeeUser->notify(new NewEmployeeTaskNotification($task));
        $notification = $employeeUser->notifications()->first();

        // Act
        Livewire::actingAs($employeeUser)
            ->test(PosNotificationDrawer::class)
            ->call('completeTask', $notification->id, $task->id);

        // Assert
        $this->assertDatabaseHas('employee_tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);

        // Assert Notification Marked as Read
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_owner_can_approve_completed_task()
    {
        // Arrange
        $branch = \App\Models\Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $task = EmployeeTask::create([
            'owner_id' => $owner->id,
            'employee_id' => Employee::create(['branch_id' => $branch->id, 'is_active' => true, 'nik' => '123', 'name' => 'A'])->id,
            'title' => 'Tugas A',
            'status' => 'completed',
        ]);

        // Act
        Livewire::actingAs($owner)
            ->test(PosEmployeeTasks::class)
            ->call('approveTask', $task->id);

        // Assert
        $this->assertDatabaseHas('employee_tasks', [
            'id' => $task->id,
            'status' => 'approved',
        ]);
    }

    public function test_owner_can_reject_completed_task()
    {
        Notification::fake();

        // Arrange
        $branch = \App\Models\Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner = User::factory()->create();
        $owner->assignRole($ownerRole);
        $employeeUser = User::factory()->create();
        $employee = $employeeUser->employee;
        $employee->update(['branch_id' => $branch->id, 'is_active' => true, 'nik' => '123', 'name' => 'A']);

        $task = EmployeeTask::create([
            'owner_id' => $owner->id,
            'employee_id' => $employee->id,
            'title' => 'Tugas A',
            'status' => 'completed',
        ]);

        // Act
        Livewire::actingAs($owner)
            ->test(PosEmployeeTasks::class)
            ->call('rejectTask', $task->id);

        // Assert
        $this->assertDatabaseHas('employee_tasks', [
            'id' => $task->id,
            'status' => 'rejected',
        ]);

        // Assert Re-notification sent to Employee
        Notification::assertSentTo($employeeUser, NewEmployeeTaskNotification::class);
    }
}
