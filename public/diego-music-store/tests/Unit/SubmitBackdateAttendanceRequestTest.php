<?php

namespace Tests\Unit;

use App\Actions\Attendance\SubmitBackdateAttendanceRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SubmitBackdateAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_backdate_request_creates_pending_request()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id'   => $user->id,
            'name'      => 'Ahmad Staf',
            'nik'       => 'EMP-0012',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $action = new SubmitBackdateAttendanceRequest();
        $request = $action->execute($employee, [
            'requested_date' => now()->subDay()->format('Y-m-d'),
            'clock_in'       => '08:30:00',
            'clock_out'      => '17:00:00',
            'reason'         => 'Lupa clock in karena tugas luar cabang',
        ]);

        $this->assertDatabaseHas('attendance_backdate_requests', [
            'id'          => $request->id,
            'employee_id' => $employee->id,
            'reason'      => 'Lupa clock in karena tugas luar cabang',
            'status'      => 'pending',
        ]);
    }

    public function test_submit_backdate_request_throws_error_for_future_date()
    {
        $this->expectException(InvalidArgumentException::class);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id'   => $user->id,
            'name'      => 'Ahmad Staf',
            'nik'       => 'EMP-0013',
            'is_active' => true,
        ]);

        $action = new SubmitBackdateAttendanceRequest();
        $action->execute($employee, [
            'requested_date' => now()->addDay()->format('Y-m-d'),
            'reason'         => 'Backdate tanggal besok',
        ]);
    }
}
