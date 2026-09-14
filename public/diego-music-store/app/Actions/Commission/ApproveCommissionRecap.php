<?php

namespace App\Actions\Commission;

use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveCommissionRecap
{
    /**
     * Execute action to approve pending commission logs for an employee or date range.
     *
     * @param  int  $employeeId
     * @param  string  $yearMonth Format: YYYY-MM
     * @param  User|null  $approver
     * @return int Number of updated logs
     */
    public function execute(int $employeeId, string $yearMonth, ?User $approver = null): int
    {
        return DB::transaction(function () use ($employeeId, $yearMonth, $approver) {
            $parts = explode('-', $yearMonth);
            $year = (int) ($parts[0] ?? now()->year);
            $month = (int) ($parts[1] ?? now()->month);
            $userId = $approver ? $approver->id : auth()->id();

            $query = SalesCommissionLog::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->where('status', 'pending');

            $count = $query->count();

            $query->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'updated_at' => now(),
            ]);

            return $count;
        });
    }
}
