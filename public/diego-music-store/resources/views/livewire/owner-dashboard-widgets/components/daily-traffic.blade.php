<!-- Grafik Monthly Report (Daily Visitor Low/Peak) -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition h-full flex flex-col justify-between">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-calendar-blank text-rose-600 dark:text-rose-400"></i> Monthly Traffic (Daily Low &amp; Peak)
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Pola jumlah pengunjung &amp; transaksi harian toko</p>
        </div>
        <div class="flex items-center gap-2 text-[10px]">
            <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 rounded font-bold">
                Peak: {{ $dailyTrafficChart['peak_day'] }} ({{ $dailyTrafficChart['peak_count'] }} Transaksi)
            </span>
            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 rounded font-bold">
                Low: {{ $dailyTrafficChart['low_day'] }} ({{ $dailyTrafficChart['low_count'] }} Transaksi)
            </span>
        </div>
    </div>
    <div class="h-56">
        <canvas id="dailyTrafficChartCanvas"></canvas>
    </div>
</div>
