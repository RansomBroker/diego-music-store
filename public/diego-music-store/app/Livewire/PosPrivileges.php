<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Actions\Privilege\CreateRole;
use App\Actions\Privilege\UpdateRolePermissions;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosPrivileges extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $roleName = '';
    public array $selectedPermissions = [];

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    /**
     * Definisi default permissions jika belum ada di database
     */
    public static array $defaultPermissionsGrouped = [
        'POS & Penjualan' => [
            'pos.access' => 'Akses Kasir POS',
            'pos.discount' => 'Beri Diskon Khusus',
            'pos.hold' => 'Simpan Transaksi Gantung',
            'pos.void' => 'Batalkan Transaksi (Void)',
        ],
        'Kas & Keuangan' => [
            'cash.session' => 'Kelola Sesi Kasir',
            'daily_cash.view' => 'Lihat Kas Harian',
            'daily_cash.manage' => 'Input Kas Masuk/Keluar',
            'supplier_payments.manage' => 'Pelunasan Hutang Supplier',
        ],
        'Data Master' => [
            'master.customers' => 'Kelola Data Pelanggan',
            'master.users' => 'Kelola Data User',
            'master.units' => 'Kelola Satuan Barang',
            'master.categories' => 'Kelola Kategori Penjualan',
            'master.payment_methods' => 'Kelola Metode Pembayaran',
        ],
        'Utility & Pengaturan' => [
            'utility.privileges' => 'Setting Hak Akses User',
            'utility.store' => 'Register & Profil Toko',
            'utility.receipt' => 'Setting Struk & Invoice',
            'utility.barcode' => 'Cetak Barcode Produk',
        ],
    ];

    public function mount(): void
    {
        $this->ensurePermissionsExist();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    private function ensurePermissionsExist(): void
    {
        foreach (self::$defaultPermissionsGrouped as $group => $perms) {
            foreach ($perms as $name => $label) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }
    }

    public function openCreate(): void
    {
        $this->reset(['roleName', 'selectedPermissions', 'editingId', 'isEditing']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editingId = $id;
        $this->isEditing = true;
        $this->roleName  = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function save(CreateRole $createRole, UpdateRolePermissions $updateRolePermissions): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . ($this->editingId ?? 'NULL'),
        ], [
            'roleName.required' => 'Nama role / hak akses wajib diisi.',
            'roleName.unique'   => 'Nama role ini sudah digunakan.',
        ]);

        if ($this->isEditing) {
            $role = Role::findOrFail($this->editingId);
            $updateRolePermissions->execute($role, [
                'name' => $this->roleName,
                'permissions' => $this->selectedPermissions,
            ]);
            Notification::make()->title('Role & Hak Akses Berhasil Diperbarui')->success()->send();
        } else {
            $createRole->execute([
                'name' => $this->roleName,
                'permissions' => $this->selectedPermissions,
            ]);
            Notification::make()->title('Role Baru Berhasil Dibuat')->success()->send();
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin' || $role->name === 'admin') {
            Notification::make()->title('Gagal Hapus')->body('Role utama tidak dapat dihapus.')->danger()->send();
            return;
        }
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        $role = Role::findOrFail($this->deletingId);
        $role->delete();

        Notification::make()->title('Role Berhasil Dihapus')->success()->send();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->resetPage();
    }

    public function render()
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        $userBranchId = Auth::user()->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-privileges', [
            'roles' => $roles,
            'permissionGroups' => self::$defaultPermissionsGrouped,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Setting Privilege User — POS']);
    }
}
