<!-- AR Aging Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm col-span-2 md:col-span-1">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Saldo Piutang</span>
        <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_outstanding'] ?? 0, 0, ',', '.') }}</div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['count_invoices'] ?? 0 }} Invoice Belum Lunas</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/40 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">0 - 30 Hari (Lancar)</span>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_0_30'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">31 - 60 Hari</span>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_31_60'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/40 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase">61 - 90 Hari</span>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_61_90'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/40 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase">> 90 Hari (Menunggak)</span>
        <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_over_90'] ?? 0, 0, ',', '.') }}</div>
    </div>
</div>
