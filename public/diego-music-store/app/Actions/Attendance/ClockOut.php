<?php

namespace App\Actions\Attendance;

use App\Helpers\GeolocationHelper;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClockOut
{
    /**
     * Execute action to clock-out an employee for today.
     *
     * @param  Employee  $employee
     * @param  string|null  $notes
     * @param  float|null  $latitude
     * @param  float|null  $longitude
     * @param  string|null  $photoPath
     * @return EmployeeAttendance
     *
     * @throws InvalidArgumentException
     */
    public function execute(
        Employee $employee,
        ?string $notes = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $photoPath = null
    ): EmployeeAttendance {
        return DB::transaction(function () use ($employee, $notes, $latitude, $longitude, $photoPath) {
            $today = now()->format('Y-m-d');
            $branch = $employee->branch_id ? Branch::find($employee->branch_id) : null;

            // Strict Radius Validation
            $dist = 0;
            if ($latitude !== null && $longitude !== null) {
                $branchLat = ($branch && $branch->latitude !== null) ? (float) $branch->latitude : -0.03470087552402962;
                $branchLng = ($branch && $branch->longitude !== null) ? (float) $branch->longitude : 109.33239215349418;
                $allowedRadius = ($branch && $branch->attendance_radius_meters) ? (int) $branch->attendance_radius_meters : 100;

                $dist = (int) round(GeolocationHelper::calculateDistanceInMeters(
                    (float) $latitude,
                    (float) $longitude,
                    $branchLat,
                    $branchLng
                ));

                if ($dist > $allowedRadius) {
                    throw new InvalidArgumentException("Gagal presensi! Anda berada {$dist} meter dari lokasi cabang (Batas maksimal radius: {$allowedRadius} meter). Mohon lakukan presensi dari area cabang.");
                }
            }

            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance) {
                // If not clocked in yet, create record with clock out
                $attendance = new EmployeeAttendance([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id,
                    'date' => $today,
                    'status' => 'hadir',
                ]);
            }

            $attendance->clock_out = now();
            if ($notes) {
                $attendance->notes = $notes;
            }
            if ($photoPath) {
                $attendance->clock_out_photo_path = $photoPath;
            }
            if ($latitude !== null && $longitude !== null) {
                $attendance->latitude = $latitude;
                $attendance->longitude = $longitude;
                $attendance->distance_meters = $dist;
                $attendance->is_out_of_radius = false;
            }

            $attendance->save();

            // Trigger Automatic Attendance Violation Calculation using branch shift times
            $shiftStart = $branch && $branch->shift_start_time
                ? $branch->shift_start_time
                : '09:00:00';
            $shiftEnd = $branch && $branch->shift_end_time
                ? $branch->shift_end_time
                : '17:00:00';
            app(ProcessAttendanceViolations::class)->execute($attendance, $shiftStart, $shiftEnd);

            return $attendance;
        });
    }
}
