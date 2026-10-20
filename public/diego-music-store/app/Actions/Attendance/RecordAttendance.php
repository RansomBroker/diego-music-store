<?php

namespace App\Actions\Attendance;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;

class RecordAttendance
{
    /**
     * Execute action to record or update attendance for an employee on a specific date.
     *
     * @param  Employee  $employee
     * @param  array<string, mixed>  $data
     * @return EmployeeAttendance
     */
    public function execute(Employee $employee, array $data): EmployeeAttendance
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']), 403);
        $branchId = (int) ($data['branch_id'] ?? $employee->branch_id ?? 0);
        abort_unless($employee->branch_id && $branchId === (int) $employee->branch_id, 403);
        abort_unless(\App\Helpers\BranchHelper::getAllowedBranchesQuery()->where('branches.id', $branchId)->exists(), 403);

        return DB::transaction(function () use ($employee, $data, $branchId) {
            $date = $data['date'] ?? now()->format('Y-m-d');
            $status = $data['status'] ?? 'hadir';

            if (in_array($status, ['off_day', 'izin', 'sakit', 'alpha'])) {
                $existing = EmployeeAttendance::where('employee_id', $employee->id)
                    ->where('date', $date)
                    ->first();

                if ($existing && $existing->clock_in !== null) {
                    $clockInTime = $existing->clock_in ? $existing->clock_in->format('H:i') : '-';
                    throw new \InvalidArgumentException("Gagal mencatat status '{$status}'! Karyawan \"{$employee->name}\" sudah melakukan Clock In jam masuk (Jam {$clockInTime}) pada tanggal ini.");
                }
            }

            $attendance = EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ],
                [
                    'branch_id' => $branchId,
                    'clock_in' => $data['clock_in'] ?? null,
                    'clock_out' => $data['clock_out'] ?? null,
                    'status' => $status,
                    'late_minutes' => $data['late_minutes'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                    'is_backdate' => $data['is_backdate'] ?? false,
                    'approved_by' => $data['approved_by'] ?? null,
                ]
            );

            return $attendance;
        });
    }
}
