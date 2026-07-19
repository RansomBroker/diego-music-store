<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Unit;
use App\Models\Branch;
use App\Actions\Unit\CreateUnit;
use App\Actions\Unit\UpdateUnit;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosUnits extends Component
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
    public ?string $base_unit_id = null;
    public int $conversion_factor = 1;
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

    public function updatedBaseUnitId($value): void
    {
        if (blank($value)) {
            $this->conversion_factor = 1;
        }
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
        $this->reset(['name', 'code', 'base_unit_id', 'conversion_factor', 'editingId', 'isEditing']);
        $this->is_active = true;
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $unit = Unit::findOrFail($id);

        $this->editingId         = $id;
        $this->isEditing         = true;
        $this->name              = $unit->name;
        $this->code              = $unit->code;
        $this->base_unit_id      = $unit->base_unit_id ? (string) $unit->base_unit_id : null;
        $this->conversion_factor = (int) $unit->conversion_factor;
        $this->is_active         = (bool) $unit->is_active;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(CreateUnit $createUnit, UpdateUnit $updateUnit): void
    {
        $rules = [
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:255|unique:units,code,' . ($this->editingId ?? 'NULL'),
            'base_unit_id'      => 'nullable|exists:units,id',
            'conversion_factor' => 'required|integer|min:1',
            'is_active'         => 'boolean',
        ];

        $this->validate($rules, [
            'name.required'              => 'Nama satuan wajib diisi.',
            'code.required'              => 'Kode satuan wajib diisi.',
            'code.unique'                => 'Kode satuan sudah terdaftar.',
            'conversion_factor.required' => 'Faktor konversi wajib diisi.',
            'conversion_factor.min'      => 'Faktor konversi minimal bernilai 1.',
        ]);

        $data = [
            'name'              => $this->name,
            'code'              => $this->code,
            'base_unit_id'      => blank($this->base_unit_id) ? null : (int) $this->base_unit_id,
            'conversion_factor' => blank($this->base_unit_id) ? 1 : $this->conversion_factor,
            'is_active'         => $this->is_active,
        ];

        if ($this->isEditing) {
            $unit = Unit::findOrFail($this->editingId);
            $updateUnit->execute($unit, $data);
            Notification::make()->title('Satuan Berhasil Diperbarui')->success()->send();
        } else {
            $createUnit->execute($data);
            Notification::make()->title('Satuan Berhasil Ditambahkan')->success()->send();
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
        $unit = Unit::findOrFail($this->deletingId);

        // Check if this unit is referenced as a base unit for others
        if ($unit->conversionUnits()->exists()) {
            Notification::make()->title('Gagal Hapus')->body('Satuan ini digunakan sebagai referensi satuan dasar oleh satuan konversi lain.')->danger()->send();
            $this->showDeleteModal = false;
            return;
        }

        $unit->delete();

        Notification::make()->title('Satuan Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $units = Unit::with('baseUnit')
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Options for base units (only base units that are active and not this record)
        $baseUnitsQuery = Unit::whereNull('base_unit_id')->where('is_active', true);
        if ($this->editingId) {
            $baseUnitsQuery->where('id', '!=', $this->editingId);
        }
        $baseUnitsList = $baseUnitsQuery->orderBy('name')->get();

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-units', [
            'units'           => $units,
            'baseUnitsList'   => $baseUnitsList,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Satuan Barang — POS']);
    }
}
