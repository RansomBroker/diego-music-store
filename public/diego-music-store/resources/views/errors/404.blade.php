@extends('errors.layout')

@php
    $pageTitle = '404 Halaman Tidak Ditemukan';
    $errorMessage = $exception?->getMessage();
    if (empty($errorMessage)) {
        $errorMessage = 'Halaman atau alamat URL yang Anda tuju tidak ditemukan, telah dipindahkan, atau tautan yang Anda masukkan salah.';
    }
    $user = auth()->user();
@endphp

@section('title', '404 - Halaman Tidak Ditemukan | Diego Music Store')

@section('content')
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 sm:p-10 shadow-xl text-center space-y-6">

    {{-- Error Icon / Illustration --}}
    <div class="relative mx-auto w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 flex items-center justify-center text-primary dark:text-blue-400 shadow-inner">
        <i class="ph-bold ph-compass text-4xl sm:text-5xl"></i>
        <span class="absolute -bottom-2 -right-2 px-2.5 py-0.5 bg-primary text-white rounded-full text-xs font-black tracking-wider uppercase shadow-md">
            404
        </span>
    </div>

    {{-- Heading & Description --}}
    <div class="space-y-2">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            Halaman Tidak Ditemukan
        </h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-lg mx-auto leading-relaxed">
            {{ $errorMessage }}
        </p>
    </div>

    {{-- Context Info (Requested URL) --}}
    <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-500 dark:text-slate-400 max-w-lg mx-auto break-all font-mono">
        <div class="flex items-center justify-center gap-1.5 mb-1 font-sans font-bold text-slate-700 dark:text-slate-300">
            <i class="ph-bold ph-link text-sm"></i>
            <span>Alamat URL yang diakses:</span>
        </div>
        <span>{{ request()->fullUrl() }}</span>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        {{-- Primary Action: Back to POS / Login --}}
        @if ($user)
            <a
                href="{{ route('pos.front-office') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 active:scale-95 text-white font-black text-sm shadow-lg shadow-primary/20 transition-all cursor-pointer"
            >
                <i class="ph-bold ph-storefront text-lg"></i>
                <span>Kembali ke Dashboard POS</span>
            </a>
        @else
            <a
                href="{{ route('pos.login') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 active:scale-95 text-white font-black text-sm shadow-lg shadow-primary/20 transition-all cursor-pointer"
            >
                <i class="ph-bold ph-sign-in text-lg"></i>
                <span>Masuk ke POS</span>
            </a>
        @endif

        {{-- Secondary Action: Back to Previous History --}}
        <button
            onclick="window.history.length > 1 ? window.history.back() : (window.location.href='{{ url('/') }}')"
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-sm border border-slate-200 dark:border-slate-600 transition-all cursor-pointer"
        >
            <i class="ph-bold ph-arrow-left text-lg"></i>
            <span>Halaman Sebelumnya</span>
        </button>

        {{-- Tertiary Action: Logout if Authenticated --}}
        @if ($user)
            <form action="{{ route('pos.logout') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 active:scale-95 text-rose-600 dark:text-rose-400 font-bold text-sm border border-rose-200 dark:border-rose-800/80 transition-all cursor-pointer"
                    title="Keluar dari akun saat ini"
                >
                    <i class="ph-bold ph-sign-out text-lg"></i>
                    <span>Keluar / Logout</span>
                </button>
            </form>
        @else
            <a
                href="{{ url('/') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-sm border border-slate-200 dark:border-slate-600 transition-all cursor-pointer"
            >
                <i class="ph-bold ph-house text-lg"></i>
                <span>Beranda</span>
            </a>
        @endif
    </div>

</div>
@endsection

