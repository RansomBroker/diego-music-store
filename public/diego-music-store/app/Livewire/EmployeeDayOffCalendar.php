<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\EmployeeDayOff;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EmployeeDayOffCalendar extends Component
{
    public string $month = '';
    public ?string $selectedDate = null;
    public bool $showAddModal = false;
    public bool $showCancelModal = false;
    public ?int $cancellingDayOffId = null;
    public string $cancellationReason = '';

    public function mount(): void
    {
        $this->month = now()->startOfMonth()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $currentMonth = now()->startOfMonth()->format('Y-m');

        if ($this->month > $currentMonth) {
            $this->month = CarbonImmutable::createFromFormat('!Y-m', $this->month)
                ->subMonth()
                ->format('Y-m');
        }

        $this->resetSelection();
    }

    public function nextMonth(): void
    {
        $currentMonth = now()->startOfMonth()->format('Y-m');
        $nextMonth = now()->startOfMonth()->addMonth()->format('Y-m');

        if ($this->month < $nextMonth) {
            $this->month = CarbonImmutable::createFromFormat('!Y-m', $this->month)
                ->addMonth()
                ->format('Y-m');
        }

        $this->resetSelection();
    }

    public function goToCurrentMonth(): void
    {
        $this->month = now()->startOfMonth()->format('Y-m');
        $this->resetSelection();
    }

    public function openDate(string $date): void
    {
        if (!$this->isSelectableDate($date)) {
            return;
        }

        $this->selectedDate = $date;
        $this->showAddModal = true;
        $this->showCancelModal = false;
        $this->resetValidation();
    }

    public function registerMyself(): void
    {
        $date = $this->selectedDate;

        if (!$date || !$this->isSelectableDate($date)) {
            $this->addError('selectedDate', 'Tanggal off harus hari ini atau berada dalam rentang bulan yang diizinkan.');
            return;
        }

        $user = auth()->user();
        $employee = $user?->employee;

        if (!$employee || !$employee->is_active) {
            $this->addError('selectedDate', 'Akun ini tidak terhubung dengan profil karyawan aktif.');
            return;
        }

        if (!$employee->branch_id) {
            $this->addError('selectedDate', 'Profil karyawan belum ditetapkan ke cabang.');
            return;
        }

        $error = DB::transaction(function () use ($employee, $user, $date) {
            $branch = Branch::query()
                ->whereKey($employee->branch_id)
                ->lockForUpdate()
                ->first();

            if (!$branch || !$branch->is_active) {
                return 'Cabang karyawan tidak tersedia atau sedang nonaktif.';
            }

            $alreadyRegistered = EmployeeDayOff::query()
                ->where('employee_id', $employee->id)
                ->whereDate('off_date', $date)
                ->where('status', 'active')
                ->exists();

            if ($alreadyRegistered) {
                return 'Anda sudah terdaftar off pada tanggal ini.';
            }

            $monthStart = CarbonImmutable::parse($date)->startOfMonth()->toDateString();
            $monthEnd = CarbonImmutable::parse($date)->endOfMonth()->toDateString();
            $monthlyQuota = max(0, (int) ($branch->monthly_off_days_quota ?? 4));
            $monthlyUsed = EmployeeDayOff::query()
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->whereBetween('off_date', [$monthStart, $monthEnd])
                ->count();

            if ($monthlyUsed >= $monthlyQuota) {
                return "Kuota off bulanan Anda sudah penuh ({$monthlyUsed}/{$monthlyQuota} hari).";
            }

            EmployeeDayOff::create([
                'employee_id' => $employee->id,
                'branch_id' => $branch->id,
                'off_date' => $date,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            return null;
        });

        if ($error) {
            $this->addError('selectedDate', $error);
            return;
        }

        $this->showAddModal = false;
        $this->resetValidation();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Jadwal Off Ditambahkan',
            'body' => 'Anda berhasil mendaftarkan jadwal off.',
        ]);
    }

    public function openCancelModal(int $dayOffId): void
    {
        abort_unless($this->isOwner(), 403);

        $dayOff = EmployeeDayOff::query()
            ->where('branch_id', $this->activeBranchId())
            ->where('status', 'active')
            ->findOrFail($dayOffId);

        $this->selectedDate = $dayOff->off_date->toDateString();
        $this->cancellingDayOffId = $dayOff->id;
        $this->cancellationReason = '';
        $this->showAddModal = false;
        $this->showCancelModal = true;
        $this->resetValidation();
    }

    public function cancelSelectedDayOff(): void
    {
        abort_unless($this->isOwner(), 403);

        $validated = $this->validate([
            'cancellationReason' => ['nullable', 'string', 'max:500'],
        ]);

        $dayOff = EmployeeDayOff::query()
            ->where('branch_id', $this->activeBranchId())
            ->where('status', 'active')
            ->findOrFail($this->cancellingDayOffId);

        DB::transaction(function () use ($dayOff, $validated) {
            $dayOff->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellationReason'] ?: null,
            ]);
        });

        $this->showCancelModal = false;
        $this->cancellingDayOffId = null;
        $this->cancellationReason = '';

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Jadwal Off Dibatalkan',
            'body' => 'Jadwal off karyawan berhasil dibatalkan.',
        ]);
    }

    public function closeModals(): void
    {
        $this->showAddModal = false;
        $this->showCancelModal = false;
        $this->cancellingDayOffId = null;
        $this->resetValidation();
    }

    private function isOwner(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['owner', 'Owner']);
    }

    private function activeBranchId(): ?int
    {
        $user = auth()->user();
        $employee = $user?->employee;

        // Staff always use their assigned branch, not a branch selected in the UI session.
        if ($employee && !$this->isOwner()) {
            return $employee->branch_id ? (int) $employee->branch_id : null;
        }

        $branchId = session('pos_active_branch_id') ?: $user?->branches()->first()?->id;

        if (!$branchId) {
            $branchId = Branch::query()->value('id');
        }

        return $branchId ? (int) $branchId : null;
    }

    private function isSelectableDate(string $date): bool
    {
        try {
            $candidate = CarbonImmutable::parse($date)->startOfDay();
        } catch (\Throwable) {
            return false;
        }

        $today = CarbonImmutable::today();
        $start = $today->startOfMonth();
        $end = $start->addMonths(2)->subDay();

        return $candidate->greaterThanOrEqualTo($today)
            && $candidate->greaterThanOrEqualTo($start)
            && $candidate->lessThanOrEqualTo($end)
            && $candidate->format('Y-m-d') === $date;
    }

    private function resetSelection(): void
    {
        $this->selectedDate = null;
        $this->showAddModal = false;
        $this->showCancelModal = false;
        $this->cancellingDayOffId = null;
        $this->cancellationReason = '';
        $this->resetValidation();
    }

    public function render()
    {
        $currentMonth = now()->startOfMonth();
        $allowedMonth = CarbonImmutable::createFromFormat('!Y-m', $this->month ?: $currentMonth->format('Y-m'));
        $minimumMonth = CarbonImmutable::parse($currentMonth->format('Y-m-01'));
        $maximumMonth = $minimumMonth->addMonth();

        if ($allowedMonth->lt($minimumMonth) || $allowedMonth->gt($maximumMonth)) {
            $this->month = $minimumMonth->format('Y-m');
            $allowedMonth = $minimumMonth;
        }

        $monthStart = $allowedMonth->startOfMonth();
        $monthEnd = $allowedMonth->endOfMonth();
        $calendarStart = $monthStart->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->endOfWeek(Carbon::SUNDAY);
        $branchId = $this->activeBranchId();
        $branch = $branchId ? Branch::find($branchId) : null;

        $dayOffs = EmployeeDayOff::query()
            ->with(['employee', 'canceller'])
            ->where('branch_id', $branchId ?? 0)
            ->whereBetween('off_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->orderBy('off_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (EmployeeDayOff $dayOff) => $dayOff->off_date->toDateString());

        $weeks = [];
        $week = [];

        for ($date = $calendarStart; $date->lte($calendarEnd); $date = $date->addDay()) {
            $dateString = $date->toDateString();
            $activeDayOffs = ($dayOffs->get($dateString, collect()))->where('status', 'active')->values();
            $cancelledCount = ($dayOffs->get($dateString, collect()))->where('status', 'cancelled')->count();

            $week[] = [
                'date' => $dateString,
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $monthStart->month,
                'isToday' => $dateString === now()->toDateString(),
                'isSelectable' => $this->isSelectableDate($dateString),
                'activeDayOffs' => $activeDayOffs,
                'cancelledCount' => $cancelledCount,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        $selectedDayOffs = collect();
        if ($this->selectedDate && $branchId) {
            $selectedDayOffs = EmployeeDayOff::query()
                ->with(['employee', 'canceller'])
                ->where('branch_id', $branchId)
                ->whereDate('off_date', $this->selectedDate)
                ->when(!$this->isOwner(), fn ($query) => $query->where('status', 'active'))
                ->orderBy('id')
                ->get();
        }

        $currentEmployee = auth()->user()?->employee;
        $personalMonthlyUsed = 0;
        $personalMonthlyQuota = (int) ($branch?->monthly_off_days_quota ?? 4);
        $selectedDayQuotaUsed = $selectedDayOffs->where('status', 'active')->count();

        if ($currentEmployee && $this->selectedDate) {
            $selectedMonth = CarbonImmutable::parse($this->selectedDate);
            $personalMonthlyUsed = EmployeeDayOff::query()
                ->where('employee_id', $currentEmployee->id)
                ->where('status', 'active')
                ->whereBetween('off_date', [$selectedMonth->startOfMonth()->toDateString(), $selectedMonth->endOfMonth()->toDateString()])
                ->count();
        }

        $monthLabel = $monthStart->locale('id')->translatedFormat('F Y');

        return view('livewire.employee-day-off-calendar', [
            'weeks' => $weeks,
            'monthLabel' => $monthLabel,
            'isCurrentMonth' => $monthStart->format('Y-m') === $minimumMonth->format('Y-m'),
            'isNextMonth' => $monthStart->format('Y-m') === $maximumMonth->format('Y-m'),
            'selectedDayOffs' => $selectedDayOffs,
            'isOwner' => $this->isOwner(),
            'currentEmployee' => $currentEmployee,
            'selectedBranch' => $branch,
            'monthlyOffQuota' => (int) ($branch?->monthly_off_days_quota ?? 4),
            'personalMonthlyUsed' => $personalMonthlyUsed,
            'personalMonthlyQuota' => $personalMonthlyQuota,
        ])->layout('layouts.pos', ['title' => 'Kalender Jadwal Off — POS Diego Music Store']);
    }
}
