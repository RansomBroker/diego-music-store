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
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $activeSession = \App\Models\CashSession::with('user')
            ->where('branch_id', $activeBranchId)
            ->where('status', 'open')
            ->first();
        if ($activeSession) {
            $activeSessionInfo = [
                'id'           => $activeSession->id,
                'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
                'opening_cash' => $activeSession->opening_cash,
                'opened_by'    => $activeSession->user->name ?? auth()->user()->name,
            ];
        }
    }

    $currentActiveBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
    $currentBranchModel = $currentActiveBranchId ? \App\Models\Branch::find($currentActiveBranchId) : \App\Models\Branch::first();
    $userBranchList = auth()->check()
        ? (auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin'])
            ? \App\Models\Branch::where('is_active', true)->get()
            : auth()->user()->branches()->where('is_active', true)->get())
        : collect();

    // Attendance Info Calculation
    $currentEmployee = auth()->check() ? auth()->user()->employee : null;
    $isOwner = auth()->check() && auth()->user()->hasRole(['owner', 'Owner']);
    $todayDate = now()->format('Y-m-d');

    $todayAttendance = null;
    $usedOffDays = 0;
    $quotaOffDays = 4;
    $isOverQuota = false;
    $overCount = 0;
    $todayStatusText = 'Belum Presensi';

    if ($currentEmployee) {
        $usedOffDays = $currentEmployee->used_off_days_this_month;
        $quotaOffDays = $currentEmployee->monthly_off_days_quota;
        $isOverQuota = $currentEmployee->is_off_days_over_quota;
        $overCount = $currentEmployee->off_days_over_count;

        $todayAttendance = \App\Models\EmployeeAttendance::where('employee_id', $currentEmployee->id)
            ->where('date', $todayDate)
            ->first();

        if ($todayAttendance) {
            if ($todayAttendance->status === 'hadir') {
                $clockInFormatted = $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i') : '-';
                $todayStatusText = "Hadir ({$clockInFormatted})";
            } else {
                $todayStatusText = ucfirst(str_replace('_', ' ', $todayAttendance->status));
            }
        }
    }

    // Smart Navbar Attendance State Detection
    $clockState = 'not_clocked_in';
    $clockInTimeText = null;
    $clockOutTimeText = null;

    if ($todayAttendance) {
        if ($todayAttendance->clock_in && !$todayAttendance->clock_out) {
            $clockState = 'clocked_in';
            $clockInTimeText = $todayAttendance->clock_in->format('H:i');
        } elseif ($todayAttendance->clock_in && $todayAttendance->clock_out) {
            $clockState = 'clocked_out';
            $clockInTimeText = $todayAttendance->clock_in->format('H:i');
            $clockOutTimeText = $todayAttendance->clock_out->format('H:i');
        } elseif (in_array($todayAttendance->status, ['off_day', 'izin', 'sakit', 'alpha'])) {
            $clockState = 'clocked_out';
        }
    }

    $allBranchEmployees = collect();
    if ($isOwner && $currentBranchModel) {
        $allBranchEmployees = \App\Models\Employee::with(['attendances' => function ($q) use ($todayDate) {
            $q->where('date', $todayDate);
        }, 'user'])
        ->where('is_active', true)
        ->where(function ($q) use ($currentBranchModel) {
            $q->where('branch_id', $currentBranchModel->id)
              ->orWhereNull('branch_id');
        })
        ->get();
    }
@endphp

