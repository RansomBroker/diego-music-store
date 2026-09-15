<!-- KPI Card -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Pelunasan Piutang Diterima</span>
        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['total_settled'] ?? 0, 0, ',', '.') }}</div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['total_count'] ?? 0 }} Transaksi Pelunasan</span>
    </div>
</div>
