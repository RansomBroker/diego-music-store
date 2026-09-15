<?php

namespace App\Livewire;

use App\Actions\Attendance\ClockIn;
use App\Actions\Attendance\ClockOut;
use App\Actions\Attendance\ProcessBackdateAttendanceApproval;
use App\Actions\Attendance\RecordAttendance;
use App\Actions\Attendance\SubmitBackdateAttendanceRequest;
use App\Models\AttendanceBackdateRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class PosAttendances extends Component
{
    use WithPagination, WithFileUploads;

    // ── Tab State ────────────────────────────────────────────────────────
    public string $activeTab = 'attendances'; // 'attendances' or 'backdate_requests'

    // ── Filter State ─────────────────────────────────────────────────────
    public string $search = '';
    public ?int $filterBranchId = null;
    public string $filterMonth = '';
    public int $perPage = 15;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    // ── Modal State (Catat Status Presensi / Off Day) ───────────────────
    public bool $showModal = false;
    public ?int $selectedEmployeeId = null;
    public string $attendanceDate = '';
    public string $status = 'hadir';
    public string $notes = '';

    // ── Modal State (Clock In / Clock Out dengan Live Webcam & GPS) ──────
    public bool $showClockModal = false;
    public string $clockType = 'in'; // 'in' or 'out'
    public ?string $webcamDataUrl = null; // Base64 image snapshot from webcam
    public $attendancePhoto = null;      // File upload fallback
    public ?float $userLatitude = null;
    public ?float $userLongitude = null;
    public ?string $errorMessage = null;

    // ── Backdate Request Modal State ─────────────────────────────────────
    public bool $showBackdateModal = false;
    public ?int $backdateEmployeeId = null;
    public string $backdateDate = '';
    public string $backdateClockIn = '09:00';
    public string $backdateClockOut = '17:00';
    public string $backdateReason = '';
    public $backdatePhoto = null;
    public string $adminNotes = '';

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->attendanceDate = now()->format('Y-m-d');
        $this->backdateDate = now()->subDay()->format('Y-m-d');
        $this->filterBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;

        if (request()->query('tab') === 'backdate_requests') {
            $this->activeTab = 'backdate_requests';
        }

        if (request()->query('action') === 'clock_in') {
            $this->openClockModal('in');
        } elseif (request()->query('action') === 'clock_out') {
            $this->openClockModal('out');
        } elseif (request()->query('action') === 'backdate') {
            $this->openBackdateModal();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openClockModal(string $type = 'in'): void
    {
        $this->clockType = $type;
        $this->webcamDataUrl = null;
        $this->attendancePhoto = null;
        $this->errorMessage = null;
        $this->showClockModal = true;
    }

    public function quickClockIn(ClockIn $actionClass): void
    {
        try {
            $employee = auth()->user()?->employee;
            if (!$employee) {
                throw new \Exception('Data karyawan tidak ditemukan.');
            }

            $photoPath = $this->storeWebcamPhoto($this->webcamDataUrl);

            $actionClass->execute(
                $employee,
                $this->filterBranchId ?: $employee->branch_id,
                $this->notes,
                $this->userLatitude,
                $this->userLongitude,
                $photoPath
            );

            $this->dispatch('toast', ['type' => 'success', 'title' => 'Clock In Berhasil', 'body' => 'Clock In berhasil dicatat.']);
            $this->showClockModal = false;
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', ['type' => 'danger', 'title' => 'Gagal Clock In', 'body' => $e->getMessage()]);
        }
    }

    public function quickClockOut(ClockOut $actionClass): void
    {
        try {
            $employee = auth()->user()?->employee;
            if (!$employee) {
                throw new \Exception('Data karyawan tidak ditemukan.');
            }

            $photoPath = $this->storeWebcamPhoto($this->webcamDataUrl);

            $actionClass->execute(
                $employee,
                $this->notes,
                $this->userLatitude,
                $this->userLongitude,
                $photoPath
            );

            $this->dispatch('toast', ['type' => 'success', 'title' => 'Clock Out Berhasil', 'body' => 'Clock Out berhasil dicatat.']);
            $this->showClockModal = false;
        } catch (Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', ['type' => 'danger', 'title' => 'Gagal Clock Out', 'body' => $e->getMessage()]);
        }
    }

    protected function storeWebcamPhoto(?string $dataUrl): ?string
    {
        if (!$dataUrl) {
            return null;
        }

        if (str_starts_with($dataUrl, 'data:image')) {
            $parts = explode(',', $dataUrl, 2);
            if (count($parts) === 2) {
                $imageData = base64_decode($parts[1]);
                if ($imageData !== false) {
                    $fileName = 'attendance-selfies/' . uniqid('selfie_') . '.jpg';
                    Storage::disk('public')->put($fileName, $imageData);
                    return $fileName;
                }
            }
        }

        if (strlen($dataUrl) > 255) {
            $imageData = base64_decode($dataUrl, true);
            if ($imageData !== false) {
                $fileName = 'attendance-selfies/' . uniqid('selfie_') . '.jpg';
                Storage::disk('public')->put($fileName, $imageData);
                return $fileName;
            }
            return substr($dataUrl, 0, 255);
        }

        return $dataUrl;
    }

    public function openRecordModal(): void
    {
        $this->selectedEmployeeId = auth()->user()?->employee?->id;
        $this->attendanceDate = now()->format('Y-m-d');
        $this->status = 'off_day';
        $this->notes = '';
        $this->showModal = true;
    }

    public function saveRecord(RecordAttendance $actionClass): void
    {
        $this->validate([
            'selectedEmployeeId' => 'required|exists:employees,id',
            'attendanceDate' => 'required|date',
            'status' => 'required|in:hadir,off_day,izin,sakit,alpha',
        ]);

        try {
            $employee = Employee::findOrFail($this->selectedEmployeeId);

            $actionClass->execute($employee, [
                'branch_id' => $this->filterBranchId ?: $employee->branch_id,
                'date' => $this->attendanceDate,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            $this->dispatch('toast', ['type' => 'success', 'title' => 'Presensi Disimpan', 'body' => 'Data presensi berhasil disimpan.']);
            Notification::make()
                ->title('Presensi Berhasil Disimpan')
                ->body("Data presensi {$employee->name} berhasil diperbarui.")
                ->success()
                ->send();

            $this->showModal = false;
            $this->reset(['selectedEmployeeId', 'notes']);
        } catch (Throwable $e) {
            $this->dispatch('toast', ['type' => 'danger', 'title' => 'Gagal Simpan Presensi', 'body' => $e->getMessage()]);
        }
    }

    public function openBackdateModal(): void
    {
        $employee = auth()->user()?->employee;
        $this->backdateEmployeeId = $employee?->id;
        $this->backdateDate = now()->subDay()->format('Y-m-d');
        $this->backdateClockIn = '09:00';
        $this->backdateClockOut = '17:00';
        $this->backdateReason = '';
        $this->backdatePhoto = null;
        $this->showBackdateModal = true;
    }

    public function submitBackdateRequest(SubmitBackdateAttendanceRequest $actionClass): void
    {
        try {
            $employee = auth()->user()?->employee ?: Employee::find($this->backdateEmployeeId);

            if (!$employee) {
                throw new \Exception('Data karyawan tidak ditemukan.');
            }

            $photoPath = null;
            if ($this->backdatePhoto) {
                $photoPath = $this->backdatePhoto->store('backdate-proofs', 'public');
            }

            $actionClass->execute($employee, [
                'branch_id'        => $this->filterBranchId ?: $employee->branch_id,
                'requested_date'   => $this->backdateDate,
                'clock_in'         => $this->backdateClockIn,
                'clock_out'        => $this->backdateClockOut,
                'reason'           => $this->backdateReason,
                'proof_photo_path' => $photoPath,
            ]);

            $this->dispatch('toast', ['type' => 'success', 'title' => 'Request Dikirim', 'body' => 'Pengajuan presensi susulan (backdate) berhasil dikirim dan menunggu persetujuan Owner.']);
            Notification::make()
                ->title('Pengajuan Backdate Dikirim')
                ->body('Pengajuan presensi susulan berhasil dikirim ke Owner untuk diverifikasi.')
                ->success()
                ->send();

            $this->showBackdateModal = false;
            $this->activeTab = 'backdate_requests';
        } catch (Throwable $e) {
            $this->dispatch('toast', ['type' => 'danger', 'title' => 'Gagal Kirim Pengajuan', 'body' => $e->getMessage()]);
        }
    }

    public function processBackdateApproval(int $requestId, string $approvalAction, ProcessBackdateAttendanceApproval $actionClass): void
    {
        try {
            $isOwner = auth()->check() && auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);
            if (!$isOwner) {
                throw new \Exception('Hanya Owner / Admin yang berhak menyetujui atau menolak pengajuan presensi susulan.');
            }

            $request = AttendanceBackdateRequest::findOrFail($requestId);
            $actionClass->execute($request, $approvalAction, auth()->user(), $this->adminNotes);

            $statusText = $approvalAction === 'approve' ? 'Disetujui' : 'Ditolak';
            $this->dispatch('toast', ['type' => 'success', 'title' => "Request {$statusText}", 'body' => "Pengajuan presensi susulan untuk {$request->employee->name} telah {$statusText}."]);
            Notification::make()
                ->title("Pengajuan Presensi {$statusText}")
                ->body("Pengajuan presensi susulan untuk {$request->employee->name} tanggal {$request->requested_date->format('d/m/Y')} telah {$statusText}.")
                ->success()
                ->send();

            $this->adminNotes = '';
            $this->activeTab = 'backdate_requests';
        } catch (Throwable $e) {
            $this->dispatch('toast', ['type' => 'danger', 'title' => 'Gagal Memproses Pengajuan', 'body' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $yearMonth = explode('-', $this->filterMonth ?: now()->format('Y-m'));
        $year = (int) ($yearMonth[0] ?? now()->year);
        $month = (int) ($yearMonth[1] ?? now()->month);

        $query = EmployeeAttendance::with(['employee', 'branch'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($this->filterBranchId) {
            $query->where('branch_id', $this->filterBranchId);
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('nik', 'like', $searchTerm);
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage > 0 ? $this->perPage : 1000);

        $employees = Employee::where('is_active', true)->get();
        $branches  = Branch::where('is_active', true)->get();

        $pendingBackdateRequests = AttendanceBackdateRequest::with(['employee', 'branch'])
            ->where('status', 'pending')
            ->orderBy('requested_date', 'desc')
            ->get();

        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        $isOwnerUser = auth()->check() && auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);

        return view('livewire.pos-attendances', [
            'attendances'             => $attendances,
            'employees'               => $employees,
            'branches'                => $branches,
            'selectedBranch'          => $branch,
            'selectedLogoUrl'         => $selectedLogoUrl,
            'pendingBackdateRequests' => $pendingBackdateRequests,
            'isOwnerUser'             => $isOwnerUser,
        ])->layout('layouts.pos', ['title' => 'Presensi & Absensi Karyawan — POS']);
    }
}
