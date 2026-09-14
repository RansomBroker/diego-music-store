<?php

namespace App\Livewire;

use App\Models\Branch;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class PosAttendanceRadiuses extends Component
{
    use WithPagination;

    // ── Table Search & Filter ────────────────────────────────────────────
    public string $search = '';
    public int $perPage = 10;

    // ── Modal Edit Radius State ──────────────────────────────────────────
    public bool $showEditModal = false;
    public ?int $selectedBranchId = null;
    public string $selectedBranchName = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public int $attendance_radius_meters = 100;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Open Edit Radius Modal ────────────────────────────────────────────
    public function openEditModal(int $branchId): void
    {
        $branch = Branch::findOrFail($branchId);

        $this->selectedBranchId = $branch->id;
        $this->selectedBranchName = $branch->name;
        $this->latitude = $branch->latitude !== null ? (float) $branch->latitude : -0.03470087552402962;
        $this->longitude = $branch->longitude !== null ? (float) $branch->longitude : 109.33239215349418;
        $this->attendance_radius_meters = $branch->attendance_radius_meters ?: 100;

        $this->showEditModal = true;
    }

    // ── Save Radius Setting Action ────────────────────────────────────────
    public function saveRadius(): void
    {
        $this->validate([
            'selectedBranchId'         => 'required|exists:branches,id',
            'latitude'                 => 'required|numeric',
            'longitude'                => 'required|numeric',
            'attendance_radius_meters' => 'required|integer|min:10|max:10000',
        ], [
            'latitude.required'                 => 'Koordinat Latitude wajib diisi.',
            'longitude.required'                => 'Koordinat Longitude wajib diisi.',
            'attendance_radius_meters.required' => 'Radius toleransi absensi wajib diisi.',
            'attendance_radius_meters.min'      => 'Radius minimal adalah 10 meter.',
        ]);

        $branch = Branch::findOrFail($this->selectedBranchId);

        $branch->update([
            'latitude'                 => $this->latitude,
            'longitude'                => $this->longitude,
            'attendance_radius_meters' => $this->attendance_radius_meters,
        ]);

        Notification::make()
            ->title('Radius Absensi Disimpan')
            ->body("Pengaturan radius presensi untuk \"{$branch->name}\" berhasil diperbarui ke {$this->attendance_radius_meters} meter.")
            ->success()
            ->send();

        $this->showEditModal = false;
    }

    public function render()
    {
        $query = Branch::query();

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('store_name', 'like', $searchTerm)
                  ->orWhere('address', 'like', $searchTerm)
                  ->orWhere('city', 'like', $searchTerm);
            });
        }

        $branches = $query->orderBy('name', 'asc')->paginate($this->perPage);

        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();
        $selectedLogoUrl = ($activeBranch && !empty($activeBranch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($activeBranch->logo_path)
            : null;

        return view('livewire.pos-attendance-radiuses', [
            'branches'        => $branches,
            'activeBranch'    => $activeBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Pengaturan Radius Presensi Cabang — POS']);
    }
}
