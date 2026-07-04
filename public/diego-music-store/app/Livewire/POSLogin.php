<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class POSLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:4',
    ];

    protected $messages = [
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal terdiri dari 4 karakter.',
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

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($throttleKey);
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

    public function render()
    {
        return view('livewire.pos-login')
            ->layout('layouts.pos-auth');
    }
}
