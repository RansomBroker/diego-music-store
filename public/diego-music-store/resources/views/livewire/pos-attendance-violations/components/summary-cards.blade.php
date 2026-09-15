<!-- Summary KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Total Keterlambatan -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xl flex-shrink-0">
            <i class="ph-bold ph-clock"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Keterlambatan</div>
            <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                {{ number_format($totalLateMinutesPeriod) }} <span class="text-xs font-normal text-slate-400">Menit</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Pulang Cepat -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-xl flex-shrink-0">
            <i class="ph-bold ph-sign-out"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pulang Cepat</div>
            <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                {{ number_format($totalEarlyMinutesPeriod) }} <span class="text-xs font-normal text-slate-400">Menit</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Denda Presensi -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-xl flex-shrink-0">
            <i class="ph-bold ph-currency-dollar"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Nominal Denda</div>
            <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                Rp {{ number_format($totalDeductionPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Card 4: Denda Disetujui (Payroll Ready) -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xl flex-shrink-0">
            <i class="ph-bold ph-check-circle"></i>
        </div>
        <div>
            <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Disetujui ke Payroll</div>
            <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                Rp {{ number_format($totalApprovedDeductionPeriod, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
