@if ($currentPayroll)
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Staf Gaji</span>
            <div class="text-xl font-black text-slate-900 dark:text-slate-100 mt-1">{{ $currentPayroll->total_employees }} Karyawan</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Komisi & Bonus KPI</span>
            <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 ">
                Rp {{ number_format($currentPayroll->total_commissions + $currentPayroll->total_kpi_bonuses, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Potongan (Presensi+Lain)</span>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1 ">
                Rp {{ number_format($currentPayroll->total_deductions, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-4 text-white shadow-lg shadow-emerald-500/20">
            <span class="text-[11px] font-bold opacity-80 uppercase tracking-wider block">TOTAL GAJI BERSIH (NET)</span>
            <div class="text-xl font-black mt-1 ">
                Rp {{ number_format($currentPayroll->total_net_salary, 0, ',', '.') }}
            </div>
        </div>
    </div>
@endif
