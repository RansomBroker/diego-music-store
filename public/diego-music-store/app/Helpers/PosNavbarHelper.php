<?php

namespace App\Helpers;

use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PosNavbarHelper
{
    /**
     * Get all prepared context variables required by the POS navbar component.
     *
     * @param array|null $activeSessionInfo
     * @param string|null $backUrl
     * @return array
     */
    public static function getContext(?array $activeSessionInfo = null, ?string $backUrl = null): array
    {
        $resolvedBackUrl = $backUrl ?? route('pos.front-office');
        $sessionInfo = static::resolveActiveSession($activeSessionInfo);
        $branchContext = static::resolveBranchContext();

        $isOwner = Auth::check() && Auth::user()->hasRole(['owner', 'Owner']);
        $attendanceContext = static::resolveAttendanceContext($branchContext['currentBranchModel'], $isOwner);

        return array_merge([
            'resolvedBackUrl'   => $resolvedBackUrl,
            'activeSessionInfo' => $sessionInfo,
            'isOwner'           => $isOwner,
        ], $branchContext, $attendanceContext);
    }

    /**
     * Resolve and normalize active cashier session info.
     *
     * @param array|null $activeSessionInfo
     * @return array|null
     */
    public static function resolveActiveSession(?array $activeSessionInfo = null): ?array
    {
        if (empty($activeSessionInfo) && Auth::check()) {
            $activeBranchId = BranchHelper::getActiveBranchId();
            $activeSession = CashSession::with('user')
                ->where('branch_id', $activeBranchId)
                ->where('status', 'open')
                ->first();

            if ($activeSession) {
                $activeSessionInfo = [
                    'id'           => $activeSession->id,
                    'opened_at'    => $activeSession->opened_at ? $activeSession->opened_at->format('d M Y H:i') : now()->format('d M Y H:i'),
                    'opening_cash' => $activeSession->opening_cash,
                    'opened_by'    => $activeSession->user?->name ?? (Auth::user()?->name ?? 'Kasir'),
                ];
            }
        }

        if (!empty($activeSessionInfo) && is_array($activeSessionInfo)) {
            $activeSessionInfo['id']           = $activeSessionInfo['id'] ?? '-';
            $activeSessionInfo['opened_by']    = $activeSessionInfo['opened_by'] ?? (Auth::user()?->name ?? 'Kasir');
            $activeSessionInfo['opening_cash'] = $activeSessionInfo['opening_cash'] ?? 0;
            $activeSessionInfo['opened_at']    = $activeSessionInfo['opened_at'] ?? now()->format('d M Y H:i');
        }

        return $activeSessionInfo;
    }

    /**
     * Resolve active branch ID, current Branch model, and the list of branches accessible to the user.
     *
     * @return array
     */
    public static function resolveBranchContext(): array
    {
        $currentActiveBranchId = session('pos_active_branch_id') ?: Auth::user()?->branches()->first()?->id;
        $currentBranchModel = $currentActiveBranchId ? Branch::find($currentActiveBranchId) : Branch::first();

        $userBranchList = Auth::check()
            ? (Auth::user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin'])
                ? Branch::where('is_active', true)->get()
                : Auth::user()->branches()->where('is_active', true)->get())
            : collect();

        return [
            'currentActiveBranchId' => $currentActiveBranchId,
            'currentBranchModel'    => $currentBranchModel,
            'userBranchList'        => $userBranchList,
        ];
    }

    /**
     * Resolve employee attendance info, quota, smart clock state, and branch employee attendance.
     *
     * @param Branch|null $currentBranchModel
     * @param bool $isOwner
     * @return array
     */
    public static function resolveAttendanceContext(?Branch $currentBranchModel, bool $isOwner): array
    {
        $currentEmployee = Auth::check() ? Auth::user()->employee : null;
        $todayDate = now()->format('Y-m-d');

        $todayAttendance = null;
        $usedOffDays = 0;
        $quotaOffDays = 4;
        $isOverQuota = false;
        $overCount = 0;
        $todayStatusText = 'Belum Presensi';

        if ($currentEmployee) {
            $usedOffDays = $currentEmployee->used_off_days_this_month;
            $quotaOffDays = $currentEmployee->monthly_off_days_quota;
            $isOverQuota = $currentEmployee->is_off_days_over_quota;
            $overCount = $currentEmployee->off_days_over_count;

            $todayAttendance = EmployeeAttendance::where('employee_id', $currentEmployee->id)
                ->where('date', $todayDate)
                ->first();

            if ($todayAttendance) {
                if ($todayAttendance->status === 'hadir') {
                    $clockInFormatted = $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i') : '-';
                    $todayStatusText = "Hadir ({$clockInFormatted})";
                } else {
                    $todayStatusText = ucfirst(str_replace('_', ' ', $todayAttendance->status));
                }
            }
        }

        // Smart Navbar Attendance State Detection
        $clockState = 'not_clocked_in';
        $clockInTimeText = null;
        $clockOutTimeText = null;

        if ($todayAttendance) {
            if ($todayAttendance->clock_in && !$todayAttendance->clock_out) {
                $clockState = 'clocked_in';
                $clockInTimeText = $todayAttendance->clock_in->format('H:i');
            } elseif ($todayAttendance->clock_in && $todayAttendance->clock_out) {
                $clockState = 'clocked_out';
                $clockInTimeText = $todayAttendance->clock_in->format('H:i');
                $clockOutTimeText = $todayAttendance->clock_out->format('H:i');
            } elseif (in_array($todayAttendance->status, ['off_day', 'izin', 'sakit', 'alpha'])) {
                $clockState = 'clocked_out';
            }
        }

        $allBranchEmployees = collect();
        if ($isOwner && $currentBranchModel) {
            $allBranchEmployees = Employee::with(['attendances' => function ($q) use ($todayDate) {
                $q->where('date', $todayDate);
            }, 'user'])
            ->where('is_active', true)
            ->where(function ($q) use ($currentBranchModel) {
                $q->where('branch_id', $currentBranchModel->id)
                  ->orWhereNull('branch_id');
            })
            ->get();
        }

        return [
            'currentEmployee'    => $currentEmployee,
            'todayAttendance'    => $todayAttendance,
            'usedOffDays'        => $usedOffDays,
            'quotaOffDays'       => $quotaOffDays,
            'isOverQuota'        => $isOverQuota,
            'overCount'          => $overCount,
            'todayStatusText'    => $todayStatusText,
            'clockState'         => $clockState,
            'clockInTimeText'    => $clockInTimeText,
            'clockOutTimeText'   => $clockOutTimeText,
            'allBranchEmployees' => $allBranchEmployees,
        ];
    }
}
