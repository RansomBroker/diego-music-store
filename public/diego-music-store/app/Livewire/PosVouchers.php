<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Voucher;
use App\Models\Branch;
use App\Actions\Voucher\CreateVoucher;
use App\Actions\Voucher\UpdateVoucher;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosVouchers extends Component
{
    use WithPagination;

    // ── Table State ──────────────────────────────────────────────────────
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // ── Modal State ──────────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // ── Form State ───────────────────────────────────────────────────────
    public string $code = '';
    public string $name = '';
    public string $type = 'fixed';
    public float $value = 0;
    public float $min_spend = 0;
    public ?string $valid_until = null;
    public ?int $max_uses = null;
    public bool $is_active = true;

    // ── Delete Confirmation State ────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function openCreate(): void
    {
        $this->reset(['code', 'name', 'type', 'value', 'min_spend', 'valid_until', 'max_uses', 'is_active', 'editingId', 'isEditing']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $voucher = Voucher::findOrFail($id);

        $this->editingId = $id;
        $this->isEditing = true;
        $this->code = $voucher->code;
        $this->name = $voucher->name;
        $this->type = $voucher->type;
        $this->value = floatval($voucher->value);
        $this->min_spend = floatval($voucher->min_spend);
        $this->valid_until = $voucher->valid_until ? $voucher->valid_until->format('Y-m-d\TH:i') : null;
        $this->max_uses = $voucher->max_uses;
        $this->is_active = (bool)$voucher->is_active;

        $this->showModal = true;
    }

    public function save(CreateVoucher $createAction, UpdateVoucher $updateAction): void
    {
        $rules = [
            'code' => 'required|string|max:100|unique:vouchers,code,' . ($this->editingId ?? 'NULL'),
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];

        $this->validate($rules, [
            'code.required' => 'Kode voucher wajib diisi.',
            'code.unique' => 'Kode voucher sudah terdaftar.',
            'name.required' => 'Nama voucher wajib diisi.',
            'value.required' => 'Nilai diskon voucher wajib diisi.',
        ]);

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'min_spend' => $this->min_spend,
            'valid_until' => $this->valid_until,
            'max_uses' => $this->max_uses,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $voucher = Voucher::findOrFail($this->editingId);
            $updateAction->execute($voucher, $data);
            Notification::make()->title('Voucher Berhasil Diperbarui')->success()->send();
        } else {
            $createAction->execute($data);
            Notification::make()->title('Voucher Berhasil Ditambahkan')->success()->send();
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function destroy(): void
    {
        $voucher = Voucher::findOrFail($this->deletingId);
        $voucher->delete();

        Notification::make()->title('Voucher Berhasil Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->resetPage();
    }

    public function render()
    {
        $vouchers = Voucher::when($this->search, function ($q) {
            $q->where('code', 'like', "%{$this->search}%")
              ->orWhere('name', 'like', "%{$this->search}%");
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);

        $userBranchId = Auth::user()?->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-vouchers', [
            'vouchers' => $vouchers,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Kelola Voucher — POS']);
    }
}
