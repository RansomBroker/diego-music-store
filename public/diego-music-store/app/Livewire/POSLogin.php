<?php

namespace App\Livewire;

use App\Helpers\PermissionHelper;
use App\Models\Branch;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

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
            $targetUrl = PermissionHelper::resolveFirstAccessibleFrontofficeUrl(Auth::user());
            if ($targetUrl) {
                return redirect()->to($targetUrl);
            }
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
            if (! $user->is_active || ! PermissionHelper::canAccessFrontoffice($user)) {
                Auth::logout();
                $message = ! $user->is_active
                    ? 'Akun Anda sedang nonaktif.'
                    : 'Akun Anda tidak memiliki hak akses ke Front Office.';
                $this->addError('email', $message);
                $this->dispatch('toast', type: 'danger', title: 'Akses Ditolak', body: $message);
                return;
            }

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
            $this->dispatch('toast', type: 'success', title: 'Berhasil Masuk', body: 'Selamat datang kembali di sistem kasir.');

            return $this->redirect(PermissionHelper::resolveFirstAccessibleFrontofficeUrl($user) ?? '/pos/front-office');
        }

        RateLimiter::hit($throttleKey, 60);

        $this->addError('email', 'Kredensial yang dimasukkan tidak cocok dengan data kami.');
        $this->dispatch('toast', type: 'danger', title: 'Gagal Masuk', body: 'Kredensial yang dimasukkan tidak cocok dengan data kami.');
    }

    public function selectBranchAndCompleteLogin()
    {
        if (! Auth::check()) {
            return $this->redirect('/pos/login');
        }

        $user = Auth::user();
        if (! PermissionHelper::canAccessFrontoffice($user)) {
            Auth::logout();
            return $this->redirect('/pos/login');
        }

        $branch = $user->branches()->where('branches.id', $this->selectedBranchId)->first();

        if (! $branch && ! \App\Helpers\BranchHelper::canAccessAllBranches($user)) {
            $this->addError('selectedBranchId', 'Anda tidak memiliki hak akses ke lokasi cabang ini.');
            $this->dispatch('toast', type: 'danger', title: 'Akses Ditolak', body: 'Anda tidak memiliki hak akses ke lokasi cabang ini.');
            return;
        }

        session(['pos_active_branch_id' => $this->selectedBranchId]);

        Notification::make()
            ->title('Berhasil Masuk')
            ->body('Cabang Aktif: ' . ($branch?->name ?: 'POS Store'))
            ->success()
            ->send();
        $this->dispatch('toast', type: 'success', title: 'Berhasil Masuk', body: 'Cabang Aktif: ' . ($branch?->name ?: 'POS Store'));

        return $this->redirect(PermissionHelper::resolveFirstAccessibleFrontofficeUrl($user) ?? '/pos/front-office');
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
