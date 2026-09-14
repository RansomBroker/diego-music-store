<?php

namespace App\Livewire;

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
        $activeSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        $activeSessionInfo = $activeSession ? [
            'id'           => $activeSession->id,
            'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
        ] : null;

        // Ambil logo cabang aktif untuk sidebar
        $activeBranchId = session('pos_active_branch_id') ?: Auth::user()->branches()->first()?->id;
        $branchId       = $activeSession?->branch_id ?? $activeBranchId;
        $branch         = $branchId ? Branch::find($branchId) : Branch::first();
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;

        // Load presensi cabang hari ini
        $todayDate = now()->format('Y-m-d');
        $branchEmployees = Employee::with(['user', 'attendances' => function ($q) use ($todayDate) {
            $q->where('date', $todayDate);
        }])
        ->where('is_active', true)
        ->when($branch, function ($q) use ($branch) {
            $q->where(function ($sub) use ($branch) {
                $sub->where('branch_id', $branch->id)
                    ->orWhereNull('branch_id');
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
        $currentUserEmployee = Auth::user()?->employee;
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
