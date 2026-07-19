<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\PricingTier;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PosCustomers extends Component
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
    public string $name             = '';
    public string $phone            = '';
    public string $email            = '';
    public string $address          = '';
    public string $date_of_birth    = '';
    public ?int   $customer_label_id = null;
    public ?int   $pricing_tier_id   = null;
    public bool   $is_loyalty_member = false;
    public int    $loyalty_points    = 0;

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
        $this->reset(['name','phone','email','address','date_of_birth',
                       'customer_label_id','pricing_tier_id','is_loyalty_member','loyalty_points',
                       'editingId','isEditing']);
        $this->showModal = true;
    }

    // ── Modal: Buka Form Edit ────────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->editingId          = $id;
        $this->isEditing          = true;
        $this->name               = $customer->name;
        $this->phone              = $customer->phone ?? '';
        $this->email              = $customer->email ?? '';
        $this->address            = $customer->address ?? '';
        $this->date_of_birth      = $customer->date_of_birth?->format('Y-m-d') ?? '';
        $this->customer_label_id  = $customer->customer_label_id;
        $this->pricing_tier_id    = $customer->pricing_tier_id;
        $this->is_loyalty_member  = (bool) $customer->is_loyalty_member;
        $this->loyalty_points     = (int) $customer->loyalty_points;

        $this->showModal = true;
    }

    // ── Simpan (Create / Update) ─────────────────────────────────────────
    public function save(): void
    {
        $this->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20|unique:customers,phone,' . ($this->editingId ?? 'NULL'),
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string|max:500',
            'date_of_birth'   => 'nullable|date',
            'loyalty_points'  => 'integer|min:0',
        ], [
            'name.required'   => 'Nama pelanggan wajib diisi.',
            'phone.unique'    => 'Nomor telepon sudah digunakan pelanggan lain.',
            'email.email'     => 'Format email tidak valid.',
            'loyalty_points.min' => 'Poin tidak boleh negatif.',
        ]);

        $data = [
            'name'              => $this->name,
            'phone'             => $this->phone ?: null,
            'email'             => $this->email ?: null,
            'address'           => $this->address ?: null,
            'date_of_birth'     => $this->date_of_birth ?: null,
            'customer_label_id' => $this->customer_label_id,
            'pricing_tier_id'   => $this->pricing_tier_id,
            'is_loyalty_member' => $this->is_loyalty_member,
            'loyalty_points'    => $this->loyalty_points,
        ];

        if ($this->isEditing) {
            Customer::findOrFail($this->editingId)->update($data);
            Notification::make()->title('Pelanggan Diperbarui')->success()->send();
        } else {
            Customer::create($data);
            Notification::make()->title('Pelanggan Ditambahkan')->success()->send();
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
        Customer::findOrFail($this->deletingId)->delete();

        Notification::make()->title('Pelanggan Dihapus')->success()->send();

        $this->showDeleteModal = false;
        $this->deletingId      = null;
        $this->resetPage();
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $customers = Customer::with(['label', 'pricingTier'])
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Logo untuk sidebar
        $userBranchId    = Auth::user()->branches()->first()?->id;
        $branch          = $userBranchId ? \App\Models\Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-customers', [
            'customers'      => $customers,
            'labels'         => CustomerLabel::orderBy('name')->get(),
            'pricingTiers'   => PricingTier::orderBy('name')->get(),
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Data Pelanggan — POS']);
    }
}
