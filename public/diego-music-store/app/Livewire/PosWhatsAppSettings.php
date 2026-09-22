<?php

namespace App\Livewire;

use App\Actions\Notification\TestFonnteConnection;
use App\Helpers\BranchHelper;
use App\Helpers\FonnteHelper;
use App\Models\Branch;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class PosWhatsAppSettings extends Component
{
    public ?int $selectedBranchId = null;

    // Form fields
    public string $fonnte_token = '';
    public string $fonnte_whatsapp_number = '';
    public bool $is_whatsapp_enabled = true;
    public bool $showTokenPassword = false;

    // Test Modal State
    public bool $showTestModal = false;
    public string $test_number = '';
    public string $test_message = '';
    public ?array $test_result = null;
    public bool $is_testing = false;
    public ?array $device_status = null;

    public function mount(): void
    {
        $activeBranchId = session('pos_active_branch_id') ?: Auth::user()?->branches()->first()?->id;
        $branch = $activeBranchId ? Branch::find($activeBranchId) : Branch::first();

        if ($branch) {
            $this->loadBranchSettings($branch);
        }
    }

    public function loadBranchSettings(Branch $branch): void
    {
        $this->selectedBranchId       = $branch->id;
        $this->fonnte_token           = $branch->fonnte_token ?? '';
        $this->fonnte_whatsapp_number = $branch->fonnte_whatsapp_number ?? '';
        $this->is_whatsapp_enabled    = (bool) ($branch->is_whatsapp_enabled ?? true);
        $this->test_result            = null;
        $this->device_status          = null;
    }

    public function selectBranch(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $this->loadBranchSettings($branch);
        $this->dispatch('toast', [
            'type' => 'info',
            'message' => 'Pengaturan WhatsApp untuk cabang ' . $branch->name . ' dimuat.',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'fonnte_token'           => 'nullable|string|max:255',
            'fonnte_whatsapp_number' => 'nullable|string|max:50',
            'is_whatsapp_enabled'    => 'required|boolean',
        ]);

        $branch = Branch::findOrFail($this->selectedBranchId);

        $branch->update([
            'fonnte_token'           => trim($this->fonnte_token),
            'fonnte_whatsapp_number' => FonnteHelper::formatPhoneNumber($this->fonnte_whatsapp_number),
            'is_whatsapp_enabled'    => $this->is_whatsapp_enabled,
        ]);

        Notification::make()
            ->title('Pengaturan WhatsApp Cabang Berhasil Disimpan')
            ->body('Token dan nomor WhatsApp pengirim untuk cabang ' . $branch->name . ' telah diperbarui.')
            ->success()
            ->send();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Pengaturan WhatsApp cabang ' . $branch->name . ' berhasil disimpan.',
        ]);
    }

    public function checkStatus(): void
    {
        $branch = Branch::find($this->selectedBranchId);
        $token = !empty($this->fonnte_token) ? trim($this->fonnte_token) : ($branch?->fonnte_token);

        $this->device_status = FonnteHelper::checkDeviceStatus($token, $branch);

        if ($this->device_status['status']) {
            Notification::make()->title('Status Fonnte: Terhubung')->success()->send();
        } else {
            Notification::make()->title('Status Fonnte: ' . $this->device_status['message'])->danger()->send();
        }
    }

    public function openTestModal(): void
    {
        $branch = Branch::find($this->selectedBranchId);
        $this->test_number = Auth::user()?->phone ?? '';
        $this->test_message = "[Diego Music Store]\n\n"
            . "Tes Koneksi WhatsApp Fonnte untuk cabang: *" . ($branch?->name ?? 'Cabang Pusat') . "*.\n"
            . "Status: Integrasi WhatsApp Berhasil & Siap Digunakan!";
        $this->test_result = null;
        $this->showTestModal = true;
    }

    public function sendTestMessage(TestFonnteConnection $testFonnteConnection): void
    {
        $this->validate([
            'test_number' => 'required|string|min:8|max:20',
            'test_message' => 'required|string|max:1000',
        ], [
            'test_number.required' => 'Nomor WhatsApp test wajib diisi.',
            'test_message.required' => 'Pesan test wajib diisi.',
        ]);

        $this->is_testing = true;

        $branch = Branch::find($this->selectedBranchId);
        $token = !empty($this->fonnte_token) ? trim($this->fonnte_token) : null;

        $result = $testFonnteConnection->execute(
            targetNumber: $this->test_number,
            testMessage: $this->test_message,
            branch: $branch,
            token: $token
        );

        $this->test_result = $result;
        $this->is_testing = false;

        if ($result['status']) {
            Notification::make()->title('Uji Coba Kirim WA Berhasil!')->success()->send();
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Pesan test WA berhasil terkirim ke ' . $this->test_number,
            ]);
        } else {
            Notification::make()->title('Gagal Kirim WA Test: ' . $result['message'])->danger()->send();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal: ' . $result['message'],
            ]);
        }
    }

    public function render()
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranch = Branch::find($this->selectedBranchId);

        $currentActiveBranchId = session('pos_active_branch_id') ?: Auth::user()?->branches()->first()?->id;
        $currentBranch = $currentActiveBranchId ? Branch::find($currentActiveBranchId) : Branch::first();
        $selectedLogoUrl = (!empty($currentBranch?->logo_path))
            ? Storage::url($currentBranch->logo_path)
            : null;

        return view('livewire.pos-whatsapp-settings', [
            'branches'        => $branches,
            'selectedBranch'  => $selectedBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Setting WhatsApp Fonnte — POS']);
    }
}
