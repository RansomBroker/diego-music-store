<?php

namespace Database\Seeders;

use App\Models\AttendanceViolationLog;
use App\Models\AttendanceViolationRule;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AttendanceViolationRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Comprehensive Rules
        $rule1 = AttendanceViolationRule::firstOrCreate(
            ['name' => 'Terlambat Ringan (1 - 15 Menit)'],
            [
                'violation_type' => 'late_in',
                'min_minutes' => 1,
                'max_minutes' => 15,
                'deduction_type' => 'fixed_amount',
                'deduction_amount' => 10000,
                'is_active' => true,
            ]
        );

        $rule2 = AttendanceViolationRule::firstOrCreate(
            ['name' => 'Terlambat Sedang (16 - 30 Menit)'],
            [
                'violation_type' => 'late_in',
                'min_minutes' => 16,
                'max_minutes' => 30,
                'deduction_type' => 'fixed_amount',
                'deduction_amount' => 25000,
                'is_active' => true,
            ]
        );

        $rule3 = AttendanceViolationRule::firstOrCreate(
            ['name' => 'Terlambat Berat (>30 Menit)'],
            [
                'violation_type' => 'late_in',
                'min_minutes' => 31,
                'max_minutes' => null,
                'deduction_type' => 'fixed_amount',
                'deduction_amount' => 50000,
                'is_active' => true,
            ]
        );

        $rule4 = AttendanceViolationRule::firstOrCreate(
            ['name' => 'Pulang Cepat (Sebelum Jam Shift Selesai)'],
            [
                'violation_type' => 'early_out',
                'min_minutes' => 1,
                'max_minutes' => null,
                'deduction_type' => 'fixed_amount',
                'deduction_amount' => 20000,
                'is_active' => true,
            ]
        );

        $rule5 = AttendanceViolationRule::firstOrCreate(
            ['name' => 'Mangkir / Absen Tanpa Keterangan'],
            [
                'violation_type' => 'unexcused_absence',
                'min_minutes' => 0,
                'max_minutes' => null,
                'deduction_type' => 'fixed_amount',
                'deduction_amount' => 100000,
                'is_active' => true,
            ]
        );

        // 2. Seed Sample Logs across active employees
        $employees = Employee::where('is_active', true)->get();
        if ($employees->count() > 0) {
            foreach ($employees->take(5) as $index => $emp) {
                // Sample Late In Log
                AttendanceViolationLog::firstOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'date' => now()->subDays($index * 2 + 1)->format('Y-m-d'),
                        'violation_type' => 'late_in',
                    ],
                    [
                        'violation_rule_id' => $index % 2 === 0 ? $rule1->id : $rule2->id,
                        'late_early_minutes' => $index % 2 === 0 ? 12 : 22,
                        'deduction_amount' => $index % 2 === 0 ? 10000 : 25000,
                        'notes' => $index % 2 === 0 ? 'Terlambat masuk 12 menit (Aturan: Terlambat Ringan)' : 'Terlambat masuk 22 menit (Aturan: Terlambat Sedang)',
                        'status' => $index === 0 ? 'approved' : 'pending',
                        'payroll_period' => now()->format('Y-m'),
                    ]
                );

                // Sample Early Out Log for some employees
                if ($index % 2 === 1) {
                    AttendanceViolationLog::firstOrCreate(
                        [
                            'employee_id' => $emp->id,
                            'date' => now()->subDays($index + 3)->format('Y-m-d'),
                            'violation_type' => 'early_out',
                        ],
                        [
                            'violation_rule_id' => $rule4->id,
                            'late_early_minutes' => 18,
                            'deduction_amount' => 20000,
                            'notes' => 'Pulang cepat 18 menit (Aturan: Pulang Cepat)',
                            'status' => 'pending',
                            'payroll_period' => now()->format('Y-m'),
                        ]
                    );
                }
            }
        }
    }
}
