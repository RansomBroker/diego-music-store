<!-- 5. Leaderboard Top 3 Sales -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-trophy text-amber-500 dark:text-amber-400"></i> Leaderboard Top 3 Sales Bulan Ini
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Ranking staf penjualan tertinggi di toko</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($leaderboard as $index => $rank)
            @php
                $rankColorClass = match($rank['rank']) {
                    1 => 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 border-amber-300 dark:border-amber-500/40',
                    2 => 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-200 border-slate-300 dark:border-slate-600',
                    3 => 'bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-300 border-orange-300 dark:border-orange-500/40',
                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 border-slate-200',
                };
                $badgePillClass = match($rank['rank']) {
                    1 => 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border-amber-500/30',
                    2 => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600',
                    3 => 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 border-orange-500/30',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            @endphp
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 shadow-sm hover:scale-[1.01] transition-all">
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Rank Medal Avatar -->
                    <div class="w-10 h-10 rounded-2xl {{ $rankColorClass }} border text-xl font-black flex items-center justify-center flex-shrink-0 shadow-sm">
                        {{ $rank['medal'] }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">{{ $rank['name'] }}</h4>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border tracking-wider flex-shrink-0 {{ $badgePillClass }}">
                                {{ $rank['badge_name'] ?? 'Top Sales' }}
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Rank #{{ $rank['rank'] }} Sales Staff</div>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    <div class="text-xs  font-black text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($rank['sales_val'], 0, ',', '.') }}
                    </div>
                    <div class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider">Total Omzet</div>
                </div>
            </div>
        @empty
            <div class="text-center text-xs text-slate-400 py-6">Belum ada data ranking transaksi bulan ini</div>
        @endforelse
    </div>
</div>
