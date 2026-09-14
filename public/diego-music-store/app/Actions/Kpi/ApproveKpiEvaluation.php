<?php

namespace App\Actions\Kpi;

use App\Models\KpiEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveKpiEvaluation
{
    /**
     * Approve a KPI evaluation record for payroll processing.
     *
     * @param int $evaluationId
     * @param User $approver
     * @return KpiEvaluation
     */
    public function execute(int $evaluationId, User $approver): KpiEvaluation
    {
        return DB::transaction(function () use ($evaluationId, $approver) {
            $evaluation = KpiEvaluation::findOrFail($evaluationId);

            $evaluation->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $evaluation;
        });
    }
}
