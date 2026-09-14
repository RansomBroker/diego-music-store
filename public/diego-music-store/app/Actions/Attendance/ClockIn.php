<?php

namespace App\Actions\Attendance;

use App\Helpers\GeolocationHelper;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClockIn
{
    /**
     * Execute action to clock-in an employee for today.
     *
     * @param  Employee  $employee
     * @param  int|null  $branchId
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
        ?int $branchId = null,
        ?string $notes = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $photoPath = null
    ): EmployeeAttendance {
        return DB::transaction(function () use ($employee, $branchId, $notes, $latitude, $longitude, $photoPath) {
            $today = now()->format('Y-m-d');
            $clockInTime = now();
            $targetBranchId = $branchId ?: $employee->branch_id;
            $branch = $targetBranchId ? Branch::find($targetBranchId) : null;

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
                $attendance = new EmployeeAttendance([
                    'employee_id' => $employee->id,
                    'date' => $today,
                ]);
            }

            // If already clocked in and not updating notes/photo, return existing
            if ($attendance->exists && $attendance->clock_in) {
                if ($notes) {
                    $attendance->notes = $notes;
                }
                if ($photoPath) {
                    $attendance->clock_in_photo_path = $photoPath;
                }
                $attendance->save();
                return $attendance;
            }

            $attendance->branch_id = $targetBranchId;
            $attendance->clock_in = $clockInTime;
            $attendance->status = 'hadir';
            if ($notes) {
                $attendance->notes = $notes;
            }
            if ($photoPath) {
                $attendance->clock_in_photo_path = $photoPath;
            }
            if ($latitude !== null && $longitude !== null) {
                $attendance->latitude = $latitude;
                $attendance->longitude = $longitude;
                $attendance->distance_meters = $dist;
                $attendance->is_out_of_radius = false;
            }

            // Calculate late minutes based on branch shift_start_time (default 09:00)
            $shiftStart = $branch && $branch->shift_start_time
                ? $branch->shift_start_time
                : '09:00:00';
            $shiftStartParts = explode(':', $shiftStart);
            $expectedTime = now()->setTime((int) $shiftStartParts[0], (int) $shiftStartParts[1], 0);

            if ($clockInTime->greaterThan($expectedTime)) {
                $attendance->late_minutes = (int) $expectedTime->diffInMinutes($clockInTime);
            } else {
                $attendance->late_minutes = 0;
            }

            $attendance->save();

            // Trigger Automatic Attendance Violation Calculation using branch shift times
            $shiftEnd = $branch && $branch->shift_end_time
                ? $branch->shift_end_time
                : '17:00:00';
            app(ProcessAttendanceViolations::class)->execute($attendance, $shiftStart, $shiftEnd);

            return $attendance;
        });
    }
}
