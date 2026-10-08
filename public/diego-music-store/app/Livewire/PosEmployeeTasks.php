<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Notifications\NewEmployeeTaskNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class PosEmployeeTasks extends Component
{
    use WithPagination;

    // ── Table State ──────────────────────────────────────────
    public string $search = '';
    public string $statusFilter = '';

    // ── Form State ──────────────────────────────────────────
    public bool $showModal = false;
    public bool $isEdit = false;
    public bool $showDeleteModal = false;

    public $taskId = null;
    public $deletingId = null;

    public string $title = '';
    public string $description = '';
    public array $employee_ids = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'employee_ids' => 'required|array|min:1',
        'employee_ids.*' => 'exists:employees,id',
    ];

    public function mount()
    {
        // Must be logged in
        if (!auth()->check()) {
            return redirect()->route('pos.login');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isEdit = false;
        $this->taskId = null;
        $this->title = '';
        $this->description = '';
        $this->employee_ids = [];
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function editTask($id)
    {
        $this->resetValidation();
        $task = \App\Models\EmployeeTask::find($id);
        if ($task) {
            $this->isEdit = true;
            $this->taskId = $task->id;
            $this->title = $task->title;
            $this->description = $task->description;
            $this->employee_ids = [$task->employee_id];
            $this->showModal = true;
        }
    }

    public function saveTask()
    {
        $this->validate();

        $owner = auth()->user();

        if ($this->isEdit && $this->taskId) {
            // Edit existing task
            $task = EmployeeTask::find($this->taskId);
            if ($task) {
                // If they changed the assigned employee
                $employeeId = count($this->employee_ids) > 0 ? $this->employee_ids[0] : $task->employee_id;
                
                $task->update([
                    'title' => $this->title,
                    'description' => $this->description,
                    'employee_id' => $employeeId,
                ]);

                FilamentNotification::make()
                    ->title('Berhasil')
                    ->body('Tugas berhasil diperbarui.')
                    ->success()
                    ->send();
            }
        } else {
            // Create new tasks
            foreach ($this->employee_ids as $employeeId) {
                $task = EmployeeTask::create([
                    'owner_id' => $owner->id,
                    'employee_id' => $employeeId,
                    'title' => $this->title,
                    'description' => $this->description,
                    'status' => 'pending',
                ]);

                // Dispatch notification to employee's user account
                if ($task->employee && $task->employee->user) {
                    Notification::send($task->employee->user, new NewEmployeeTaskNotification($task));
                }
            }

            FilamentNotification::make()
                ->title('Berhasil')
                ->body('Tugas berhasil dibuat dan dikirim ke karyawan.')
                ->success()
                ->send();
        }

        $this->closeModal();
    }

    public function approveTask($id)
    {
        $task = EmployeeTask::find($id);
        if ($task && $task->status === 'completed') {
            $task->update(['status' => 'approved']);
            
            FilamentNotification::make()
                ->title('Tugas Disetujui')
                ->success()
                ->send();
        }
    }

    public function rejectTask($id)
    {
        $task = EmployeeTask::find($id);
        if ($task && ($task->status === 'completed' || $task->status === 'pending')) {
            $task->update(['status' => 'rejected']);
            
            // Re-send notification so they fix it
            if ($task->employee && $task->employee->user) {
                Notification::send($task->employee->user, new NewEmployeeTaskNotification($task));
            }

            FilamentNotification::make()
                ->title('Tugas Ditolak')
                ->warning()
                ->send();
        }
    }

    public function markAsDone($id)
    {
        $task = EmployeeTask::find($id);
        if ($task && ($task->status === 'pending' || $task->status === 'rejected')) {
            $task->update(['status' => 'completed']);
            
            FilamentNotification::make()
                ->title('Berhasil')
                ->body('Tugas telah ditandai selesai (Menunggu Approve).')
                ->success()
                ->send();
        }
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function destroyTask()
    {
        if ($this->deletingId) {
            $task = EmployeeTask::find($this->deletingId);
            if ($task) {
                $task->delete();
                
                FilamentNotification::make()
                    ->title('Berhasil')
                    ->body('Tugas berhasil dihapus.')
                    ->success()
                    ->send();
            }
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $user = auth()->user();
        
        $query = EmployeeTask::query()->with(['employee', 'owner'])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('employee', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            });

        // If not admin/owner, maybe they only see their own tasks.
        // Assuming Owner can see all tasks they created. 
        // For simplicity, we just show tasks created by current user if they are owner.
        // Or if they are employee, they see tasks assigned to them.
        if ($user->hasRole(['owner', 'admin', 'super_admin'])) {
            $query->where('owner_id', $user->id);
        } else {
            // Employee view (if they access this page)
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $tasks = $query->latest()->paginate(15);
        $employees = Employee::where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['owner', 'Owner', 'super_admin', 'Super Admin']);
                });
            })
            ->get();

        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $branch = $activeBranchId ? \App\Models\Branch::find($activeBranchId) : \App\Models\Branch::first();
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-employee-tasks', [
            'tasks' => $tasks,
            'employees' => $employees,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Tugas Karyawan - POS']);
    }
}
