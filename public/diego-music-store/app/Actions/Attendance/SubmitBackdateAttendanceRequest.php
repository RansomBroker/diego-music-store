<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceBackdateRequest;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class SubmitBackdateAttendanceRequest
{
    /**
     * Execute submitting a backdate attendance request.
     */
    public function execute(Employee $employee, array $data): AttendanceBackdateRequest
    {
        $user = Auth::user();
        abort_unless($user && $user->employee && (int) $user->employee->id === (int) $employee->id, 403);
        abort_unless($employee->is_active && $employee->branch_id, 403);
        abort_unless((int) ($data['branch_id'] ?? $employee->branch_id) === (int) $employee->branch_id, 403);
        abort_unless(\App\Helpers\BranchHelper::getAllowedBranchesQuery()->where('branches.id', $employee->branch_id)->exists(), 403);

        $requestedDate = Carbon::parse($data['requested_date'])->format('Y-m-d');
        $todayDate = now()->format('Y-m-d');

        if ($requestedDate > $todayDate) {
            throw new InvalidArgumentException('Tanggal presensi susulan tidak boleh di masa mendatang.');
        }

        if (empty($data['reason'])) {
            throw new InvalidArgumentException('Alasan pengajuan presensi susulan wajib diisi.');
        }

        return AttendanceBackdateRequest::create([
            'employee_id'      => $employee->id,
            'branch_id'        => $employee->branch_id,
            'requested_date'   => $requestedDate,
            'clock_in'         => $data['clock_in'] ?? '09:00:00',
            'clock_out'        => $data['clock_out'] ?? '17:00:00',
            'reason'           => $data['reason'],
            'proof_photo_path' => $data['proof_photo_path'] ?? null,
            'status'           => 'pending',
        ]);
    }
}
