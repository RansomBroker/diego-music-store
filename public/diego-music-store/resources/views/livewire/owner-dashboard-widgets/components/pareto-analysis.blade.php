<!-- Grafik Pareto 80/20 -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition h-full flex flex-col justify-between">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-sparkle text-amber-500 dark:text-amber-400"></i> Analisis Pareto 80/20
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Produk &amp; Pelanggan penyumbang 80% omzet terbesar</p>
        </div>
        <span class="px-2.5 py-0.5 bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-black rounded-full border border-amber-500/30">
            Ratio: {{ $paretoChart['products']['pareto_ratio'] ?? 0 }}% Top Items
        </span>
    </div>

    <!-- Pareto Summary Table -->
    <div class="overflow-x-auto max-h-56 no-scrollbar">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-200/70 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 uppercase text-[9px] font-bold">
                <tr>
                    <th class="px-3 py-2">Item / Pelanggan</th>
                    <th class="px-3 py-2 text-right">Omzet</th>
                    <th class="px-3 py-2 text-center">Pareto Class</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                @forelse (array_slice($paretoChart['products']['items'], 0, 6) as $p)
                    <tr class="hover:bg-slate-100 dark:hover:bg-slate-700/30 transition">
                        <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-200 truncate max-w-[150px]">{{ $p['name'] }}</td>
                        <td class="px-3 py-2 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($p['value'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold {{ $p['is_top_80'] ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                {{ $p['pareto_class'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-3 py-4 text-center text-slate-400">Belum ada data transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
