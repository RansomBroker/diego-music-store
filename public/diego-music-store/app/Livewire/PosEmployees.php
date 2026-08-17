<?php

namespace App\Livewire;

use App\Actions\Employee\CreateEmployee;
use App\Actions\Employee\UpdateEmployee;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class PosEmployees extends Component
{
    use WithPagination;

    // ── State Tabel & Pencarian ──────────────────────────────────────────
    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    // ── State Modal ──────────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $isEditing  = false;
    public ?int $editingId  = null;

    // ── State Form ───────────────────────────────────────────────────────
    public string $nik = '';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public ?string $join_date = null;
    public int $monthly_off_days_quota = 4;
    public float $basic_salary = 0;
    public ?int $branch_id = null;
    public ?int $user_id = null;
    public bool $is_active = true;

    // ── State Konfirmasi Hapus ───────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId      = null;

    // ── Lifecycle ────────────────────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // ── Sorting ──────────────────────────────────────────────────────────
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ── Modal: Buka Form Tambah ──────────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset([
            'nik', 'name', 'phone', 'email', 'address', 'join_date',
            'branch_id', 'user_id', 'editingId', 'isEditing'
        ]);
        $this->monthly_off_days_quota = 4;
        $this->basic_salary = 0;
        $this->is_active = true;
        $this->join_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $employee = Employee::findOrFail($id);

        $this->editingId              = $id;
        $this->isEditing              = true;
        $this->nik                    = $employee->nik ?? '';
        $this->name                   = $employee->name ?? '';
        $this->phone                  = $employee->phone ?? '';
        $this->email                  = $employee->email ?? '';
        $this->address                = $employee->address ?? '';
        $this->join_date              = $employee->join_date?->format('Y-m-d');
        $this->monthly_off_days_quota = $employee->monthly_off_days_quota ?? 4;
        $this->basic_salary           = (float) ($employee->basic_salary ?? 0);
        $this->branch_id              = $employee->branch_id;
        $this->user_id                = $employee->user_id;
        $this->is_active              = (bool) $employee->is_active;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreateEmployee $createEmployee, UpdateEmployee $updateEmployee): void
    {
        $rules = [
            'name'                   => 'required|string|max:255',
            'nik'                    => 'nullable|string|max:50|unique:employees,nik,' . ($this->editingId ?? 'NULL'),
            'phone'                  => 'nullable|string|max:50',
            'email'                  => 'nullable|email|max:255',
            'monthly_off_days_quota' => 'required|integer|min:0',
            'basic_salary'           => 'required|numeric|min:0',
            'branch_id'              => 'nullable|exists:branches,id',
            'user_id'                => 'nullable|exists:users,id',
        ];

        $validated = $this->validate($rules);
        $validated['address']   = $this->address;
        $validated['join_date'] = $this->join_date ?: null;
        $validated['is_active'] = $this->is_active;

        if ($this->isEditing && $this->editingId) {
            $employee = Employee::findOrFail($this->editingId);
            $updateEmployee->execute($employee, $validated);

            Notification::make()
                ->title('Karyawan Diperbarui')
                ->body("Data karyawan \"{$this->name}\" berhasil diperbarui.")
                ->success()
                ->send();
        } else {
            $createEmployee->execute($validated);

            Notification::make()
                ->title('Karyawan Dibuat')
                ->body("Karyawan baru \"{$this->name}\" berhasil ditambahkan.")
                ->success()
                ->send();
        }

        $this->showModal = false;
    }

    // ── Hapus ────────────────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $employee = Employee::findOrFail($this->deletingId);
            $name     = $employee->name;
            $employee->delete();

            Notification::make()
                ->title('Karyawan Dihapus')
                ->body("Data karyawan \"{$name}\" berhasil dihapus.")
                ->success()
                ->send();
        }

        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $query = Employee::with(['user.roles', 'branch']);

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('nik', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        $employees = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $branches = Branch::where('is_active', true)->get();
        $users    = User::where('is_active', true)->get();
        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-employees', [
            'employees'       => $employees,
            'branches'        => $branches,
            'users'           => $users,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Data Karyawan — POS Diego Music Store']);
    }
}
