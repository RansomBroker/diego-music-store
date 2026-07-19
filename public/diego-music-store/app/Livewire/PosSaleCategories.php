<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SaleCategory;
use App\Models\Branch;
use App\Actions\SaleCategory\CreateSaleCategory;
use App\Actions\SaleCategory\UpdateSaleCategory;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosSaleCategories extends Component
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
        $this->reset(['name', 'editingId', 'isEditing']);
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $category = SaleCategory::findOrFail($id);

        $this->editingId = $id;
        $this->isEditing = true;
        $this->name      = $category->name;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreateSaleCategory $createAction, UpdateSaleCategory $updateAction): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:sale_categories,name,' . ($this->editingId ?? 'NULL'),
        ];

        $this->validate($rules, [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah terdaftar.',
        ]);

        $data = [
            'name' => $this->name,
        ];

        if ($this->isEditing) {
            $category = SaleCategory::findOrFail($this->editingId);
            $updateAction->execute($category, $data);
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
        $category = SaleCategory::findOrFail($this->deletingId);

        // Check if this category name is currently used by any sales
        $existsInSales = \App\Models\Sale::where('sale_category', $category->name)->exists();
        if ($existsInSales) {
            Notification::make()->title('Gagal Hapus')->body('Kategori penjualan ini telah digunakan dalam transaksi penjualan.')->danger()->send();
            $this->showDeleteModal = false;
            return;
        }

        $category->delete();

        Notification::make()->title('Kategori Penjualan Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $categories = SaleCategory::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-sale-categories', [
            'categories'      => $categories,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Kategori Penjualan — POS']);
    }
}
