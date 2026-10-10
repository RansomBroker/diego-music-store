<?php

namespace App\Livewire;

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
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Profil Karyawan Tidak Tersedia',
                'body' => 'Akun ini tidak terhubung dengan profil karyawan aktif.',
            ]);
            return;
        }

        $alreadyRegistered = EmployeeDayOff::query()
            ->where('employee_id', $employee->id)
            ->whereDate('off_date', $date)
            ->where('status', 'active')
            ->exists();

        if ($alreadyRegistered) {
            $this->addError('selectedDate', 'Anda sudah terdaftar off pada tanggal ini.');
            return;
        }

        EmployeeDayOff::create([
            'employee_id' => $employee->id,
            'off_date' => $date,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

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

        $dayOffs = EmployeeDayOff::query()
            ->with(['employee', 'canceller'])
            ->whereBetween('off_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->orderBy('off_date')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (EmployeeDayOff $dayOff) => $dayOff->off_date->toDateString());

        $weeks = [];
        $week = [];

        for ($date = $calendarStart; $date->lte($calendarEnd); $date = $date->addDay()) {
            $dateString = $date->toDateString();
            $week[] = [
                'date' => $dateString,
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $monthStart->month,
                'isToday' => $dateString === now()->toDateString(),
                'isSelectable' => $this->isSelectableDate($dateString),
                'activeDayOffs' => ($dayOffs->get($dateString, collect()))->where('status', 'active')->values(),
                'cancelledCount' => $this->isOwner()
                    ? ($dayOffs->get($dateString, collect()))->where('status', 'cancelled')->count()
                    : 0,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        $selectedDayOffs = collect();
        if ($this->selectedDate) {
            $selectedDayOffs = EmployeeDayOff::query()
                ->with(['employee', 'canceller'])
                ->whereDate('off_date', $this->selectedDate)
                ->when(!$this->isOwner(), fn ($query) => $query->where('status', 'active'))
                ->orderBy('id')
                ->get();
        }

        $monthLabel = $monthStart->locale('id')->translatedFormat('F Y');

        return view('livewire.employee-day-off-calendar', [
            'weeks' => $weeks,
            'monthLabel' => $monthLabel,
            'isCurrentMonth' => $monthStart->format('Y-m') === $minimumMonth->format('Y-m'),
            'isNextMonth' => $monthStart->format('Y-m') === $maximumMonth->format('Y-m'),
            'selectedDayOffs' => $selectedDayOffs,
            'isOwner' => $this->isOwner(),
            'currentEmployee' => auth()->user()?->employee,
        ])->layout('layouts.pos', ['title' => 'Kalender Jadwal Off — POS Diego Music Store']);
    }
}
