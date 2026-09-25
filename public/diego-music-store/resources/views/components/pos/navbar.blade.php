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
    extract(\App\Helpers\PosNavbarHelper::getContext($activeSessionInfo, $backUrl));
@endphp

<header 
    x-data="{ mobileMenuOpen: false }"
    class="bg-white dark:bg-slate-800 px-3 sm:px-6 py-2.5 sm:py-3.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 z-30 transition-colors flex-shrink-0 w-full min-w-0 shadow-xs"
>

    {{-- ====== LEFT: Sidebar Toggle + Back Button + Page Identity ====== --}}
    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 sm:flex-initial mr-2">
        {{-- Tombol Toggle Collapse / Expand Sidebar --}}
        <button
            onclick="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
            type="button"
            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-all cursor-pointer flex-shrink-0 shadow-xs active:scale-95"
            title="Buka / Tutup Sidebar Navigasi"
            aria-label="Toggle Sidebar"
        >
            <i class="ph-bold ph-list text-lg"></i>
        </button>

        {{-- Tombol Kembali (hanya tampil jika showBack=true) --}}
        @if ($showBack)
            <a href="{{ $resolvedBackUrl }}"
               class="flex items-center justify-center gap-1.5 px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-700 hover:bg-primary/10 dark:hover:bg-blue-950/40 rounded-xl transition-all group flex-shrink-0"
               title="Kembali ke {{ $backLabel }}">
                <i class="ph-bold ph-arrow-left text-base group-hover:-translate-x-0.5 transition-transform"></i>
                <span class="hidden sm:inline">{{ $backLabel }}</span>
            </a>
        @endif

        {{-- Judul & Identitas Halaman (Responsive) --}}
        <div class="min-w-0 flex-1">
            <!-- Mobile View (< sm): Judul Halaman Prioritas Utama -->
            <div class="block sm:hidden">
                <h1 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate leading-tight tracking-tight">
                    {{ $pageTitle }}
                </h1>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 truncate flex items-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary inline-block flex-shrink-0"></span>
                    <span class="truncate">{{ $currentBranchModel?->store_name ?: ($currentBranchModel?->name ?: 'Diego Music Store') }}</span>
                </p>
            </div>

            <!-- Desktop View (>= sm): Nama Toko & Cabang di atas, Judul di bawah -->
            <div class="hidden sm:block">
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg lg:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight leading-none truncate">
                        {{ $currentBranchModel?->store_name ?: ($currentBranchModel?->name ?: 'Diego Music Store') }}
                    </h1>

                    @if ($userBranchList->count() > 1)
                        <div class="relative flex-shrink-0" x-data="{ openBranchDropdown: false }">
                            <button 
                                @click="openBranchDropdown = !openBranchDropdown" 
                                type="button"
                                class="flex items-center gap-1 px-2.5 py-1 bg-primary/10 hover:bg-primary/20 text-primary dark:text-blue-400 border border-primary/20 rounded-full text-xs font-bold transition-all cursor-pointer"
                            >
                                <i class="ph-bold ph-storefront text-xs"></i>
                                <span>{{ $currentBranchModel?->name ?: 'Pilih Cabang' }}</span>
                                <i class="ph-bold ph-caret-down text-[10px]"></i>
                            </button>

                            <div 
                                x-show="openBranchDropdown" 
                                @click.away="openBranchDropdown = false" 
                                x-cloak 
                                class="absolute left-0 mt-2 w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 p-1.5"
                            >
                                <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ganti Cabang Aktif</div>
                                @foreach ($userBranchList as $b)
                                    <a href="{{ route('pos.switch-branch', $b->id) }}" class="flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl {{ $b->id == $currentActiveBranchId ? 'bg-primary text-white' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                                        <span>{{ $b->name }}</span>
                                        @if ($b->id == $currentActiveBranchId)
                                            <i class="ph-bold ph-check"></i>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <span class="px-2.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full text-xs font-bold inline-block flex-shrink-0">
                            {{ $currentBranchModel?->name ?: 'Cabang Utama' }}
                        </span>
                    @endif
                </div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-1 truncate">
                    {{ $pageTitle }}
                </p>
            </div>
        </div>
    </div>

    {{-- ====== RIGHT (DESKTOP): Full Action Toolbar (>= md) ====== --}}
    <div class="hidden md:flex items-center gap-2 lg:gap-2.5 flex-shrink-0">

        {{-- Slot Aksi Tambahan (custom per halaman) --}}
        {{ $slot }}

        {{-- Tombol Pintar Presensi (Smart Detection) --}}
        @if ($clockState === 'not_clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_in"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm hover:shadow-md transition-all cursor-pointer group flex-shrink-0"
                title="Klik untuk Clock In (Absen Masuk)"
            >
                <i class="ph-bold ph-sign-in text-base group-hover:scale-110 transition-transform"></i>
                <span>Clock In</span>
            </a>
        @elseif ($clockState === 'clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_out"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black shadow-sm hover:shadow-md transition-all cursor-pointer group flex-shrink-0"
                title="Masuk jam {{ $clockInTimeText }}. Klik untuk Clock Out (Absen Pulang)"
            >
                <i class="ph-bold ph-sign-out text-base group-hover:scale-110 transition-transform"></i>
                <div class="flex flex-col text-left leading-none">
                    <span class="font-extrabold">Clock Out</span>
                    <span class="text-[9px] opacity-90 mt-0.5">In: {{ $clockInTimeText }}</span>
                </div>
            </a>
        @else
            <div
                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold flex-shrink-0"
                title="Presensi hari ini telah selesai"
            >
                <i class="ph-bold ph-check-circle text-base text-emerald-500"></i>
                <div class="flex flex-col text-left leading-none">
                    <span class="font-extrabold text-[11px]">Selesai</span>
                    @if ($clockInTimeText && $clockOutTimeText)
                        <span class="text-[9px] opacity-75">{{ $clockInTimeText }} - {{ $clockOutTimeText }}</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Widget Presensi Karyawan & Kuota Off-Day --}}
        @if ($currentEmployee)
            <div class="relative flex-shrink-0" x-data="{ openAttendanceModal: false }">
                <button
                    @click="openAttendanceModal = !openAttendanceModal"
                    type="button"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all cursor-pointer shadow-xs {{ $isOverQuota ? 'bg-rose-600 text-white border-rose-500 animate-pulse shadow-rose-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    title="Widget Presensi & Off Day Karyawan: {{ $usedOffDays }}/{{ $quotaOffDays }} Hari"
                >
                    <i class="ph-bold {{ $isOverQuota ? 'ph-warning-circle text-base text-white' : 'ph-clock text-base text-primary dark:text-blue-400' }}"></i>

                    <div class="flex flex-col text-left leading-none">
                        <div class="flex items-center gap-1.5 text-[11px]">
                            <span class="font-extrabold">Off Day:</span>
                            <span>{{ $usedOffDays }} / {{ $quotaOffDays }} Hari</span>
                            @if ($isOverQuota)
                                <span class="bg-white text-rose-700 text-[10px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wider">OVER!</span>
                            @endif
                        </div>
                        <div class="text-[10px] font-semibold opacity-80 mt-0.5">
                            Hari Ini: <span class="capitalize font-bold">{{ $todayStatusText }}</span>
                        </div>
                    </div>
                </button>

                <!-- Popup Modal Presensi -->
                <div
                    x-show="openAttendanceModal"
                    @click.away="openAttendanceModal = false"
                    x-cloak
                    class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 p-4 space-y-3"
                >
                    <div class="flex items-center justify-between border-b border-slate-150 dark:border-slate-700 pb-2">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-user-check text-primary dark:text-blue-400"></i>
                            <h4 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-wider">Status Presensi {{ $currentEmployee->name }}</h4>
                        </div>
                        <button @click="openAttendanceModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <i class="ph-bold ph-x text-sm"></i>
                        </button>
                    </div>

                    <!-- Off Day Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-500 dark:text-slate-400">Penggunaan Off Day Bulan Ini</span>
                            <span class="{{ $isOverQuota ? 'text-rose-600 dark:text-rose-400 font-extrabold' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $usedOffDays }} / {{ $quotaOffDays }} Hari
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                            @php
                                $percent = min(100, ($usedOffDays / max(1, $quotaOffDays)) * 100);
                            @endphp
                            <div class="h-full transition-all duration-300 {{ $isOverQuota ? 'bg-rose-500' : 'bg-primary dark:bg-blue-500' }}" style="width: {{ $percent }}%;"></div>
                        </div>
                        @if ($isOverQuota)
                            <p class="text-[11px] font-extrabold text-rose-600 dark:text-rose-400 flex items-center gap-1 mt-1">
                                <i class="ph-bold ph-warning"></i>
                                Perhatian: Jatah Off Day telah melebihi kuota sebanyak {{ $overCount }} hari!
                            </p>
                        @endif
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                            Status Hari Ini: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $todayStatusText }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Status Sesi Kasir --}}
        @if (!empty($activeSessionInfo))
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40 rounded-xl text-left"
                     title="Sesi ID: #{{ $activeSessionInfo['id'] }} • Dibuka: {{ $activeSessionInfo['opened_by'] ?? 'Kasir' }} • Modal: Rp {{ number_format($activeSessionInfo['opening_cash'], 0, ',', '.') }}">
                    <span class="relative flex h-2 w-2 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <div class="leading-none">
                        <div class="text-xs font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                            Sesi: {{ $activeSessionInfo['opened_by'] ?? (auth()->user()->name ?? 'Kasir') }}
                        </div>
                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">
                            {{ substr($activeSessionInfo['opened_at'], -5) }} • Rp {{ number_format($activeSessionInfo['opening_cash'], 0, ',', '.') }}
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
                        class="flex items-center gap-1 px-3 py-2 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 active:scale-95 rounded-xl shadow-xs transition-all cursor-pointer flex-shrink-0"
                        title="Tutup Sesi Kasir"
                    >
                        <i class="ph-bold ph-lock-key text-sm"></i>
                        <span>Tutup Sesi</span>
                    </button>
                @endif
            </div>
        @else
            <div class="flex items-center gap-1.5 px-3 py-2 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40 rounded-xl text-left flex-shrink-0"
                 title="Sesi Kasir Tidak Aktif">
                <span class="h-2 w-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span class="text-xs font-black text-rose-800 dark:text-rose-350 uppercase tracking-wider leading-none">
                    Sesi Nonaktif
                </span>
            </div>
        @endif

        {{-- Dropdown Cabang Terkunci (POS Kasir) --}}
        @if ($showBranchSelector && count($branches) > 0)
            <div class="relative flex-shrink-0">
                <select wire:model.live="selectedBranchId"
                        disabled
                        class="pl-2.5 pr-7 py-1.5 bg-slate-100 dark:bg-slate-700 border-none rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 outline-none cursor-not-allowed opacity-80"
                        title="Cabang terkunci oleh sesi kasir aktif. Tutup sesi untuk mengganti cabang.">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Dark Mode Toggle --}}
        <button onclick="toggleDarkMode()"
                type="button"
                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex-shrink-0 cursor-pointer shadow-xs"
                title="Ubah Tema Gelap / Terang">
            <i class="ph-bold ph-sun dark:hidden text-base text-amber-500"></i>
            <i class="ph-bold ph-moon hidden dark:block text-base text-blue-400"></i>
        </button>

        {{-- Divider --}}
        <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-1 flex-shrink-0"></div>

        {{-- Profil User Avatar --}}
        <div class="flex items-center gap-2.5 flex-shrink-0">
            <div class="w-9 h-9 rounded-xl bg-slate-200 dark:bg-slate-600 overflow-hidden border border-slate-200 dark:border-slate-700 shadow-xs flex-shrink-0 flex items-center justify-center font-bold text-xs text-primary dark:text-blue-400"
                 title="{{ auth()->user()->name ?? 'User' }} ({{ auth()->user()->roles()->first()?->name ?? 'Kasir' }})">
                @if (auth()->user()?->avatar_full_url)
                    <img src="{{ auth()->user()->avatar_full_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                @endif
            </div>
            <div class="hidden xl:block text-left leading-tight">
                <div class="text-sm font-black text-slate-800 dark:text-slate-100 leading-tight">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </div>
                @if ($todaySalesTotal !== null)
                    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">
                        Penjualan:
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

        {{-- Tombol Logout --}}
        <form action="{{ route('pos.logout') }}" method="POST" class="inline-block flex-shrink-0">
            @csrf
            <button type="submit"
                    class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-extrabold text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/80 rounded-xl transition-all cursor-pointer shadow-xs active:scale-95 group"
                    title="Logout (Keluar dari Sistem)">
                <i class="ph-bold ph-sign-out text-base group-hover:scale-110 transition-transform"></i>
                <span>Logout</span>
            </button>
        </form>

        {{-- Jam & Tanggal (Far Right) --}}
        <div class="hidden lg:flex flex-col items-end justify-center min-w-[80px] leading-tight text-right flex-shrink-0 pl-1">
            <span class="text-xs font-black text-slate-800 dark:text-slate-200">
                {{ now()->format('d M Y') }}
            </span>
            <span id="pos-realtime-clock" class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-0.5">
                00:00:00
            </span>
        </div>

    </div>

    {{-- ====== RIGHT (MOBILE / TABLET COMPACT): Lean & Clean (< md) ====== --}}
    <div class="flex md:hidden items-center gap-1.5 flex-shrink-0">

        {{-- Slot Aksi Tambahan jika ada --}}
        {{ $slot }}

        {{-- Tombol Ringkas Presensi Clock In / Out --}}
        @if ($clockState === 'not_clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_in"
                class="w-9 h-9 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-xs active:scale-95 transition-all flex-shrink-0"
                title="Clock In (Absen Masuk)"
            >
                <i class="ph-bold ph-sign-in text-base"></i>
            </a>
        @elseif ($clockState === 'clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_out"
                class="w-9 h-9 rounded-xl bg-amber-600 hover:bg-amber-700 text-white flex items-center justify-center shadow-xs active:scale-95 transition-all flex-shrink-0"
                title="Clock Out (In: {{ $clockInTimeText }})"
            >
                <i class="ph-bold ph-sign-out text-base"></i>
            </a>
        @else
            <div
                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-emerald-500 flex items-center justify-center border border-slate-200 dark:border-slate-600 flex-shrink-0"
                title="Presensi Selesai"
            >
                <i class="ph-bold ph-check-circle text-base"></i>
            </div>
        @endif

        {{-- Tombol Pemicu Mobile Action Drawer --}}
        <button
            @click="mobileMenuOpen = true"
            type="button"
            class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-200 dark:hover:bg-slate-600 transition-all cursor-pointer active:scale-95 flex-shrink-0"
            title="Menu & Akun Kasir"
            aria-label="Buka Menu Akun"
        >
            <div class="relative w-7 h-7 rounded-lg overflow-hidden border border-white dark:border-slate-700 shadow-xs flex-shrink-0 flex items-center justify-center bg-slate-200 dark:bg-slate-700 font-bold text-[10px] text-primary dark:text-blue-400">
                @if (auth()->user()?->avatar_full_url)
                    <img src="{{ auth()->user()->avatar_full_url }}"
                         alt="Profile"
                         class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                @endif
                @if (!empty($activeSessionInfo))
                    <span class="absolute bottom-0 right-0 w-2 h-2 bg-emerald-500 rounded-full border border-white dark:border-slate-800"></span>
                @endif
            </div>
            <i class="ph-bold ph-dots-three-vertical text-slate-500 dark:text-slate-300 text-sm pr-0.5"></i>
        </button>

    </div>

    {{-- ====== MOBILE ACTION SHEET DRAWER ====== --}}
    <template x-teleport="body">
    <div
        x-show="mobileMenuOpen"
        @keydown.escape.window="mobileMenuOpen = false"
        class="fixed inset-0 z-50 md:hidden flex flex-col justify-end"
        x-cloak
    >
        <!-- Backdrop -->
        <div
            x-show="mobileMenuOpen"
            @click="mobileMenuOpen = false"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"
        ></div>

        <!-- Bottom Sheet Panel -->
        <div
            x-show="mobileMenuOpen"
            @click.away="mobileMenuOpen = false"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-t-3xl shadow-2xl p-5 max-h-[85vh] overflow-y-auto space-y-4 no-scrollbar z-10"
        >
            <!-- Handle Drag Pill -->
            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-1"></div>

            <!-- Header Info User -->
            <div class="flex items-center justify-between border-b border-slate-150 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-primary/10 text-primary dark:text-blue-400 overflow-hidden border border-primary/20 flex items-center justify-center font-black text-sm shadow-xs flex-shrink-0">
                        @if (auth()->user()?->avatar_full_url)
                            <img src="{{ auth()->user()->avatar_full_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-black text-slate-900 dark:text-white leading-tight truncate">
                            {{ auth()->user()->name ?? 'Administrator' }}
                        </div>
                        <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5 truncate">
                            {{ auth()->user()->roles()->first()?->name ?? 'Kasir' }} &bull; {{ $currentBranchModel?->name ?: 'Cabang Utama' }}
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center cursor-pointer flex-shrink-0"
                    title="Tutup Menu"
                >
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            <!-- Status Sesi Kasir -->
            <div class="p-3.5 rounded-2xl border {{ !empty($activeSessionInfo) ? 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/40' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700/60' }} space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 font-black {{ !empty($activeSessionInfo) ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">
                        <span class="w-2 h-2 rounded-full {{ !empty($activeSessionInfo) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span>{{ !empty($activeSessionInfo) ? 'Sesi Kasir Aktif' : 'Sesi Kasir Tidak Aktif' }}</span>
                    </div>
                    @if (!empty($activeSessionInfo))
                        <span class="text-[11px] font-extrabold text-emerald-800 dark:text-emerald-200">
                            Modal: Rp {{ number_format($activeSessionInfo['opening_cash'] ?? 0, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                @if (!empty($activeSessionInfo))
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center justify-between pt-1 border-t border-emerald-200/50 dark:border-emerald-800/30">
                        <span>Dibuka: {{ $activeSessionInfo['opened_by'] ?? (auth()->user()?->name ?? 'Kasir') }} ({{ !empty($activeSessionInfo['opened_at']) ? substr($activeSessionInfo['opened_at'], -5) : '--:--' }} WIB)</span>
                        @if ($showCloseSession)
                            <button
                                type="button"
                                @click="mobileMenuOpen = false; $dispatch('confirm-open', {
                                    title: 'Tutup Sesi Kasir?',
                                    message: 'Anda akan dialihkan ke halaman penutupan sesi untuk mencatat kas fisik dan mencetak Z-Report.',
                                    onConfirm: 'redirect:{{ route('pos.session') }}',
                                    confirmLabel: 'Ya, Tutup Sesi',
                                    isDanger: true
                                })"
                                class="text-[11px] font-black text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                            >
                                Tutup Sesi &rarr;
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Status Presensi & Jatah Off-Day -->
            @if ($currentEmployee)
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-500 dark:text-slate-400">Presensi Hari Ini:</span>
                        <span class="font-extrabold text-slate-800 dark:text-white capitalize">{{ $todayStatusText }}</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                            <span>Jatah Off Day Bulan Ini</span>
                            <span class="{{ $isOverQuota ? 'text-rose-600 dark:text-rose-400 font-extrabold' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $usedOffDays }} / {{ $quotaOffDays }} Hari
                            </span>
                        </div>
                        @php
                            $percent = min(100, ($usedOffDays / max(1, $quotaOffDays)) * 100);
                        @endphp
                        <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300 {{ $isOverQuota ? 'bg-rose-500' : 'bg-primary' }}" style="width: {{ $percent }}%;"></div>
                        </div>
                        @if ($isOverQuota)
                            <p class="text-[10px] font-bold text-rose-600 dark:text-rose-400 mt-1">
                                Perhatian: Melebihi kuota sebanyak {{ $overCount }} hari!
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Ganti Cabang Aktif (Jika Punya Akses > 1 Cabang) -->
            @if ($userBranchList->count() > 1)
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        Pilih Cabang Aktif
                    </label>
                    <div class="grid grid-cols-1 gap-1.5 max-h-40 overflow-y-auto no-scrollbar">
                        @foreach ($userBranchList as $b)
                            <a
                                href="{{ route('pos.switch-branch', $b->id) }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition {{ $b->id == $currentActiveBranchId ? 'bg-primary text-white shadow-xs shadow-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                            >
                                <span>{{ $b->name }}</span>
                                @if ($b->id == $currentActiveBranchId)
                                    <i class="ph-bold ph-check text-sm"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pengaturan Cepat: Mode Gelap & Logout -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-150 dark:border-slate-800">
                <!-- Tombol Mode Gelap -->
                <button
                    type="button"
                    onclick="toggleDarkMode()"
                    class="flex items-center justify-center gap-2 p-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-black hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                >
                    <i class="ph-bold ph-sun dark:hidden text-base text-amber-500"></i>
                    <i class="ph-bold ph-moon hidden dark:block text-base text-blue-400"></i>
                    <span class="dark:hidden">Mode Gelap</span>
                    <span class="hidden dark:inline">Mode Terang</span>
                </button>

                <!-- Tombol Logout -->
                <form action="{{ route('pos.logout') }}" method="POST" class="w-full">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 p-3 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/80 rounded-xl text-xs font-black hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer"
                    >
                        <i class="ph-bold ph-sign-out text-base"></i>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    </template>
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
