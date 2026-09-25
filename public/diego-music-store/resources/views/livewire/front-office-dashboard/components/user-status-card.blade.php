<!-- Card Status User Login & Presensi -->
<div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/70 dark:border-slate-700/80 transition-colors">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        
        <!-- Left (Cols 7): User Identity & Greeting -->
        <div class="lg:col-span-7 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 dark:bg-slate-700 border border-primary/20 dark:border-slate-600 overflow-hidden flex-shrink-0 shadow-sm flex items-center justify-center text-primary dark:text-blue-400 font-black text-xl">
                @if (auth()->user()?->avatar_full_url)
                    <img src="{{ auth()->user()->avatar_full_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                @endif
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
                        <span>Masuk: <strong class=" text-slate-900 dark:text-slate-100">{{ $currentUserTodayAttendance?->clock_in ? $currentUserTodayAttendance->clock_in->format('H:i') : '-' }}</strong></span>
                    </div>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300">
                        <span class="w-2.5 h-2.5 rounded-full {{ $currentUserTodayAttendance?->clock_out ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span>Pulang: <strong class=" text-slate-900 dark:text-slate-100">{{ $currentUserTodayAttendance?->clock_out ? $currentUserTodayAttendance->clock_out->format('H:i') : '-' }}</strong></span>
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
                            <span>Komisi: <strong class=" text-slate-900 dark:text-slate-100">Rp {{ number_format($currentUserMonthlyCommission, 0, ',', '.') }}</strong></span>
                        </a>
                    </div>

                    <!-- Progress Bar Capaian Target Omset Sales -->
                    <div class="pt-2 space-y-1">
                        <div class="flex items-center justify-between text-[10px] font-extrabold text-slate-500 dark:text-slate-400">
                            <span>Target Omset: <strong class=" text-slate-800 dark:text-slate-200">Rp {{ number_format($currentUserMonthlySales, 0, ',', '.') }} / Rp {{ number_format($currentUserTargetSales, 0, ',', '.') }}</strong></span>
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
