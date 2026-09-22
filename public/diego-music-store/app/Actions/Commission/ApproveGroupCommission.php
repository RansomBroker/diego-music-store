<?php

namespace App\Actions\Commission;

use App\Models\CommissionGroup;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveGroupCommission
{
    /**
     * Approve and post unlocked group commission to sales commission logs for the leader.
     *
     * @param  CommissionGroup  $group
     * @param  int  $year
     * @param  int  $month
     * @param  User|null  $approver
     * @return array
     */
    public function execute(CommissionGroup $group, int $year, int $month, ?User $approver = null): array
    {
        $evaluator = new EvaluateGroupCommission();
        $eval = $evaluator->execute($group, $year, $month);

        if (!$eval['is_unlocked']) {
            return [
                'success' => false,
                'message' => "Komisi grup '{$group->name}' belum dapat disetujui karena masih terkunci ({$eval['achieved_members_count']}/{$eval['total_members']} anggota achieve).",
                'log' => null,
            ];
        }

        if ($eval['commission_amount'] <= 0) {
            return [
                'success' => false,
                'message' => "Nominal komisi grup '{$group->name}' adalah Rp 0.",
                'log' => null,
            ];
        }

        return DB::transaction(function () use ($group, $year, $month, $eval, $approver) {
            $formattedMonth = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
            $periodDate = "{$year}-{$formattedMonth}-01";
            $noteKey = "Komisi Grup: {$group->name} ({$year}-{$formattedMonth})";
            $userId = $approver ? $approver->id : auth()->id();

            $log = SalesCommissionLog::updateOrCreate([
                'employee_id' => $group->leader_employee_id,
                'notes' => $noteKey,
            ], [
                'commission_scheme_id' => null,
                'sale_id' => null,
                'date' => $periodDate,
                'sale_amount' => $eval['total_group_sales'],
                'commission_amount' => $eval['commission_amount'],
                'status' => 'approved',
                'approved_by' => $userId,
            ]);

            $rupiah = 'Rp ' . number_format($eval['commission_amount'], 0, ',', '.');
            $leaderName = $group->leader?->name ?? 'Leader';

            return [
                'success' => true,
                'message' => "Komisi grup sebesar {$rupiah} berhasil disetujui & dialokasikan untuk {$leaderName}.",
                'log' => $log,
            ];
        });
    }
}
