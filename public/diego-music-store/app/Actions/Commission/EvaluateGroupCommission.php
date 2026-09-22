<?php

namespace App\Actions\Commission;

use App\Models\CommissionGroup;
use App\Models\Sale;
use App\Models\SalesCommissionLog;

class EvaluateGroupCommission
{
    /**
     * Evaluate group commission for a specific year and month.
     *
     * @param  CommissionGroup  $group
     * @param  int  $year
     * @param  int  $month
     * @return array
     */
    public function execute(CommissionGroup $group, int $year, int $month): array
    {
        $group->loadMissing(['leader', 'members.employee.user']);

        $members = $group->members;
        $totalMembers = $members->count();

        if ($totalMembers === 0) {
            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'leader_id' => $group->leader_employee_id,
                'leader_name' => $group->leader?->name ?? '—',
                'year' => $year,
                'month' => $month,
                'is_unlocked' => false,
                'total_members' => 0,
                'achieved_members_count' => 0,
                'unachieved_members_count' => 0,
                'total_group_sales' => 0.0,
                'rate' => (float) $group->rate,
                'commission_amount' => 0.0,
                'members_detail' => [],
            ];
        }

        $totalGroupSales = 0.0;
        $achievedCount = 0;
        $membersDetail = [];

        foreach ($members as $member) {
            $employee = $member->employee;
            if (!$employee) {
                continue;
            }

            // 1. Calculate actual sales for this employee in the month
            $logSales = (float) SalesCommissionLog::where('employee_id', $employee->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('sale_amount');

            $directSales = 0.0;
            if ($employee->user_id) {
                $directSales = (float) Sale::where('sales_rep_id', $employee->user_id)
                    ->whereYear('invoice_date', $year)
                    ->whereMonth('invoice_date', $month)
                    ->where('status', '!=', 'cancelled')
                    ->sum('grand_total');
            }

            $actualSales = max($logSales, $directSales);
            $totalGroupSales += $actualSales;

            $target = (float) $member->monthly_target_amount;
            $isAchieved = ($target <= 0) || ($actualSales >= $target);

            if ($isAchieved) {
                $achievedCount++;
            }

            $percentage = $target > 0 ? round(($actualSales / $target) * 100, 1) : 100.0;

            $membersDetail[] = [
                'employee_id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->name,
                'target_sales' => $target,
                'actual_sales' => $actualSales,
                'achievement_pct' => $percentage,
                'is_achieved' => $isAchieved,
            ];
        }

        // All members must achieve their individual target for group commission to unlock
        $isUnlocked = ($totalMembers > 0) && ($achievedCount === $totalMembers);

        $rate = (float) $group->rate;
        $commissionAmount = $isUnlocked ? round($totalGroupSales * ($rate / 100), 2) : 0.0;

        return [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'leader_id' => $group->leader_employee_id,
            'leader_name' => $group->leader?->name ?? '—',
            'year' => $year,
            'month' => $month,
            'is_unlocked' => $isUnlocked,
            'total_members' => $totalMembers,
            'achieved_members_count' => $achievedCount,
            'unachieved_members_count' => $totalMembers - $achievedCount,
            'total_group_sales' => $totalGroupSales,
            'rate' => $rate,
            'commission_amount' => $commissionAmount,
            'members_detail' => $membersDetail,
        ];
    }
}
