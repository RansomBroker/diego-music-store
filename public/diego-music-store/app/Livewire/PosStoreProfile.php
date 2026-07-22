<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Branch;
use App\Actions\Store\UpdateBranchProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosStoreProfile extends Component
{
    use WithFileUploads;

    public ?int $selectedBranchId = null;

    public string $store_name = '';
    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public bool $is_active = true;

    public $logo;
    public ?string $currentLogoUrl = null;

    public bool $showCreateModal = false;

    public function mount(): void
    {
        $userBranchId = Auth::user()->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : Branch::first();

        if ($branch) {
            $this->loadBranchData($branch);
        }
    }

    public function loadBranchData(Branch $branch): void
    {
        $this->selectedBranchId = $branch->id;
        $this->store_name       = $branch->store_name ?? $branch->name;
        $this->name             = $branch->name;
        $this->address          = $branch->address ?? '';
        $this->phone            = $branch->phone ?? '';
        $this->is_active        = (bool) $branch->is_active;
        $this->currentLogoUrl   = !empty($branch->logo_path) ? Storage::url($branch->logo_path) : null;
        $this->logo             = null;
    }

    public function selectBranch(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $this->loadBranchData($branch);
    }

    public function save(UpdateBranchProfile $updateBranchProfile): void
    {
        $this->validate([
            'store_name' => 'required|string|max:255',
            'name'       => 'required|string|max:255',
            'address'    => 'nullable|string|max:500',
            'phone'      => 'nullable|string|max:50',
            'logo'       => 'nullable|image|max:2048', // 2MB max
        ], [
            'store_name.required' => 'Nama Toko / Usaha wajib diisi.',
            'name.required'       => 'Nama Cabang wajib diisi.',
            'logo.image'          => 'File logo harus berupa gambar (png, jpg, webp).',
            'logo.max'            => 'Ukuran logo maksimal 2MB.',
        ]);

        $branch = Branch::findOrFail($this->selectedBranchId);

        $data = [
            'store_name' => $this->store_name,
            'name'       => $this->name,
            'address'    => $this->address,
            'phone'      => $this->phone,
            'is_active'  => $this->is_active,
        ];

        if ($this->logo) {
            $path = $this->logo->store('store-logos', 'public');
            $data['logo_path'] = $path;
        }

        $branch = $updateBranchProfile->execute($branch, $data);
        $this->loadBranchData($branch);

        Notification::make()->title('Profil Toko Berhasil Disimpan')->success()->send();
    }

    public function openCreateStore(): void
    {
        $this->reset(['store_name', 'name', 'address', 'phone', 'logo', 'currentLogoUrl']);
        $this->is_active = true;
        $this->showCreateModal = true;
    }

    public function createStore(UpdateBranchProfile $updateBranchProfile): void
    {
        $this->validate([
            'store_name' => 'required|string|max:255',
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string|max:500',
            'logo'       => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->logo->store('store-logos', 'public');
        }

        $newBranch = Branch::create([
            'name' => $this->name,
            'store_name' => $this->store_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'logo_path' => $logoPath,
            'is_active' => $this->is_active,
        ]);

        // Attach to current user
        Auth::user()->branches()->attach($newBranch->id);

        $this->showCreateModal = false;
        $this->loadBranchData($newBranch);

        Notification::make()->title('Toko / Cabang Baru Berhasil Didaftarkan')->success()->send();
    }

    public function render()
    {
        $branches = Branch::orderBy('name')->get();

        return view('livewire.pos-store-profile', [
            'branches' => $branches,
            'selectedLogoUrl' => $this->currentLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Register Nama Toko — POS']);
    }
}
