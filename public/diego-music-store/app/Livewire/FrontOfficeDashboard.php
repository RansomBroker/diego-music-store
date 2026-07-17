<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FrontOfficeDashboard extends Component
{
    public function render()
    {
        $activeSession = \App\Models\CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        $activeSessionInfo = $activeSession ? [
            'id' => $activeSession->id,
            'opened_at' => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
        ] : null;

        return view('livewire.front-office-dashboard', [
            'activeSessionInfo' => $activeSessionInfo,
        ])->layout('layouts.pos', ['title' => 'Front Office Dashboard']);
    }
}
