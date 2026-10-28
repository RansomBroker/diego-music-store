<?php

namespace App\Actions\SalesQueue;

use App\Exports\SalesQueueExport;
use App\Helpers\SalesQueueHelper;
use App\Models\Branch;
use App\Models\SalesDutyLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportSalesQueueToExcel
{
    /**
     * Generate and download Excel (.xlsx) report for daily sales queue & duties.
     *
     * @param string $selectedDate
     * @param int $branchId
     * @param int|null $userId
     * @param string $status
     * @param string $search
     * @return BinaryFileResponse
     */
    public function execute(
        string $selectedDate,
        int $branchId,
        ?int $userId = null,
        string $status = 'all',
        string $search = ''
    ): BinaryFileResponse {
        $branch = Branch::find($branchId);

        // 1. Ambil KPI Summary Harian
        $summaryStats = SalesQueueHelper::getDailyStats($branchId, $selectedDate);

        // 2. Ambil Leaderboard / Rekapitulasi Performa Sales
        $dutyLogsForLeaderboard = SalesDutyLog::with(['user.employee'])
            ->where('branch_id', $branchId)
            ->whereDate('started_at', $selectedDate)
            ->get();

        $leaderboard = $dutyLogsForLeaderboard->groupBy('user_id')->map(function ($userLogs) {
            $first = $userLogs->first();
            $totalServings = $userLogs->count();
            $closings = $userLogs->filter(fn($l) => !empty($l->sale_id) || $l->closing_amount > 0)->count();
            $revenue = $userLogs->sum('closing_amount');
            $completed = $userLogs->where('status', 'completed');
            $avgDuration = $completed->count() > 0 ? (int) round($completed->avg('duration_seconds') / 60) : 0;
            $rate = $totalServings > 0 ? round(($closings / $totalServings) * 100, 1) : 0;

            return [
                'user_id' => $first->user_id,
                'name' => $first->user->name ?? 'Sales',
                'employee_nik' => $first->user->employee->nik ?? '-',
                'total_servings' => $totalServings,
                'total_closings' => $closings,
                'closing_rate' => $rate,
                'total_revenue' => $revenue,
                'avg_duration_minutes' => $avgDuration,
            ];
        })->sortByDesc('total_revenue')->values();

        // 3. Ambil Rincian Log Sesi Melayani (Sesuai Filter)
        $query = SalesDutyLog::with(['user.employee', 'sale', 'creator'])
            ->where('branch_id', $branchId)
            ->whereDate('started_at', $selectedDate);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (!empty($search)) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('user', function ($u) use ($term) {
                    $u->where('name', 'like', $term);
                })->orWhereHas('sale', function ($s) use ($term) {
                    $s->where('invoice_number', 'like', $term);
                });
            });
        }

        $logs = $query->orderBy('started_at', 'asc')->get();

        // 4. Buat Nama File Dinamis
        $branchSlug = Str::slug($branch?->name ?: 'pusat');
        $filename = "Laporan_Antrian_Sales_{$selectedDate}_{$branchSlug}.xlsx";

        return Excel::download(
            new SalesQueueExport(
                branch: $branch,
                date: $selectedDate,
                summaryStats: $summaryStats,
                leaderboard: $leaderboard,
                logs: $logs,
                exportedBy: Auth::user()
            ),
            $filename
        );
    }
}
