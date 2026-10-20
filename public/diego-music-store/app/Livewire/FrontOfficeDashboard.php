<?php

namespace App\Livewire;

use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class FrontOfficeDashboard extends Component
{
    public function render()
    {
        $activeSession = CashSession::with('user')
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        $activeSessionInfo = $activeSession ? [
            'id'           => $activeSession->id,
            'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
            'opened_by'    => $activeSession->user?->name ?? (Auth::user()?->name ?? 'Kasir'),
        ] : null;

        // Resolve only an active branch assigned to this user (owners may switch branches).
        $activeBranchId = BranchHelper::getActiveBranchId();
        $branchId       = $activeSession?->branch_id ?? $activeBranchId;
        $branch         = Branch::findOrFail($branchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;

        $isOwner = Auth::check() && Auth::user()->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']);
        $currentUserEmployee = Auth::user()?->employee;

        // Load presensi cabang hari ini (khusus karyawan operasional, kecualikan Owner)
        $todayDate = now()->format('Y-m-d');
        $branchEmployees = Employee::with(['user', 'attendances' => function ($q) use ($todayDate) {
            $q->where('date', $todayDate);
        }])
        ->where('is_active', true)
        ->whereDoesntHave('user.roles', function ($q) {
            $q->whereIn('name', ['owner', 'Owner', 'super_admin', 'Super Admin']);
        })
        ->when(!$isOwner, function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->when($branch, function ($q) use ($branch, $isOwner) {
            $q->where(function ($sub) use ($branch) {
                $sub->where('branch_id', $branch->id);
                if ($isOwner) {
                    $sub->orWhereNull('branch_id');
                } else {
                    $sub->orWhere(function ($ownEmployee) {
                        $ownEmployee->where('user_id', Auth::id())->whereNull('branch_id');
                    });
                }
            });
        })
        ->orderBy('name', 'asc')
        ->get();

        $totalStaff = $branchEmployees->count();
        $hadirCount = 0;
        $izinCount  = 0;
        $belumCount = 0;

        foreach ($branchEmployees as $emp) {
            $att = $emp->attendances->first();
            if (!$att) {
                $belumCount++;
            } elseif ($att->status === 'hadir') {
                $hadirCount++;
            } else {
                $izinCount++;
            }
        }

        // Logged-in User Employee Data, Today Attendance, Commission & Progress Bar
        $currentUserTodayAttendance = null;
        $currentUserMonthlyCommission = 0.0;
        $currentUserMonthlySales = 0.0;
        $currentUserTargetSales = 25000000.0; // Default target 25 juta
        $currentUserProgressPercent = 0;

        if ($currentUserEmployee) {
            $currentUserTodayAttendance = EmployeeAttendance::where('employee_id', $currentUserEmployee->id)
                ->where('date', $todayDate)
                ->first();

            $currentUserMonthlyCommission = (float) \App\Models\SalesCommissionLog::where('employee_id', $currentUserEmployee->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('commission_amount');

            $currentUserMonthlySales = (float) \App\Models\SalesCommissionLog::where('employee_id', $currentUserEmployee->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('sale_amount');

            // Cari target omset dari skema komisi khusus karyawan atau skema aktif
            $userScheme = \App\Models\CommissionScheme::where('is_active', true)
                ->where(function ($q) use ($currentUserEmployee) {
                    $q->where('employee_id', $currentUserEmployee->id)
                        ->orWhereNull('employee_id');
                })
                ->where('min_monthly_sales_target', '>', 0)
                ->orderBy('employee_id', 'desc')
                ->first();

            if ($userScheme && $userScheme->min_monthly_sales_target > 0) {
                $currentUserTargetSales = (float) $userScheme->min_monthly_sales_target;
            }

            if ($currentUserTargetSales > 0) {
                $currentUserProgressPercent = min(100, (int) round(($currentUserMonthlySales / $currentUserTargetSales) * 100));
            }
        }

        return view('livewire.front-office-dashboard', [
            'isOwner'                       => $isOwner,
            'activeSessionInfo'             => $activeSessionInfo,
            'selectedLogoUrl'               => $selectedLogoUrl,
            'currentBranch'                 => $branch,
            'branchEmployees'               => $branchEmployees,
            'totalStaff'                    => $totalStaff,
            'hadirCount'                    => $hadirCount,
            'izinCount'                     => $izinCount,
            'belumCount'                    => $belumCount,
            'currentUserEmployee'           => $currentUserEmployee,
            'currentUserTodayAttendance'    => $currentUserTodayAttendance,
            'currentUserMonthlyCommission' => $currentUserMonthlyCommission,
            'currentUserMonthlySales'      => $currentUserMonthlySales,
            'currentUserTargetSales'       => $currentUserTargetSales,
            'currentUserProgressPercent'   => $currentUserProgressPercent,
        ])->layout('layouts.pos', ['title' => 'Dashboard — POS']);
    }
}
