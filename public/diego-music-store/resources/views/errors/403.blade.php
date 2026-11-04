@extends('errors.layout')

@php
    $pageTitle = '403 Akses Ditolak';
    $errorMessage = $exception?->getMessage();
    if (empty($errorMessage)) {
        $errorMessage = 'Anda tidak memiliki hak akses untuk membuka halaman ini atau akun Anda belum memiliki penugasan tempat bertugas aktif.';
    }
    $user = auth()->user();
    $userBranches = $user ? $user->branches()->where('branches.is_active', true)->get() : collect();
    $isUnassigned = $user && $userBranches->isEmpty() && !\App\Helpers\BranchHelper::canAccessAllBranches($user);
@endphp

@section('title', '403 - Akses Ditolak | Diego Music Store')

@section('content')
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 sm:p-10 shadow-xl text-center space-y-6">

    {{-- Error Icon / Illustration --}}
    <div class="relative mx-auto w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-inner">
        <i class="ph-bold ph-shield-warning text-4xl sm:text-5xl"></i>
        <span class="absolute -bottom-2 -right-2 px-2.5 py-0.5 bg-rose-600 text-white rounded-full text-xs font-black tracking-wider uppercase shadow-md">
            403
        </span>
    </div>

    {{-- Heading & Description --}}
    <div class="space-y-2">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ $isUnassigned ? 'Akun Belum Ditetapkan ke Tempat Bertugas' : 'Akses Ditolak (Forbidden)' }}
        </h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 max-w-lg mx-auto leading-relaxed">
            {{ $errorMessage }}
        </p>
    </div>

    {{-- User Status & Duty Context (If Authenticated) --}}
    @if ($user)
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60 text-left space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-800 pb-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary dark:text-blue-400 font-black text-xs flex items-center justify-center border border-primary/20">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                    </div>
                    <div>
                        <div class="text-xs font-black text-slate-900 dark:text-white">
                            {{ $user->name }}
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                            {{ $user->email }}
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    {{ $user->roles()->first()?->name ?? 'Karyawan' }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-semibold">Tempat Bertugas:</span>
                    @if ($isUnassigned)
                        <span class="inline-flex items-center gap-1 font-extrabold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-md border border-rose-200 dark:border-rose-900/50">
                            <i class="ph-bold ph-warning-circle"></i>
                            Belum Ditugaskan
                        </span>
                    @else
                        <span class="font-bold text-slate-700 dark:text-slate-200">
                            {{ $userBranches->pluck('name')->implode(', ') ?: '-' }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-semibold">Waktu Error:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200">
                        {{ now()->format('d M Y - H:i') }} WIB
                    </span>
                </div>
            </div>

            @if ($isUnassigned)
                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800 text-xs text-amber-800 dark:text-amber-300/90 flex items-start gap-2 bg-amber-50/70 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200/50 dark:border-amber-800/40">
                    <i class="ph-bold ph-info text-base text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                    <span>
                        <strong>Petunjuk:</strong> Akun Anda sudah aktif dan berhasil login, namun belum dimasukkan ke cabang/toko manapun. Silakan hubungi <strong>Administrator</strong> atau <strong>Supervisor</strong> untuk menetapkan cabang tugas akun Anda.
                    </span>
                </div>
            @endif
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        {{-- Primary Action: Logout / Switch Account --}}
        @if ($user)
            <form action="{{ route('pos.logout') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-black text-sm shadow-lg shadow-rose-600/20 transition-all cursor-pointer"
                    title="Keluar dari akun ini untuk berganti akun lain"
                >
                    <i class="ph-bold ph-sign-out text-lg"></i>
                    <span>Keluar / Ganti Akun</span>
                </button>
            </form>
        @else
            <a
                href="{{ route('pos.login') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 active:scale-95 text-white font-black text-sm shadow-lg shadow-primary/20 transition-all cursor-pointer"
            >
                <i class="ph-bold ph-sign-in text-lg"></i>
                <span>Masuk ke Akun Lain</span>
            </a>
        @endif

        {{-- Secondary Action: Refresh / Try Again --}}
        <button
            onclick="window.location.reload()"
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-sm border border-slate-200 dark:border-slate-600 transition-all cursor-pointer"
            title="Muat ulang halaman ini jika Anda baru saja ditugaskan oleh Admin"
        >
            <i class="ph-bold ph-arrow-clockwise text-lg"></i>
            <span>Coba Lagi / Refresh</span>
        </button>

        {{-- Tertiary Action: Back to Home / Front Office --}}
        <a
            href="{{ url('/') }}"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 active:scale-95 text-slate-700 dark:text-slate-200 font-bold text-sm border border-slate-200 dark:border-slate-600 transition-all cursor-pointer"
        >
            <i class="ph-bold ph-house text-lg"></i>
            <span>Beranda</span>
        </a>
    </div>

</div>
@endsection

