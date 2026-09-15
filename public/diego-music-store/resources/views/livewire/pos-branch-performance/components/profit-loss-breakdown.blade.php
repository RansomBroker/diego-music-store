<!-- Breakdown Laba Rugi Multi-Step Cabang -->
<div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
    <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2 mb-4">
        <i class="ph-bold ph-receipt text-blue-500"></i>
        Rincian Laba Rugi Multi-Step Cabang Aktif
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">1. Pendapatan Penjualan</div>
            <div class="text-lg font-black text-slate-900 dark:text-slate-100 mt-1">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">2. Harga Pokok Penjualan (HPP)</div>
            <div class="text-lg font-black text-rose-600 dark:text-rose-400 mt-1">
                (Rp {{ number_format($totalCogs, 0, ',', '.') }})
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">3. Beban Operasional Kas Keluar</div>
            <div class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1">
                (Rp {{ number_format($totalOperationalExpenses, 0, ',', '.') }})
            </div>
        </div>
    </div>
</div>
