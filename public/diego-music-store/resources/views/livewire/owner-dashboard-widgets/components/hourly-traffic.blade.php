<!-- Grafik Waktu Pengunjung (Hourly Traffic Jam) -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition h-full flex flex-col justify-between">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-clock-afternoon text-amber-600 dark:text-amber-400"></i> Hourly Traffic Jam (Jam Sibuk)
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Kepadatan pengunjung per jam operasional</p>
        </div>
        <span class="px-2.5 py-0.5 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-black rounded-full border border-amber-300 dark:border-amber-500/30">
            Busy: {{ $hourlyTrafficChart['busy_hour'] }}
        </span>
    </div>
    <div class="h-56">
        <canvas id="hourlyTrafficChartCanvas"></canvas>
    </div>
</div>
