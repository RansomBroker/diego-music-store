<?php

namespace App\Helpers;

use App\Models\SalesDutyLog;
use App\Models\SalesQueueMember;
use Illuminate\Database\Eloquent\Collection;

class SalesQueueHelper
{
    /**
     * Get active queue members for a branch and date.
     */
    public static function getActiveQueue(int $branchId, ?string $date = null): Collection
    {
        $targetDate = $date ?? now()->toDateString();
        return SalesQueueMember::with(['user.employee', 'currentDutyLog'])
            ->where('branch_id', $branchId)
            ->where('date', $targetDate)
            ->where('is_active', true)
            ->orderBy('queue_order', 'asc')
            ->get();
    }

    /**
     * Get the next sales rep in line (first waiting member).
     */
    public static function getNextWaitingSales(int $branchId, ?string $date = null): ?SalesQueueMember
    {
        $targetDate = $date ?? now()->toDateString();
        return SalesQueueMember::with(['user.employee'])
            ->where('branch_id', $branchId)
            ->where('date', $targetDate)
            ->where('status', 'waiting')
            ->where('is_active', true)
            ->orderBy('queue_order', 'asc')
            ->first();
    }

    /**
     * Get members currently on duty.
     */
    public static function getOnDutyMembers(int $branchId, ?string $date = null): Collection
    {
        $targetDate = $date ?? now()->toDateString();
        return SalesQueueMember::with(['user.employee', 'currentDutyLog'])
            ->where('branch_id', $branchId)
            ->where('date', $targetDate)
            ->where('status', 'on_duty')
            ->where('is_active', true)
            ->orderBy('duty_started_at', 'asc')
            ->get();
    }

    /**
     * Get members currently waiting.
     */
    public static function getWaitingMembers(int $branchId, ?string $date = null): Collection
    {
        $targetDate = $date ?? now()->toDateString();
        return SalesQueueMember::with(['user.employee'])
            ->where('branch_id', $branchId)
            ->where('date', $targetDate)
            ->where('status', 'waiting')
            ->where('is_active', true)
            ->orderBy('queue_order', 'asc')
            ->get();
    }

    /**
     * Get daily statistics for the sales queue report.
     */
    public static function getDailyStats(int $branchId, ?string $date = null): array
    {
        $targetDate = $date ?? now()->toDateString();

        $logs = SalesDutyLog::with(['user', 'sale'])
            ->where('branch_id', $branchId)
            ->whereDate('started_at', $targetDate)
            ->get();

        $totalServings = $logs->count();
        $closingLogs = $logs->filter(fn($l) => !empty($l->sale_id) || $l->closing_amount > 0);
        $totalClosings = $closingLogs->count();
        $conversionRate = $totalServings > 0 ? round(($totalClosings / $totalServings) * 100, 1) : 0;
        $totalRevenue = $closingLogs->sum('closing_amount');

        $completedLogs = $logs->where('status', 'completed');
        $avgDurationSeconds = $completedLogs->count() > 0 ? (int) round($completedLogs->avg('duration_seconds')) : 0;
        $avgDurationMinutes = (int) round($avgDurationSeconds / 60);

        return [
            'total_servings' => $totalServings,
            'total_closings' => $totalClosings,
            'conversion_rate' => $conversionRate,
            'total_revenue' => $totalRevenue,
            'avg_duration_minutes' => $avgDurationMinutes,
            'active_on_duty_count' => $logs->where('status', 'serving')->count(),
        ];
    }
}
