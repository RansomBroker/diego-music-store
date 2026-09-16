<div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs transition-colors">
    <div>
        <div class="text-[10px] uppercase font-bold text-emerald-800 dark:text-emerald-300 tracking-wider">
            Estimasi Gaji Bersih (Take Home Pay)
        </div>
        <div class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-400 tracking-tight">
            Rp {{ number_format($netSalary, 0, ',', '.') }}
        </div>
    </div>
    <div class="text-left sm:text-right text-xs text-slate-600 dark:text-slate-400 space-y-1">
        <div>
            Total Pendapatan: <span class="text-slate-900 dark:text-slate-100 font-bold">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
        </div>
        <div>
            Total Potongan: <span class="text-rose-600 dark:text-rose-400 font-bold">- Rp {{ number_format($totalDeductions, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
