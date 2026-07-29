<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PaymentMethod;
use App\Models\Account;
use App\Models\Branch;
use App\Actions\PaymentMethod\CreatePaymentMethod;
use App\Actions\PaymentMethod\UpdatePaymentMethod;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosPaymentMethods extends Component
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
    public string $name = '';
    public string $code = '';
    public ?int $parent_id = null;
    public ?int $account_id = null;
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
        $this->reset(['name', 'code', 'parent_id', 'account_id', 'is_active', 'editingId', 'isEditing']);
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);

        $this->editingId  = $id;
        $this->isEditing  = true;
        $this->name       = $method->name;
        $this->code       = $method->code;
        $this->parent_id  = $method->parent_id;
        $this->account_id = $method->account_id;
        $this->is_active  = (bool) $method->is_active;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreatePaymentMethod $createAction, UpdatePaymentMethod $updateAction): void
    {
        if ($this->isEditing && $this->parent_id && $this->parent_id === $this->editingId) {
            $this->addError('parent_id', 'Metode pembayaran tidak bisa menjadi parent untuk dirinya sendiri.');
            return;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code,' . ($this->editingId ?? 'NULL'),
            'parent_id' => 'nullable|exists:payment_methods,id',
            'account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
        ];

        $this->validate($rules, [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'code.required' => 'Kode unik wajib diisi.',
            'code.unique'   => 'Kode unik sudah terdaftar.',
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'parent_id' => $this->parent_id,
            'account_id' => $this->account_id,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $method = PaymentMethod::findOrFail($this->editingId);
            $updateAction->execute($method, $data);
            Notification::make()->title('Metode Pembayaran Berhasil Diperbarui')->success()->send();
        } else {
            $createAction->execute($data);
            Notification::make()->title('Metode Pembayaran Berhasil Ditambahkan')->success()->send();
        }

        $this->showModal = false;
        $this->resetPage();
    }

    // ── Konfirmasi Hapus ─────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        $method = PaymentMethod::findOrFail($this->deletingId);

        // Check if this method name is currently used by any sales
        $existsInSales = \App\Models\Sale::where('payment_method', 'like', "%{$method->name}%")->exists();
        if ($existsInSales) {
            Notification::make()->title('Gagal Hapus')->body('Metode pembayaran ini telah digunakan dalam transaksi penjualan.')->danger()->send();
            $this->showDeleteModal = false;
            return;
        }

        // Check if has children
        if ($method->children()->exists()) {
            Notification::make()->title('Gagal Hapus')->body('Metode pembayaran ini memiliki sub-metode. Hapus atau pindahkan sub-metode terlebih dahulu.')->danger()->send();
            $this->showDeleteModal = false;
            return;
        }

        $method->delete();

        Notification::make()->title('Metode Pembayaran Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $methods = PaymentMethod::with(['account', 'parent'])
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $accounts = Account::orderBy('code')->get();

        $parentOptions = PaymentMethod::whereNull('parent_id')
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->orderBy('name')
            ->get();

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-payment-methods', [
            'methods'         => $methods,
            'accounts'        => $accounts,
            'parentOptions'   => $parentOptions,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Metode Pembayaran — POS']);
    }
}
