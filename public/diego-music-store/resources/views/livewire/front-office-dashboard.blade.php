<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header — menggunakan komponen navbar POS global -->
        <x-pos.navbar
            pageTitle="Dashboard"
            backLabel="Backoffice"
            backUrl="/backoffice"
            :activeSessionInfo="$activeSessionInfo"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Card Status User Login & Presensi -->
                <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-3xl p-6 shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/70 dark:border-slate-700/80 transition-colors">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        
                        <!-- Left (Cols 7): User Identity & Greeting -->
                        <div class="lg:col-span-7 flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 dark:bg-slate-700 border border-primary/20 dark:border-slate-600 overflow-hidden flex-shrink-0 shadow-sm flex items-center justify-center text-primary dark:text-blue-400 font-black text-xl">
                                {{ substr(auth()->user()->name ?? 'AD', 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight truncate">
                                        Selamat Datang, {{ auth()->user()->name }} 👋
                                    </h2>
                                    <span class="px-2.5 py-0.5 bg-primary/10 dark:bg-primary/30 border border-primary/20 dark:border-primary/40 text-primary dark:text-blue-300 rounded-full text-[10px] font-black uppercase tracking-wider flex-shrink-0">
                                        {{ auth()->user()->roles()->first()?->name ?: 'Staff' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400 font-semibold mt-1">
                                    <span class="flex items-center gap-1 text-slate-700 dark:text-slate-300">
                                        <i class="ph-bold ph-storefront text-primary"></i>
                                        {{ $currentBranch?->name ?: 'Cabang Utama' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right (Cols 5): Presensi Ringkas & Tombol Aksi -->
                        <div class="lg:col-span-5 flex flex-col sm:flex-row items-start sm:items-center justify-between lg:justify-end gap-4 border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-slate-700/80 pt-4 lg:pt-0 lg:pl-6">
                            <div class="space-y-1">
                                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    Presensi Hari Ini ({{ now()->translatedFormat('d M Y') }})
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $currentUserTodayAttendance?->clock_in ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span>Masuk: <strong class="font-mono text-slate-900 dark:text-slate-100">{{ $currentUserTodayAttendance?->clock_in ? $currentUserTodayAttendance->clock_in->format('H:i') : '-' }}</strong></span>
                                    </div>
                                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                    <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $currentUserTodayAttendance?->clock_out ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                                        <span>Pulang: <strong class="font-mono text-slate-900 dark:text-slate-100">{{ $currentUserTodayAttendance?->clock_out ? $currentUserTodayAttendance->clock_out->format('H:i') : '-' }}</strong></span>
                                    </div>
                                </div>

                                @if ($currentUserEmployee)
                                    <div class="text-[11px] text-slate-600 dark:text-slate-300 font-semibold pt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span>Off Day: <strong class="text-slate-900 dark:text-slate-100">{{ $currentUserEmployee->used_off_days_this_month }} / {{ $currentUserEmployee->monthly_off_days_quota }} Hari</strong></span>
                                        @if ($currentUserEmployee->is_off_days_over_quota)
                                            <span class="px-1.5 py-0.5 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded text-[9px] font-black uppercase">OVER {{ $currentUserEmployee->off_days_over_count }} HARI!</span>
                                        @endif
                                        <span>&bull;</span>
                                        <a href="/pos/commissions" class="text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-extrabold">
                                            <i class="ph-bold ph-percent text-xs"></i>
                                            <span>Komisi: <strong class="font-mono text-slate-900 dark:text-slate-100">Rp {{ number_format($currentUserMonthlyCommission, 0, ',', '.') }}</strong></span>
                                        </a>
                                    </div>

                                    <!-- Progress Bar Capaian Target Omset Sales -->
                                    <div class="pt-2 space-y-1">
                                        <div class="flex items-center justify-between text-[10px] font-extrabold text-slate-500 dark:text-slate-400">
                                            <span>Target Omset: <strong class="font-mono text-slate-800 dark:text-slate-200">Rp {{ number_format($currentUserMonthlySales, 0, ',', '.') }} / Rp {{ number_format($currentUserTargetSales, 0, ',', '.') }}</strong></span>
                                            <span class="px-1.5 py-0.2 bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-300 rounded-full font-black">{{ $currentUserProgressPercent }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-700/80 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $currentUserProgressPercent }}%;"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-shrink-0">
                                @if (!$currentUserTodayAttendance || !$currentUserTodayAttendance->clock_in)
                                    <a href="{{ route('pos.attendances') }}?action=clock_in" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 whitespace-nowrap">
                                        <i class="ph-bold ph-sign-in text-base"></i>
                                        <span>Clock In (Masuk)</span>
                                    </a>
                                @elseif (!$currentUserTodayAttendance->clock_out)
                                    <a href="{{ route('pos.attendances') }}?action=clock_out" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 whitespace-nowrap">
                                        <i class="ph-bold ph-sign-out text-base"></i>
                                        <span>Clock Out (Pulang)</span>
                                    </a>
                                @else
                                    <div class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center gap-2 whitespace-nowrap">
                                        <i class="ph-bold ph-check-circle text-emerald-500 text-base"></i>
                                        <span>Selesai</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Navigation Tiles (Collapsible / Hidden for Owner) -->
                @php
                    $isOwnerUser = auth()->check() && auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);
                @endphp
                <div x-data="{ openNav: {{ $isOwnerUser ? 'false' : 'true' }} }}" class="space-y-3">
                    @if ($isOwnerUser)
                        <div class="flex items-center justify-between">
                            <button @click="openNav = !openNav" type="button" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition flex items-center gap-1.5 cursor-pointer">
                                <i class="ph-bold" :class="openNav ? 'ph-caret-up' : 'ph-caret-down'"></i>
                                <span x-text="openNav ? 'Sembunyikan Navigasi Menu Staff' : 'Tampilkan Navigasi Menu Staff (Kasir, Sesi, Karyawan, Backoffice)'"></span>
                            </button>
                        </div>
                    @endif

                    <div x-show="openNav" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- POS Kasir -->
                        <a href="{{ route('pos') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-primary dark:hover:border-blue-500 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 transition-all flex flex-col items-center gap-3 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="ph-fill ph-shopping-cart text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">POS Kasir</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Buka halaman kasir</p>
                            </div>
                        </a>

                        <!-- Sesi Kasir -->
                        <a href="{{ route('pos.session') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-amber-500 dark:hover:border-amber-400 shadow-sm hover:shadow-lg hover:shadow-amber-500/10 transition-all flex flex-col items-center gap-3 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="ph-fill ph-clock-counter-clockwise text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Sesi Kasir</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kelola sesi kasir</p>
                            </div>
                        </a>

                        <!-- Performa Cabang -->
                        <a href="{{ route('pos.branch-performance') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 transition-all flex flex-col items-center gap-3 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="ph-fill ph-buildings text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Performa Cabang</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Stok & Laba Rugi</p>
                            </div>
                        </a>

                        <!-- Data Karyawan -->
                        <a href="{{ route('pos.employees') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-400 shadow-sm hover:shadow-lg hover:shadow-purple-500/10 transition-all flex flex-col items-center gap-3 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="ph-fill ph-users-three text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Data Karyawan</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kelola personel</p>
                            </div>
                        </a>

                        <!-- Backoffice -->
                        <a href="/backoffice" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 shadow-sm hover:shadow-lg hover:shadow-emerald-500/10 transition-all flex flex-col items-center gap-3 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="ph-fill ph-house text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Backoffice</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Panel admin</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Executive Owner Dashboard Widgets Section -->
                <livewire:owner-dashboard-widgets />

                <!-- Sales & Employee Performance Dashboard Widgets Section -->
                <livewire:sales-employee-dashboard-widgets />

                @if ($isOwnerUser)
                    <!-- Dashboard Section: Presensi Karyawan Cabang Hari Ini (Owner Only) -->
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
                        
                        <!-- Section Header -->
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-blue-950/50 text-primary dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-bold ph-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-black text-slate-900 dark:text-slate-100">
                                        Presensi Karyawan Cabang Hari Ini
                                    </h2>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        {{ $currentBranch ? $currentBranch->name : 'Semua Cabang' }} &bull; {{ now()->translatedFormat('l, d F Y') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Summary Badges -->
                            <div class="flex flex-wrap items-center gap-2 text-xs font-extrabold">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full">
                                    Total: {{ $totalStaff }} Staf
                                </span>
                                <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded-full">
                                    Hadir: {{ $hadirCount }}
                                </span>
                                <span class="px-3 py-1 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 rounded-full">
                                    Izin/Off: {{ $izinCount }}
                                </span>
                                <span class="px-3 py-1 bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 rounded-full">
                                    Belum Presensi: {{ $belumCount }}
                                </span>
                                <a href="{{ route('pos.attendances') }}" class="px-3.5 py-1.5 bg-primary hover:bg-primary-dark text-white rounded-xl shadow-sm transition inline-flex items-center gap-1.5 ml-2 cursor-pointer">
                                    <span>Kelola Presensi</span>
                                    <i class="ph-bold ph-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Employees Attendance Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-black tracking-wider border-b border-slate-100 dark:border-slate-700/60">
                                        <th class="px-6 py-3.5">Karyawan</th>
                                        <th class="px-6 py-3.5">Status Hari Ini</th>
                                        <th class="px-6 py-3.5">Jam Masuk / Pulang</th>
                                        <th class="px-6 py-3.5">Selfie & Radius GPS</th>
                                        <th class="px-6 py-3.5">Kuota Off-Day</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs font-semibold">
                                    @forelse ($branchEmployees as $emp)
                                        @php
                                            $att = $emp->attendances->first();
                                            $status = $att ? $att->status : 'belum';
                                            $usedOff = $emp->used_off_days_this_month;
                                            $quotaOff = $emp->monthly_off_days_quota;
                                            $isOver = $emp->is_off_days_over_quota;
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ $emp->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $emp->nik }} &bull; {{ $emp->user->roles->first()?->name ?? 'Kasir' }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($status === 'hadir')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                                                        <i class="ph-bold ph-check-circle text-xs"></i> Hadir
                                                    </span>
                                                @elseif ($status === 'belum')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300">
                                                        <i class="ph-bold ph-clock-afternoon text-xs"></i> Belum Presensi
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 uppercase">
                                                        {{ str_replace('_', ' ', $status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-300">
                                                @if ($att && $att->clock_in)
                                                    <div class="font-bold text-slate-800 dark:text-slate-100">In: {{ $att->clock_in->format('H:i') }}</div>
                                                    <div class="text-[10px] text-slate-400">Out: {{ $att->clock_out ? $att->clock_out->format('H:i') : '-' }}</div>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($att && $att->clock_in_photo_path)
                                                    <div class="flex items-center gap-2">
                                                        <img src="{{ Storage::url($att->clock_in_photo_path) }}" alt="Selfie" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                                        <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                                            {{ number_format($att->distance_meters ?? 0, 0) }}m Radius
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 text-[11px] font-normal">Tidak Ada Foto</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="{{ $isOver ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-600 dark:text-slate-300' }}">
                                                    {{ $usedOff }} / {{ $quotaOff }} Hari
                                                </span>
                                                @if ($isOver)
                                                    <span class="ml-1 text-[9px] bg-rose-500 text-white font-extrabold px-1.5 py-0.5 rounded-full uppercase">OVER!</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                                Belum ada staf karyawan terdaftar di cabang ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>
