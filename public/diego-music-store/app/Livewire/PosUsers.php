<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosUsers extends Component
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
    public string $name     = '';
    public string $username = '';
    public string $email    = '';
    public string $password = '';
    public array  $selectedBranches = [];
    public array  $selectedRoles    = [];
    public bool   $is_active        = true;

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
        $this->reset(['name', 'username', 'email', 'password', 'selectedBranches', 'selectedRoles', 'editingId', 'isEditing']);
        $this->is_active = true;
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $user = User::with(['branches', 'roles'])->findOrFail($id);

        $this->editingId        = $id;
        $this->isEditing        = true;
        $this->name             = $user->name;
        $this->username         = $user->username ?? '';
        $this->email            = $user->email;
        $this->password         = ''; // Jangan load password hash
        $this->selectedBranches = $user->branches->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->selectedRoles    = $user->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->is_active        = (bool) $user->is_active;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreateUser $createUser, UpdateUser $updateUser): void
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . ($this->editingId ?? 'NULL'),
            'email'    => 'required|email|max:255|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $this->isEditing ? 'nullable|string|min:6' : 'required|string|min:6',
        ];

        $this->validate($rules, [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan oleh user lain.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 6 karakter.',
        ]);

        $data = [
            'name'      => $this->name,
            'username'  => $this->username,
            'email'     => $this->email,
            'is_active' => $this->is_active,
            'branches'  => array_map('intval', $this->selectedBranches),
            'roles'     => array_map('intval', $this->selectedRoles),
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingId);
            $updateUser->execute($user, $data);
            Notification::make()->title('User Berhasil Diperbarui')->success()->send();
        } else {
            $createUser->execute($data);
            Notification::make()->title('User Berhasil Ditambahkan')->success()->send();
        }

        $this->showModal = false;
        $this->resetPage();
    }

    // ── Konfirmasi Hapus ─────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        if (Auth::id() === $id) {
            Notification::make()->title('Gagal Hapus')->body('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.')->danger()->send();
            return;
        }
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        User::findOrFail($this->deletingId)->delete();

        Notification::make()->title('User Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $users = User::with(['branches', 'roles'])
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-users', [
            'users'           => $users,
            'branchesList'    => Branch::where('is_active', true)->orderBy('name')->get(),
            'rolesList'       => Role::orderBy('name')->get(),
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Data User — POS']);
    }
}
