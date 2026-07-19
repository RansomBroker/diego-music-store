{{--
    Komponen Navbar Global POS — <x-pos.navbar ...>
    ================================================
    Props:
      - pageTitle       : string  — judul halaman (wajib)
      - pageSubtitle    : string  — subjudul halaman (opsional)
      - pageIcon        : string  — class icon Phosphor (opsional)
      - backUrl         : string  — URL tombol kembali, default → pos.front-office
      - backLabel       : string  — label tombol kembali, default 'Dashboard'
      - activeSessionInfo : array|null — info sesi aktif
      - todaySalesTotal : int     — total penjualan hari ini (untuk POS Kasir)
      - branches        : Collection — daftar cabang (untuk POS Kasir)
      - selectedBranchId: mixed   — cabang yang dipilih (untuk POS Kasir)
      - showBranchSelector : bool — tampilkan dropdown cabang
      - showCloseSession   : bool — tampilkan tombol Tutup Sesi
      - extraActions    : slot    — slot untuk tombol aksi tambahan di kanan
--}}
@props([
    'pageTitle'          => 'Point of Sale',
    'showBack'           => false,
    'backUrl'            => null,
    'backLabel'          => 'Dashboard',
    'activeSessionInfo'  => null,
    'todaySalesTotal'    => null,
    'branches'           => [],
    'selectedBranchId'   => null,
    'showBranchSelector' => false,
    'showCloseSession'   => false,
])

@php
    $resolvedBackUrl = $backUrl ?? route('pos.front-office');

    // Load active cashier session automatically if not passed
    if (empty($activeSessionInfo) && auth()->check()) {
        $activeSession = \App\Models\CashSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();
        if ($activeSession) {
            $activeSessionInfo = [
                'id' => $activeSession->id,
                'opened_at' => $activeSession->opened_at->format('d M Y H:i'),
                'opening_cash' => $activeSession->opening_cash,
            ];
        }
    }
@endphp

<header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md px-6 py-4.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 z-10 transition-colors flex-shrink-0">

    {{-- ====== LEFT: Back Button + Page Identity ====== --}}
    <div class="flex items-center gap-4">
        {{-- Tombol Kembali (hanya tampil jika showBack=true) --}}
        @if ($showBack)
            <a href="{{ $resolvedBackUrl }}"
               class="flex items-center gap-2 px-3.5 py-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-700 hover:bg-primary-light dark:hover:bg-blue-950/40 rounded-xl transition-all group"
               title="Kembali ke {{ $backLabel }}">
                <i class="ph-bold ph-arrow-left text-lg group-hover:-translate-x-0.5 transition-transform"></i>
                <span class="hidden sm:inline">{{ $backLabel }}</span>
            </a>
        @endif

        {{-- Judul & Subjudul --}}
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-55 tracking-tight leading-none">
                Diego Music Store
            </h1>
            <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1.5">
                {{ $pageTitle }}
            </p>
        </div>
    </div>

    {{-- ====== RIGHT: Actions Area ====== --}}
    <div class="flex items-center gap-3">

        {{-- Slot Aksi Tambahan (custom per halaman) --}}
        {{ $slot }}

        {{-- Status Sesi Kasir --}}
        @if (!empty($activeSessionInfo))
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2.5 px-3.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40 rounded-xl text-left"
                     title="Sesi ID: #{{ $activeSessionInfo['id'] }}">
                    <span class="relative flex h-2 w-2 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-450 dark:bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <div class="leading-none">
                        <div class="text-xs font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                            Sesi Aktif
                        </div>
                        <div class="text-[10.5px] font-bold text-emerald-700 dark:text-emerald-400 mt-1">
                            Mulai: {{ substr($activeSessionInfo['opened_at'], -5) }}
                            • Modal: Rp {{ number_format($activeSessionInfo['opening_cash'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- Tombol Tutup Sesi (opsional) --}}
                @if ($showCloseSession)
                    <button
                        type="button"
                        @click="$dispatch('confirm-open', {
                            title: 'Tutup Sesi Kasir?',
                            message: 'Anda akan dialihkan ke halaman penutupan sesi untuk mencatat kas fisik dan mencetak Z-Report.',
                            onConfirm: 'redirect:{{ route('pos.session') }}',
                            confirmLabel: 'Ya, Tutup Sesi',
                            isDanger: true
                        })"
                        class="flex items-center gap-1.5 px-3 py-2.5 text-xs font-black text-white hover:text-white bg-red-600 hover:bg-red-700 active:scale-95 rounded-xl shadow-md shadow-red-650/15 hover:shadow-red-650/25 transition-all cursor-pointer"
                        title="Tutup Sesi Kasir"
                    >
                        <i class="ph-bold ph-lock-key text-sm"></i>
                        <span>Tutup Sesi</span>
                    </button>
                @endif
            </div>
        @else
            <div class="flex items-center gap-2 px-3 py-1.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40 rounded-xl text-left">
                <span class="h-2 w-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span class="text-xs font-black text-rose-800 dark:text-rose-350 uppercase tracking-wider leading-none">
                    Sesi Tidak Aktif
                </span>
            </div>
        @endif

        {{-- Dropdown Cabang (opsional, khusus POS Kasir) --}}
        @if ($showBranchSelector && count($branches) > 0)
            <div class="relative">
                <select wire:model.live="selectedBranchId"
                        disabled
                        class="pl-3 pr-8 py-2 bg-slate-100 dark:bg-slate-700 border-none rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 outline-none cursor-not-allowed opacity-80"
                        title="Cabang terkunci oleh sesi kasir aktif. Tutup sesi untuk mengganti cabang.">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Dark Mode Toggle --}}
        <button onclick="toggleDarkMode()"
                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-base"></i>
            <i class="ph-bold ph-moon hidden dark:block text-base"></i>
        </button>

        {{-- Divider --}}
        <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block"></div>

        {{-- Profil User + Penjualan Hari Ini --}}
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-slate-200 dark:bg-slate-650 overflow-hidden border-2 border-white dark:border-slate-700 shadow-md cursor-pointer flex-shrink-0">
                <img src="https://placehold.co/100x100/3b82f6/ffffff?text={{ substr(auth()->user()->name ?? 'AD', 0, 2) }}"
                     alt="Profile"
                     class="w-full h-full object-cover">
            </div>
            <div class="hidden lg:block text-left">
                <div class="text-sm font-black text-slate-855 dark:text-slate-105 leading-tight">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </div>
                @if ($todaySalesTotal !== null)
                    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">
                        Penjualan Hari Ini:
                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">
                            Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}
                        </span>
                    </div>
                @else
                    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">
                        {{ auth()->user()->roles()->first()?->name ?? 'Kasir' }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Jam & Tanggal (Far Right) --}}
        <div class="hidden sm:flex flex-col items-end justify-center min-w-[95px] leading-tight text-right">
            <span class="text-sm font-black text-slate-850 dark:text-slate-200">
                {{ now()->format('d M Y') }}
            </span>
            <span id="pos-realtime-clock" class="text-xs font-mono font-black text-slate-500 dark:text-slate-400 mt-1">
                00:00:00
            </span>
        </div>

    </div>
</header>

<script>
    function toggleDarkMode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    (function () {
        const el = document.getElementById('pos-realtime-clock');
        if (!el) return;
        function tick() {
            el.textContent = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            }).replace(/\./g, ':');
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
