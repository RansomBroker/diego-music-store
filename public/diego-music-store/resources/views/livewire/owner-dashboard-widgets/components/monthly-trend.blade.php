<!-- Grafik Tren Penjualan Bulanan & Prediksi -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition h-full flex flex-col justify-between">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-trend-up text-blue-600 dark:text-blue-400"></i> Tren &amp; Prediksi Penjualan
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Historis tren &amp; proyeksi regresi linier bulan depan</p>
        </div>
        <div class="text-right">
            <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold">Proyeksi Bulan Depan</div>
            <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 ">
                Rp {{ number_format($monthlyTrendChart['predicted_next_val'], 0, ',', '.') }}
            </div>
        </div>
    </div>
    <div class="h-52">
        <canvas id="monthlyTrendChartCanvas"></canvas>
    </div>
</div>
