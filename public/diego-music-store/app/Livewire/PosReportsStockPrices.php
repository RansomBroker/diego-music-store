<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\ReportHelper;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsStockPrices extends Component
{
    public ?int $selectedBranchId = null;
    public ?string $search = '';

    public function render()
    {
        $branches = Branch::all();
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $currentBranch = $this->selectedBranchId
            ? Branch::find($this->selectedBranchId)
            : ($userBranchId ? Branch::find($userBranchId) : Branch::first());

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $reportData = ReportHelper::getStockValuationReport(
            $this->selectedBranchId,
            $this->search
        );

        return view('livewire.pos-reports-stock-prices', [
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData' => $reportData,
        ])->layout('layouts.pos', ['title' => 'Daftar Stok & Penilaian Harga — POS']);
    }
}
