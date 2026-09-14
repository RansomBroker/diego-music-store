<?php

namespace App\Actions\Kpi;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\KpiEvaluation;
use App\Models\KpiTemplate;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateEmployeeKpi
{
    /**
     * Calculate realtime KPI achievement score & earned bonus for an employee for a specific period (YYYY-MM).
     *
     * @param Employee $employee
     * @param string|null $period Format YYYY-MM
     * @param KpiTemplate|null $templateOverride
     * @return KpiEvaluation|null
     */
    public function execute(Employee $employee, ?string $period = null, ?KpiTemplate $templateOverride = null): ?KpiEvaluation
    {
        return DB::transaction(function () use ($employee, $period, $templateOverride) {
            $period = $period ?: now()->format('Y-m');
            $year = (int) substr($period, 0, 4);
            $month = (int) substr($period, 5, 2);

            // 1. Resolve KPI Template
            $template = $templateOverride;
            if (!$template) {
                // Priority 1: Specific Employee Template
                $template = KpiTemplate::where('is_active', true)
                    ->where('employee_id', $employee->id)
                    ->first();

                // Priority 2: Position/Job Title Template
                if (!$template && !empty($employee->position)) {
                    $template = KpiTemplate::where('is_active', true)
                        ->whereNull('employee_id')
                        ->where('position', $employee->position)
                        ->first();
                }

                // Priority 3: General Default Template
                if (!$template) {
                    $template = KpiTemplate::where('is_active', true)
                        ->whereNull('employee_id')
                        ->first();
                }
            }

            if (!$template) {
                return null;
            }

            // 2. Calculate Sales & ATV Performance
            $salesQuery = Sale::query()
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month);

            if ($employee->user_id) {
                $salesQuery->where(function ($q) use ($employee) {
                    $q->where('sales_rep_id', $employee->user_id)
                      ->orWhere('created_by', $employee->user_id);
                });
            } else {
                $salesQuery->where('id', 0); // No user connected
            }

            $actualSalesAmount = (float) $salesQuery->sum('grand_total');
            $salesCount = (int) $salesQuery->count();
            $actualAtvAmount = $salesCount > 0 ? ($actualSalesAmount / $salesCount) : 0.0;

            // 3. Calculate Attendance & Punctuality Performance
            $attendances = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $totalWorkingDaysInMonth = 26; // Standard 26 working days per month
            $attendedDays = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $actualAttendancePct = round(($attendedDays / $totalWorkingDaysInMonth) * 100, 2);

            $punctualDays = $attendances->where('late_minutes', 0)->whereIn('status', ['hadir'])->count();
            $actualPunctualityPct = $attendedDays > 0 ? round(($punctualDays / $attendedDays) * 100, 2) : 100.0;

            // 4. Calculate Indicator Scores (0 - 100%)
            $salesScore = $template->target_sales_amount > 0 
                ? min(100.0, round(($actualSalesAmount / $template->target_sales_amount) * 100, 2))
                : 100.0;

            $atvScore = $template->target_atv_amount > 0 
                ? min(100.0, round(($actualAtvAmount / $template->target_atv_amount) * 100, 2))
                : 100.0;

            $attendanceScore = $template->target_attendance_pct > 0 
                ? min(100.0, round(($actualAttendancePct / $template->target_attendance_pct) * 100, 2))
                : 100.0;

            $punctualityScore = $template->target_punctuality_pct > 0 
                ? min(100.0, round(($actualPunctualityPct / $template->target_punctuality_pct) * 100, 2))
                : 100.0;

            // 5. Calculate Weighted Composite Final KPI Score
            $totalWeight = $template->weight_sales + $template->weight_atv + $template->weight_attendance + $template->weight_punctuality;
            $totalWeight = $totalWeight > 0 ? $totalWeight : 100.0;

            $weightedScore = (
                ($salesScore * $template->weight_sales) +
                ($atvScore * $template->weight_atv) +
                ($attendanceScore * $template->weight_attendance) +
                ($punctualityScore * $template->weight_punctuality)
            ) / $totalWeight;

            $finalKpiScore = min(100.0, round($weightedScore, 2));

            // 6. Calculate Earned Bonus Based on Tiering Rules
            $earnedBonusAmount = 0.0;
            $tierRules = $template->bonus_tiering_rules ?: [
                ['min_score' => 90, 'bonus_pct' => 100],
                ['min_score' => 75, 'bonus_pct' => 75],
                ['min_score' => 0, 'bonus_pct' => 0],
            ];

            // Sort tiering rules descending by min_score
            usort($tierRules, fn ($a, $b) => $b['min_score'] <=> $a['min_score']);

            foreach ($tierRules as $rule) {
                $minScore = (float) ($rule['min_score'] ?? 0);
                $bonusPct = (float) ($rule['bonus_pct'] ?? 0);

                if ($finalKpiScore >= $minScore) {
                    $earnedBonusAmount = $template->max_bonus_amount * ($bonusPct / 100);
                    break;
                }
            }

            // 7. Update or Create Evaluation Log Record
            $evaluation = KpiEvaluation::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period' => $period,
                ],
                [
                    'kpi_template_id' => $template->id,
                    'branch_id' => $employee->branch_id,
                    'actual_sales_amount' => $actualSalesAmount,
                    'sales_score' => $salesScore,
                    'actual_atv_amount' => $actualAtvAmount,
                    'atv_score' => $atvScore,
                    'actual_attendance_pct' => $actualAttendancePct,
                    'attendance_score' => $attendanceScore,
                    'actual_punctuality_pct' => $actualPunctualityPct,
                    'punctuality_score' => $punctualityScore,
                    'final_kpi_score' => $finalKpiScore,
                    'earned_bonus_amount' => $earnedBonusAmount,
                    'notes' => "Evaluasi KPI Periode {$period} (Template: {$template->name})",
                ]
            );

            return $evaluation;
        });
    }
}
