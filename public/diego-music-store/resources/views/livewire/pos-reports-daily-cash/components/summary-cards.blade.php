<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Kas Masuk (Inflow)</span>
        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['total_inflow'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Kas Keluar (Outflow)</span>
        <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_outflow'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Arus Kas Bersih (Net Cash)</span>
        <div class="text-xl font-black text-primary dark:text-blue-400 mt-1">Rp {{ number_format($reportData['net_cash_flow'] ?? 0, 0, ',', '.') }}</div>
    </div>
</div>