<header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md px-3 sm:px-6 py-2.5 sm:py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 z-10 transition-colors flex-shrink-0 w-full min-w-0">

    {{-- ====== LEFT: Sidebar Toggle + Back Button + Page Identity ====== --}}
    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
        {{-- Tombol Toggle Collapse / Expand Sidebar --}}
        <button
            onclick="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
            type="button"
            class="w-8.5 h-8.5 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer flex-shrink-0 shadow-sm active:scale-95"
            title="Buka / Tutup Sidebar Navigasi"
            aria-label="Toggle Sidebar"
        >
            <i class="ph-bold ph-list text-lg sm:text-lg"></i>
        </button>

        {{-- Tombol Kembali (hanya tampil jika showBack=true) --}}
        @if ($showBack)
            <a href="{{ $resolvedBackUrl }}"
               class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 sm:py-2.5 text-xs sm:text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-700 hover:bg-primary-light dark:hover:bg-blue-950/40 rounded-xl transition-all group flex-shrink-0"
               title="Kembali ke {{ $backLabel }}">
                <i class="ph-bold ph-arrow-left text-base sm:text-lg group-hover:-translate-x-0.5 transition-transform"></i>
                <span class="hidden sm:inline">{{ $backLabel }}</span>
            </a>
        @endif

        {{-- Judul & Subjudul --}}
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <h1 class="text-sm sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight leading-none truncate max-w-[105px] xs:max-w-[150px] sm:max-w-none">
                    {{ $currentBranchModel?->store_name ?: ($currentBranchModel?->name ?: 'Diego Music Store') }}
                </h1>

                @if ($userBranchList->count() > 1)
                    <div class="relative flex-shrink-0" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-primary/10 hover:bg-primary/20 text-primary dark:text-blue-400 border border-primary/20 rounded-full text-[10px] sm:text-xs font-bold transition-all cursor-pointer">
                            <i class="ph-bold ph-storefront text-xs"></i>
                            <span class="hidden sm:inline">{{ $currentBranchModel?->name ?: 'Pilih Cabang' }}</span>
                            <i class="ph-bold ph-caret-down text-[8px] sm:text-[10px]"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-52 sm:w-56 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 p-1.5">
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
                    <span class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full text-[10px] sm:text-xs font-bold hidden sm:inline-block flex-shrink-0">
                        {{ $currentBranchModel?->name ?: 'Cabang Utama' }}
                    </span>
                @endif
            </div>
            <p class="text-[9px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-0.5 sm:mt-1 truncate">
                {{ $pageTitle }}
            </p>
        </div>
    </div>

    {{-- ====== RIGHT: Actions Area ====== --}}
    <div class="flex items-center gap-1 sm:gap-2.5 flex-shrink-0">

        {{-- Slot Aksi Tambahan (custom per halaman) --}}
        {{ $slot }}

        {{-- Tombol Pintar Lakukan Presensi (Smart Detection) --}}
        @if ($clockState === 'not_clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_in"
                class="inline-flex items-center justify-center gap-1 sm:gap-2 w-8 h-8 sm:w-auto px-0 sm:px-3.5 py-0 sm:py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm hover:shadow-md transition-all cursor-pointer group flex-shrink-0"
                title="Klik untuk Clock In (Absen Masuk)"
            >
                <i class="ph-bold ph-sign-in text-base group-hover:scale-110 transition-transform"></i>
                <span class="hidden sm:inline">Clock In</span>
            </a>
        @elseif ($clockState === 'clocked_in')
            <a
                href="{{ route('pos.attendances') }}?action=clock_out"
                class="inline-flex items-center justify-center gap-1 sm:gap-2 w-8 h-8 sm:w-auto px-0 sm:px-3.5 py-0 sm:py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black shadow-sm hover:shadow-md transition-all cursor-pointer group flex-shrink-0"
                title="Masuk jam {{ $clockInTimeText }}. Klik untuk Clock Out (Absen Pulang)"
            >
                <i class="ph-bold ph-sign-out text-base group-hover:scale-110 transition-transform"></i>
                <div class="hidden sm:flex flex-col text-left leading-none">
                    <span class="font-extrabold">Clock Out</span>
                    <span class="text-[9px] opacity-90 mt-0.5 font-mono">In: {{ $clockInTimeText }}</span>
                </div>
            </a>
        @else
            <div
                class="inline-flex items-center justify-center gap-1 sm:gap-2 w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold flex-shrink-0"
                title="Presensi hari ini telah selesai"
            >
                <i class="ph-bold ph-check-circle text-base text-emerald-500"></i>
                <div class="hidden sm:flex flex-col text-left leading-none">
                    <span class="font-extrabold text-[11px]">Selesai</span>
                    @if ($clockInTimeText && $clockOutTimeText)
                        <span class="text-[9px] opacity-75 font-mono">{{ $clockInTimeText }} - {{ $clockOutTimeText }}</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Widget Info/Bar Absensi POS Karyawan --}}
        @if ($currentEmployee)
            <div class="relative flex-shrink-0" x-data="{ openAttendanceModal: false }">
                <button
                    @click="openAttendanceModal = !openAttendanceModal"
                    type="button"
                    class="flex items-center justify-center gap-1.5 w-8 h-8 sm:w-auto px-0 sm:px-3 py-0 sm:py-1.5 rounded-xl border text-xs font-bold transition-all cursor-pointer shadow-sm {{ $isOverQuota ? 'bg-rose-600 text-white border-rose-500 animate-pulse shadow-rose-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    title="Widget Presensi & Off Day Karyawan: {{ $usedOffDays }}/{{ $quotaOffDays }} Hari"
                >
                    <i class="ph-bold {{ $isOverQuota ? 'ph-warning-circle text-base text-white' : 'ph-clock text-base text-primary dark:text-blue-400' }}"></i>

                    <div class="hidden md:flex flex-col text-left leading-none">
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

                <!-- Popup Modal Presensi / Clock In / Out -->
                <div
                    x-show="openAttendanceModal"
                    @click.away="openAttendanceModal = false"
                    x-cloak
                    class="absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 p-4 space-y-3"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-user-check text-primary dark:text-blue-400"></i>
                            <h4 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-wider">Status Presensi {{ $currentEmployee->name }}</h4>
                        </div>
                        <button @click="openAttendanceModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                            <i class="ph-bold ph-x text-sm"></i>
                        </button>
                    </div>

                    <!-- Off Day Progress Bar -->
                    <div class="space-y-1">
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
                <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40 rounded-xl text-left"
                     title="Sesi ID: #{{ $activeSessionInfo['id'] }} • Dibuka: {{ $activeSessionInfo['opened_by'] ?? 'Kasir' }} • Modal: Rp {{ number_format($activeSessionInfo['opening_cash'], 0, ',', '.') }}">
                    <span class="relative flex h-2 w-2 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-450 dark:bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <div class="hidden lg:block leading-none">
                        <div class="text-xs font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                            Sesi: {{ $activeSessionInfo['opened_by'] ?? (auth()->user()->name ?? 'Kasir') }}
                        </div>
                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">
                            {{ substr($activeSessionInfo['opened_at'], -5) }} • Rp {{ number_format($activeSessionInfo['opening_cash'], 0, ',', '.') }}
                        </div>
                    </div>
                    <span class="hidden sm:inline lg:hidden text-[10px] font-black text-emerald-800 dark:text-emerald-300 uppercase">
                        Sesi ON
                    </span>
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
                        class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-black text-white hover:text-white bg-red-600 hover:bg-red-700 active:scale-95 rounded-xl shadow-md shadow-red-650/15 hover:shadow-red-650/25 transition-all cursor-pointer flex-shrink-0"
                        title="Tutup Sesi Kasir"
                    >
                        <i class="ph-bold ph-lock-key text-sm"></i>
                        <span class="hidden sm:inline">Tutup Sesi</span>
                    </button>
                @endif
            </div>
        @else
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40 rounded-xl text-left flex-shrink-0"
                 title="Sesi Kasir Tidak Aktif">
                <span class="h-2 w-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                <span class="text-xs font-black text-rose-800 dark:text-rose-350 uppercase tracking-wider leading-none">
                    Sesi Tidak Aktif
                </span>
            </div>
        @endif

        {{-- Dropdown Cabang (opsional, khusus POS Kasir) --}}
        @if ($showBranchSelector && count($branches) > 0)
            <div class="relative hidden sm:block flex-shrink-0">
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
                class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex-shrink-0 cursor-pointer"
                title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-base"></i>
            <i class="ph-bold ph-moon hidden dark:block text-base"></i>
        </button>

        {{-- Divider --}}
        <div class="h-6 sm:h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-0.5 sm:mx-1 hidden md:block flex-shrink-0"></div>

        {{-- Profil User Avatar --}}
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-slate-200 dark:bg-slate-650 overflow-hidden border-2 border-white dark:border-slate-700 shadow-sm flex-shrink-0"
                 title="{{ auth()->user()->name ?? 'User' }} ({{ auth()->user()->roles()->first()?->name ?? 'Kasir' }})">
                <img src="https://placehold.co/100x100/3b82f6/ffffff?text={{ substr(auth()->user()->name ?? 'AD', 0, 2) }}"
                     alt="Profile"
                     class="w-full h-full object-cover">
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
        <form id="pos-logout-form" action="{{ route('pos.logout') }}" method="POST" class="inline-block flex-shrink-0">
            @csrf
            <button type="submit"
                    class="flex items-center justify-center gap-1.5 w-8 h-8 sm:w-auto sm:px-3 sm:py-2 text-xs font-extrabold text-rose-600 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/80 rounded-xl transition-all cursor-pointer shadow-sm active:scale-95 group"
                    title="Logout (Keluar dari Sistem)">
                <i class="ph-bold ph-sign-out text-base group-hover:scale-110 transition-transform"></i>
                <span class="hidden sm:inline">Logout</span>
            </button>
        </form>

        {{-- Jam & Tanggal (Far Right) --}}
        <div class="hidden md:flex flex-col items-end justify-center min-w-[85px] leading-tight text-right flex-shrink-0">
            <span class="text-xs font-black text-slate-800 dark:text-slate-200">
                {{ now()->format('d M Y') }}
            </span>
            <span id="pos-realtime-clock" class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400 mt-0.5">
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
