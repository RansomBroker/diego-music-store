<!-- HEADER SECTION -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
    <div>
        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            Performa Sales & Indikator Target
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Monitoring pencapaian target harian, komisi, tier bonus, & absensi personal
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-black">
            {{ $monthlyTarget['progress_percent'] }}% Target Bulanan
        </span>
    </div>
</div>
