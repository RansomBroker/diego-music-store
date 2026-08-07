<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Branch;
use App\Models\User;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosBranches extends Component
{
    use WithPagination, WithFileUploads;

    // Table & filter state
    public string $search = '';
    public ?string $selectedStatus = null;
    public int $perPage = 15;

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $store_name = 'Diego Music Store';
    public $logo;
    public ?string $existing_logo_path = null;
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $city = '';
    public string $province = '';
    public string $postal_code = '';
    public string $npwp = '';
    public string $bank_info = '';
    public string $receipt_header = '';
    public string $receipt_footer = '';
    public ?int $manager_id = null;
    public bool $is_active = true;

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedStatus = null;
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->reset([
            'name', 'store_name', 'logo', 'existing_logo_path', 'address',
            'phone', 'email', 'city', 'province', 'postal_code',
            'npwp', 'bank_info', 'receipt_header', 'receipt_footer',
            'manager_id', 'editingId', 'isEditing'
        ]);
        $this->store_name = 'Diego Music Store';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $branch = Branch::findOrFail($id);
        $this->editingId          = $branch->id;
        $this->isEditing          = true;
        $this->name               = $branch->name;
        $this->store_name         = $branch->store_name ?: 'Diego Music Store';
        $this->existing_logo_path = $branch->logo_path;
        $this->address            = $branch->address ?: '';
        $this->phone              = $branch->phone ?: '';
        $this->email              = $branch->email ?: '';
        $this->city               = $branch->city ?: '';
        $this->province           = $branch->province ?: '';
        $this->postal_code        = $branch->postal_code ?: '';
        $this->npwp               = $branch->npwp ?: '';
        $this->bank_info          = $branch->bank_info ?: '';
        $this->receipt_header     = $branch->receipt_header ?: '';
        $this->receipt_footer     = $branch->receipt_footer ?: '';
        $this->manager_id         = $branch->manager_id;
        $this->is_active          = (bool) $branch->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name'       => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'email'      => 'nullable|email|max:255',
        ], [
            'name.required'       => 'Nama cabang wajib diisi.',
            'store_name.required' => 'Nama toko wajib diisi.',
        ]);

        $logoPath = $this->existing_logo_path;
        if ($this->logo) {
            $logoPath = $this->logo->store('branch-logos', 'public');
        }

        $data = [
            'name'           => $this->name,
            'store_name'     => $this->store_name,
            'logo_path'      => $logoPath,
            'address'        => $this->address,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'city'           => $this->city,
            'province'       => $this->province,
            'postal_code'    => $this->postal_code,
            'npwp'           => $this->npwp,
            'bank_info'      => $this->bank_info,
            'receipt_header' => $this->receipt_header,
            'receipt_footer' => $this->receipt_footer,
            'manager_id'     => $this->manager_id,
            'is_active'      => $this->is_active,
        ];

        if ($this->isEditing) {
            $branch = Branch::findOrFail($this->editingId);
            UpdateBranch::execute($branch, $data);
            Notification::make()->title('Cabang Berhasil Diperbarui')->success()->send();
        } else {
            $branch = CreateBranch::execute($data);
            Notification::make()
                ->title('Cabang Baru Berhasil Dibuat')
                ->body('Cabang baru ' . $branch->name . ' telah disetup lengkap dengan stok awal & pengaturannya.')
                ->success()
                ->send();
        }

        $this->showModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $query = Branch::with(['manager', 'users'])
            ->latest('id');

        if ($this->search) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('city', 'like', $s)
                  ->orWhere('address', 'like', $s);
            });
        }

        if ($this->selectedStatus !== null && $this->selectedStatus !== '') {
            $query->where('is_active', (bool) $this->selectedStatus);
        }

        $branches = $query->paginate($this->perPage);

        $managers = User::orderBy('name')->get();

        $currentActiveBranchId = session('pos_active_branch_id') ?: Auth::user()?->branches()->first()?->id;
        $currentBranch = $currentActiveBranchId ? Branch::find($currentActiveBranchId) : Branch::first();
        $selectedLogoUrl = (!empty($currentBranch?->logo_path))
            ? Storage::url($currentBranch->logo_path)
            : null;

        return view('livewire.pos-branches', [
            'branches'        => $branches,
            'managers'        => $managers,
            'currentBranch'   => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Manajemen Cabang Toko — POS']);
    }
}
