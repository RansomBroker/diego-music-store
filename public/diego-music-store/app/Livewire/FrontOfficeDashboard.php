<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Branch;
use App\Models\CashSession;

class FrontOfficeDashboard extends Component
{
    public function render()
    {
        $activeSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        $activeSessionInfo = $activeSession ? [
            'id'           => $activeSession->id,
            'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
        ] : null;

        // Ambil logo cabang aktif untuk sidebar
        $userBranchId = Auth::user()->branches()->first()?->id;
        $branchId     = $activeSession?->branch_id ?? $userBranchId;
        $branch       = $branchId ? Branch::find($branchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;

        return view('livewire.front-office-dashboard', [
            'activeSessionInfo' => $activeSessionInfo,
            'selectedLogoUrl'   => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Dashboard — POS']);
    }
}
