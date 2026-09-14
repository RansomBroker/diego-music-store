<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceViolationLog;
use App\Models\AttendanceViolationRule;
use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessAttendanceViolations
{
    /**
     * Process attendance violations for a given attendance record.
     *
     * @param EmployeeAttendance $attendance
     * @param string $expectedClockIn '08:00'
     * @param string $expectedClockOut '17:00'
     * @return array<AttendanceViolationLog>
     */
    public function execute(EmployeeAttendance $attendance, string $expectedClockIn = '09:00:00', string $expectedClockOut = '17:00:00'): array
    {
        return DB::transaction(function () use ($attendance, $expectedClockIn, $expectedClockOut) {
            $generatedLogs = [];
            $dateStr = Carbon::parse($attendance->date)->format('Y-m-d');
            $payrollPeriod = Carbon::parse($attendance->date)->format('Y-m');

            // 1. Process Late In Violation
            if ($attendance->clock_in) {
                $expectedInTime = Carbon::parse($dateStr . ' ' . $expectedClockIn);
                $actualInTime = Carbon::parse($dateStr . ' ' . $attendance->clock_in->format('H:i:s'));

                if ($actualInTime->gt($expectedInTime)) {
                    $lateMinutes = (int) $expectedInTime->diffInMinutes($actualInTime);

                    // Find matching rule
                    $rule = AttendanceViolationRule::where('is_active', true)
                        ->where('violation_type', 'late_in')
                        ->where('min_minutes', '<=', $lateMinutes)
                        ->where(function ($q) use ($lateMinutes) {
                            $q->whereNull('max_minutes')
                                ->orWhere('max_minutes', '>=', $lateMinutes);
                        })
                        ->orderBy('min_minutes', 'desc')
                        ->first();

                    if ($rule) {
                        $deductionAmount = 0.0;
                        if ($rule->deduction_type === 'percentage_per_minute') {
                            $deductionAmount = (float) $rule->deduction_amount * $lateMinutes;
                        } else {
                            $deductionAmount = (float) $rule->deduction_amount;
                        }

                        $log = AttendanceViolationLog::updateOrCreate(
                            [
                                'employee_id' => $attendance->employee_id,
                                'employee_attendance_id' => $attendance->id,
                                'violation_type' => 'late_in',
                            ],
                            [
                                'violation_rule_id' => $rule->id,
                                'date' => $dateStr,
                                'late_early_minutes' => $lateMinutes,
                                'deduction_amount' => $deductionAmount,
                                'notes' => "Terlambat masuk {$lateMinutes} menit (Aturan: {$rule->name})",
                                'status' => 'pending',
                                'payroll_period' => $payrollPeriod,
                            ]
                        );

                        $generatedLogs[] = $log;
                    }
                }
            }

            // 2. Process Early Out Violation
            if ($attendance->clock_out) {
                $expectedOutTime = Carbon::parse($dateStr . ' ' . $expectedClockOut);
                $actualOutTime = Carbon::parse($dateStr . ' ' . $attendance->clock_out->format('H:i:s'));

                if ($actualOutTime->lt($expectedOutTime)) {
                    $earlyMinutes = (int) $actualOutTime->diffInMinutes($expectedOutTime);

                    $rule = AttendanceViolationRule::where('is_active', true)
                        ->where('violation_type', 'early_out')
                        ->where('min_minutes', '<=', $earlyMinutes)
                        ->where(function ($q) use ($earlyMinutes) {
                            $q->whereNull('max_minutes')
                                ->orWhere('max_minutes', '>=', $earlyMinutes);
                        })
                        ->orderBy('min_minutes', 'desc')
                        ->first();

                    if ($rule) {
                        $deductionAmount = 0.0;
                        if ($rule->deduction_type === 'percentage_per_minute') {
                            $deductionAmount = (float) $rule->deduction_amount * $earlyMinutes;
                        } else {
                            $deductionAmount = (float) $rule->deduction_amount;
                        }

                        $log = AttendanceViolationLog::updateOrCreate(
                            [
                                'employee_id' => $attendance->employee_id,
                                'employee_attendance_id' => $attendance->id,
                                'violation_type' => 'early_out',
                            ],
                            [
                                'violation_rule_id' => $rule->id,
                                'date' => $dateStr,
                                'late_early_minutes' => $earlyMinutes,
                                'deduction_amount' => $deductionAmount,
                                'notes' => "Pulang cepat {$earlyMinutes} menit (Aturan: {$rule->name})",
                                'status' => 'pending',
                                'payroll_period' => $payrollPeriod,
                            ]
                        );

                        $generatedLogs[] = $log;
                    }
                }
            }

            return $generatedLogs;
        });
    }
}
