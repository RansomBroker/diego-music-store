<!-- Sales Queue KPI Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
    {{-- Total Giliran Melayani --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
            <i class="ph-bold ph-handshake text-2xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Giliran</p>
            <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $summaryStats['total_servings'] }}x</p>
            <span class="text-[10px] text-emerald-500 font-bold">{{ $summaryStats['active_on_duty_count'] }} aktif melayani</span>
        </div>
    </div>

    {{-- Total Closing --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
            <i class="ph-bold ph-check-circle text-2xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Closing Sukses</p>
            <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $summaryStats['total_closings'] }} Nota</p>
            <span class="text-[10px] text-slate-500 font-semibold">{{ $summaryStats['conversion_rate'] }}% Konversi</span>
        </div>
    </div>

    {{-- Total Omset Closing --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
            <i class="ph-bold ph-money text-2xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omset Closing</p>
            <p class="text-lg sm:text-xl font-black text-slate-900 dark:text-white mt-0.5 truncate">
                Rp {{ number_format($summaryStats['total_revenue'], 0, ',', '.') }}
            </p>
            <span class="text-[10px] text-slate-500 font-semibold">Total omset sales</span>
        </div>
    </div>

    {{-- Rata-rata Durasi Melayani --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
            <i class="ph-bold ph-clock text-2xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Durasi</p>
            <p class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ $summaryStats['avg_duration_minutes'] }} Menit</p>
            <span class="text-[10px] text-slate-500 font-semibold">per sesi melayani</span>
        </div>
    </div>
</div>
