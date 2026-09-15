<!-- KPI Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Total Tiket Service</span>
        <div class="text-xl font-black text-slate-900 dark:text-white mt-1 font-mono">
            {{ number_format($statusCounts['all'] ?? 0, 0, ',', '.') }} Order
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">Terdaftar di sistem</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Baru Diterima</span>
        <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
            {{ number_format($statusCounts['received'] ?? 0, 0, ',', '.') }} Unit
        </div>
        <span class="text-[11px] text-slate-500 font-medium mt-1 block">Menunggu pemeriksaan</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Dalam Pengerjaan</span>
        <div class="text-xl font-black text-amber-500 dark:text-amber-400 mt-1 font-mono">
            {{ number_format($statusCounts['in_progress'] ?? 0, 0, ',', '.') }} Unit
        </div>
        <span class="text-[11px] text-amber-500 font-bold mt-1 block">Diagnosa / Service / Parts</span>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase">Selesai / Siap Diambil</span>
        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
            {{ number_format($statusCounts['completed'] ?? 0, 0, ',', '.') }} Unit
        </div>
        <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Siap diambil pelanggan</span>
    </div>
</div>
