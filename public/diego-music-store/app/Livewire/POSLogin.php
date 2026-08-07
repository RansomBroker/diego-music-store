<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class POSLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    // Branch selection state for multi-branch users
    public bool $showBranchSelectStep = false;
    public $availableBranches = [];
    public ?int $selectedBranchId = null;

    protected $rules = [
        'email'    => 'required|string',
        'password' => 'required|min:4',
    ];

    protected $messages = [
        'email.required'    => 'Username atau email wajib diisi.',
        'password.required' => 'Password wajib diisi.',
        'password.min'      => 'Password minimal terdiri dari 4 karakter.',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->to('/pos');
        }
    }

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.");
            return;
        }

        $loginType = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginType => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($throttleKey);

            $user = Auth::user();
            $branches = $user->branches()->where('is_active', true)->get();

            // If user has access to multiple branches, show branch select step
            if ($branches->count() > 1) {
                $this->availableBranches = $branches;
                $this->selectedBranchId = $branches->first()->id;
                $this->showBranchSelectStep = true;
                return;
            }

            // Single branch or default branch fallback
            $activeBranchId = $branches->first()?->id ?: Branch::where('is_active', true)->first()?->id;
            session(['pos_active_branch_id' => $activeBranchId]);
            session()->regenerate();

            Notification::make()
                ->title('Berhasil Masuk')
                ->body('Selamat datang kembali di sistem kasir.')
                ->success()
                ->send();

            return redirect()->to('/pos');
        }

        RateLimiter::hit($throttleKey, 60);

        $this->addError('email', 'Kredensial yang dimasukkan tidak cocok dengan data kami.');
    }

    public function selectBranchAndCompleteLogin()
    {
        if (!Auth::check()) {
            return redirect()->to('/pos/login');
        }

        $user = Auth::user();
        $branch = $user->branches()->where('branches.id', $this->selectedBranchId)->first();

        if (!$branch && !$user->hasRole(['owner', 'admin', 'super_admin'])) {
            $this->addError('selectedBranchId', 'Anda tidak memiliki hak akses ke lokasi cabang ini.');
            return;
        }

        session(['pos_active_branch_id' => $this->selectedBranchId]);
        session()->regenerate();

        Notification::make()
            ->title('Berhasil Masuk')
            ->body('Cabang Aktif: ' . ($branch?->name ?: 'POS Store'))
            ->success()
            ->send();

        return redirect()->to('/pos');
    }

    public function backToCredentials()
    {
        $this->showBranchSelectStep = false;
        $this->availableBranches = [];
        Auth::logout();
    }

    public function render()
    {
        return view('livewire.pos-login')
            ->layout('layouts.pos-auth');
    }
}
