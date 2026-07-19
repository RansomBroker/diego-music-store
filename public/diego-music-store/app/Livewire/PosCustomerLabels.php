<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CustomerLabel;
use App\Models\Branch;
use App\Actions\CustomerLabel\CreateCustomerLabel;
use App\Actions\CustomerLabel\UpdateCustomerLabel;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosCustomerLabels extends Component
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
    public string $key = '';
    public string $name = '';

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
        $this->reset(['key', 'name', 'editingId', 'isEditing']);
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $label = CustomerLabel::findOrFail($id);

        $this->editingId = $id;
        $this->isEditing = true;
        $this->key       = $label->key;
        $this->name      = $label->name;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreateCustomerLabel $createAction, UpdateCustomerLabel $updateAction): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'key'  => $this->isEditing ? 'required|string|max:255' : 'required|string|max:255|unique:customer_labels,key',
        ];

        $this->validate($rules, [
            'name.required' => 'Nama label wajib diisi.',
            'key.required'  => 'Key / Code wajib diisi.',
            'key.unique'    => 'Key / Code sudah digunakan.',
        ]);

        $data = [
            'name' => $this->name,
        ];

        // Hanya sertakan key jika sedang create baru
        if (!$this->isEditing) {
            $data['key'] = $this->key;
        }

        if ($this->isEditing) {
            $label = CustomerLabel::findOrFail($this->editingId);
            $updateAction->execute($label, $data);
            Notification::make()->title('Kategori Penjualan Berhasil Diperbarui')->success()->send();
        } else {
            $createAction->execute($data);
            Notification::make()->title('Kategori Penjualan Berhasil Ditambahkan')->success()->send();
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
        $label = CustomerLabel::findOrFail($this->deletingId);

        // Check if this label is assigned to any customers
        if ($label->customers()->exists()) {
            Notification::make()->title('Gagal Hapus')->body('Kategori penjualan ini masih terhubung dengan data pelanggan.')->danger()->send();
            $this->showDeleteModal = false;
            return;
        }

        $label->delete();

        Notification::make()->title('Kategori Penjualan Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $labels = CustomerLabel::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('key', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-customer-labels', [
            'labels'          => $labels,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Kategori Penjualan — POS']);
    }
}
