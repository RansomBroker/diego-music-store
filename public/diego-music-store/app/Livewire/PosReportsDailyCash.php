<?php

namespace App\Livewire;

use Livewire\Component;
use App\Helpers\ReportHelper;
use App\Models\Branch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsDailyCash extends Component
{
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $selectedBranchId = null;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function setQuickDateRange(string $preset)
    {
        switch ($preset) {
            case 'today':
                $this->dateFrom = Carbon::today()->format('Y-m-d');
                $this->dateTo = Carbon::today()->format('Y-m-d');
                break;
            case 'this_week':
                $this->dateFrom = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        $branches = Branch::all();
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $currentBranch = $this->selectedBranchId
            ? Branch::find($this->selectedBranchId)
            : ($userBranchId ? Branch::find($userBranchId) : Branch::first());

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $reportData = ReportHelper::getDailyCashReport(
            $this->dateFrom,
            $this->dateTo,
            $this->selectedBranchId
        );

        return view('livewire.pos-reports-daily-cash', [
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData' => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan Kas Harian ERP — POS']);
    }
}
