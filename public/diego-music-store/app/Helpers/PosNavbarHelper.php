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
    public static function getContext(?array $activeSessionInfo = null, ?string $backUrl = null, $branches = null): array
    {
        $resolvedBackUrl = $backUrl ?? route('pos.front-office');
        $sessionInfo = static::resolveActiveSession($activeSessionInfo);
        $branchContext = static::resolveBranchContext($branches);

        $isOwner = PermissionHelper::isOwner(Auth::user());
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
            try {
                $activeBranchId = BranchHelper::getActiveBranchId();
            } catch (\Throwable $e) {
                $activeBranchId = session('pos_active_branch_id');
            }

            if ($activeBranchId) {
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
    public static function resolveBranchContext($preloadedBranches = null): array
    {
        $preloadedCollection = $preloadedBranches instanceof Collection
            ? $preloadedBranches
            : (is_iterable($preloadedBranches) ? collect($preloadedBranches) : collect());

        if (!Auth::check()) {
            $defaultBranch = $preloadedCollection->firstWhere('is_active', true);
            if (!$defaultBranch) {
                try {
                    $defaultBranch = Branch::where('is_active', true)->first();
                } catch (\Throwable $e) {
                    // Table might not exist in unit tests or during bootstrap
                }
            }

            return [
                'currentActiveBranchId' => null,
                'currentBranchModel'    => $defaultBranch,
                'userBranchList'        => collect(),
            ];
        }

        $user = Auth::user();
        $isManagement = false;
        try {
            $isManagement = BranchHelper::canAccessAllBranches($user);
        } catch (\Throwable $e) {
            $isManagement = false;
        }

        if ($isManagement && $preloadedCollection->isNotEmpty()) {
            $userBranchList = $preloadedCollection->where('is_active', true)->sortBy('name')->values();
            $sessionBranchId = session('pos_active_branch_id');
            $currentBranchModel = $sessionBranchId
                ? $userBranchList->firstWhere('id', (int) $sessionBranchId)
                : null;
            if (!$currentBranchModel) {
                $currentBranchModel = $userBranchList->first();
                if ($currentBranchModel) {
                    session(['pos_active_branch_id' => $currentBranchModel->id]);
                }
            }

            return [
                'currentActiveBranchId' => $currentBranchModel?->id,
                'currentBranchModel'    => $currentBranchModel,
                'userBranchList'        => $userBranchList,
            ];
        }

        try {
            $currentActiveBranchId = BranchHelper::getActiveBranchId();
        } catch (\Throwable $e) {
            $sessionBranchId = session('pos_active_branch_id');
            if ($isManagement) {
                try {
                    $currentActiveBranchId = $sessionBranchId ?: Branch::where('is_active', true)->value('id');
                } catch (\Throwable $e) {
                    $currentActiveBranchId = null;
                }
            } else {
                $userAssignedIds = [];
                try {
                    $userAssignedIds = $user->branches()->where('branches.is_active', true)->pluck('branches.id')->all();
                } catch (\Throwable $e) {
                    $userAssignedIds = [];
                }

                if (!empty($userAssignedIds) && in_array((int) $sessionBranchId, $userAssignedIds, true)) {
                    $currentActiveBranchId = (int) $sessionBranchId;
                } else {
                    $currentActiveBranchId = $userAssignedIds[0] ?? null;
                }
            }
        }

        $userBranchList = collect();
        try {
            $userBranchList = BranchHelper::getAllowedBranchesQuery()->get();
        } catch (\Throwable $e) {
            $userBranchList = collect();
        }

        $currentBranchModel = null;
        if ($currentActiveBranchId) {
            $currentBranchModel = $userBranchList->firstWhere('id', (int) $currentActiveBranchId);
            if (!$currentBranchModel) {
                try {
                    $currentBranchModel = Branch::find($currentActiveBranchId);
                } catch (\Throwable $e) {
                    $currentBranchModel = null;
                }
            }
        }

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
        // Jika user adalah Owner, kecualikan dari kewajiban presensi karyawan
        $currentEmployee = null;
        try {
            $currentEmployee = (Auth::check() && !$isOwner) ? Auth::user()->employee : null;
        } catch (\Throwable $e) {
            $currentEmployee = null;
        }

        $todayDate = now()->format('Y-m-d');

        $todayAttendance = null;
        $usedOffDays = 0;
        $quotaOffDays = 4;
        $isOverQuota = false;
        $overCount = 0;
        $todayStatusText = $isOwner ? 'Owner (Bebas Presensi)' : 'Belum Presensi';

        if ($currentEmployee) {
            try {
                // Hitung used_off_days_this_month sekali saja, hindari 3x query COUNT(*) terpisah
                $usedOffDays = $currentEmployee->used_off_days_this_month;
                $quotaOffDays = $currentEmployee->monthly_off_days_quota;
                $isOverQuota = $usedOffDays > $quotaOffDays;
                $overCount = max(0, $usedOffDays - $quotaOffDays);

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
            } catch (\Throwable $e) {
                // Table might not exist or error during lookup
            }
        }

        // Smart Navbar Attendance State Detection
        $clockState = $isOwner ? 'owner_exempt' : 'not_clocked_in';
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

        // Pertahankan key 'allBranchEmployees' untuk backward compatibility tanpa query berat yang tidak dipakai navbar
        $allBranchEmployees = collect();

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
