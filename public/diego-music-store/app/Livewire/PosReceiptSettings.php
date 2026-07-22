<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\ReceiptSetting;
use App\Actions\Setting\UpdateReceiptSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosReceiptSettings extends Component
{
    public ?int $branchId = null;

    public string $store_display_name = '';
    public string $header_text = '';
    public string $footer_text = '';
    public string $paper_width = '80mm';
    public bool $show_logo = true;
    public bool $show_customer = true;
    public bool $show_cashier = true;
    public bool $show_tax_details = true;
    public string $invoice_footer_notes = '';

    public function mount(): void
    {
        $userBranchId = Auth::user()->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : Branch::first();

        if ($branch) {
            $this->branchId = $branch->id;
            $this->loadSettings();
        }
    }

    public function loadSettings(): void
    {
        $setting = ReceiptSetting::where('branch_id', $this->branchId)->first();

        if ($setting) {
            $this->store_display_name   = $setting->store_display_name ?? '';
            $this->header_text          = $setting->header_text ?? '';
            $this->footer_text          = $setting->footer_text ?? '';
            $this->paper_width          = $setting->paper_width ?? '80mm';
            $this->show_logo            = (bool) $setting->show_logo;
            $this->show_customer        = (bool) $setting->show_customer;
            $this->show_cashier         = (bool) $setting->show_cashier;
            $this->show_tax_details     = (bool) $setting->show_tax_details;
            $this->invoice_footer_notes = $setting->invoice_footer_notes ?? '';
        } else {
            $branch = Branch::find($this->branchId);
            $this->store_display_name   = $branch?->store_name ?? $branch?->name ?? 'Diego Music Store';
            $this->header_text          = 'Selamat datang!';
            $this->footer_text          = 'Terima Kasih atas Kunjungan Anda. Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.';
            $this->paper_width          = '80mm';
            $this->show_logo            = true;
            $this->show_customer        = true;
            $this->show_cashier         = true;
            $this->show_tax_details     = true;
            $this->invoice_footer_notes = 'Pembayaran ditransfer ke BCA 123-456-7890 a.n Diego Music Store.';
        }
    }

    public function save(UpdateReceiptSettings $updateReceiptSettings): void
    {
        $this->validate([
            'paper_width' => 'required|in:58mm,80mm,A4',
            'header_text' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
            'invoice_footer_notes' => 'nullable|string|max:1000',
        ]);

        $updateReceiptSettings->execute($this->branchId, [
            'store_display_name'   => $this->store_display_name,
            'header_text'          => $this->header_text,
            'footer_text'          => $this->footer_text,
            'paper_width'          => $this->paper_width,
            'show_logo'            => $this->show_logo,
            'show_customer'        => $this->show_customer,
            'show_cashier'         => $this->show_cashier,
            'show_tax_details'     => $this->show_tax_details,
            'invoice_footer_notes' => $this->invoice_footer_notes,
        ]);

        Notification::make()->title('Setting Struk & Invoice Berhasil Disimpan')->success()->send();
    }

    public function render()
    {
        $branch = Branch::find($this->branchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        return view('livewire.pos-receipt-settings', [
            'branch' => $branch,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Setting Struk & Invoice — POS']);
    }
}
