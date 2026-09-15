<?php

namespace Tests\Feature;

use App\Actions\Attendance\ProcessBackdateAttendanceApproval;
use App\Models\AttendanceBackdateRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessBackdateAttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_approval_creates_attendance_record_and_approves_request()
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id'   => $user->id,
            'name'      => 'Budi Kasir',
            'nik'       => 'EMP-0014',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $backdateDate = now()->subDays(2)->format('Y-m-d');
        $backdateRequest = AttendanceBackdateRequest::create([
            'employee_id'    => $employee->id,
            'branch_id'      => $branch->id,
            'requested_date' => $backdateDate,
            'clock_in'       => '09:00:00',
            'clock_out'      => '17:00:00',
            'reason'         => 'Kendala koneksi internet saat presensi',
            'status'         => 'pending',
        ]);

        $action = new ProcessBackdateAttendanceApproval();
        $result = $action->execute($backdateRequest, 'approve', $owner, 'Disetujui setelah konfirmasi supervisor');

        $this->assertTrue($result);

        $this->assertDatabaseHas('attendance_backdate_requests', [
            'id'          => $backdateRequest->id,
            'status'      => 'approved',
            'approved_by' => $owner->id,
        ]);

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'date'        => $backdateDate . ' 00:00:00',
            'status'      => 'hadir',
        ]);
    }
}
