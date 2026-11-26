<?php

namespace App\Actions\SalesQueue;

use App\Models\SalesQueueMember;

class ReleaseExpiredIdleSales
{
    /**
     * Automatically complete & return sales reps who have stayed in 'on_duty' (Melayani)
     * for >= $idleMinutes (default 60 minutes / 1 hour) back to the end of the waiting queue.
     */
    public function execute(int $branchId, ?string $date = null, int $idleMinutes = 60): int
    {
        $targetDate = $date ?? now()->toDateString();
        $cutoff = now()->subMinutes($idleMinutes);

        $idleOnDutyMembers = SalesQueueMember::with(['user', 'currentDutyLog'])
            ->where('branch_id', $branchId)
            ->whereDate('date', $targetDate)
            ->where('status', 'on_duty')
            ->where('is_active', true)
            ->whereNotNull('duty_started_at')
            ->where('duty_started_at', '<=', $cutoff)
            ->orderBy('duty_started_at', 'asc')
            ->get();

        if ($idleOnDutyMembers->isEmpty()) {
            return 0;
        }

        $completer = app(CompleteSalesDuty::class);
        $released = 0;

        foreach ($idleOnDutyMembers as $member) {
            $completer->execute(
                member: $member,
                notes: 'Selesai otomatis (idle di Melayani >= 1 jam)'
            );
            $released++;
        }

        return $released;
    }
}
