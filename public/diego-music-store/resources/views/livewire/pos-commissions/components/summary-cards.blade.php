<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Penjualan Staf -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
            <i class="ph-bold ph-chart-line-up"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Penjualan Staf</div>
            <div class="text-lg font-black text-slate-900 dark:text-slate-100  mt-0.5">
                Rp {{ number_format($totalSalesPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Total Komisi Dihasilkan -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
            <i class="ph-bold ph-currency-dollar"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Komisi Periode Ini</div>
            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400  mt-0.5">
                Rp {{ number_format($totalCommissionPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Komisi Disetujui (Approved) -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
            <i class="ph-bold ph-check-circle"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Komisi Approved</div>
            <div class="text-lg font-black text-purple-600 dark:text-purple-400  mt-0.5">
                Rp {{ number_format($totalApprovedPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Komisi Menunggu (Pending) -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
            <i class="ph-bold ph-clock"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Menunggu Approval</div>
            <div class="text-lg font-black text-amber-600 dark:text-amber-400  mt-0.5">
                Rp {{ number_format($totalPendingPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
