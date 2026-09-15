<!-- ========================================================================= -->
<!-- TAB 4: LAPORAN KAS HARIAN (DAILY CASH REPORT)                              -->
<!-- ========================================================================= -->
<div class="space-y-6">
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

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
            Mutasi BUKU KAS HARIAN POS
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>Waktu</x-pos.table.th>
                        <x-pos.table.th>Petugas</x-pos.table.th>
                        <x-pos.table.th>Kategori Transaksi</x-pos.table.th>
                        <x-pos.table.th class="text-center">Tipe</x-pos.table.th>
                        <x-pos.table.th class="text-right">Jumlah</x-pos.table.th>
                        <x-pos.table.th>Keterangan / Notes</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @php
                        $displayTx = $reportData['paginated_transactions'] ?? ($reportData['transactions'] ?? []);
                    @endphp
                    @forelse ($displayTx as $tx)
                        <x-pos.table.tr>
                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300 font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">{{ $tx->creator?->name ?? $tx->user?->name ?? '-' }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $tx->category }}</x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tx->type === 'inflow' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40' }}">
                                    {{ $tx->type }}
                                </span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs {{ $tx->type === 'inflow' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-500 italic">{{ $tx->notes ?: '-' }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="6" icon="ph-wallet" message="Belum ada mutasi kas harian pada periode ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :paginator="$reportData['paginated_transactions'] ?? null" :total="count($reportData['transactions'] ?? [])" perPageModel="perPage" />
        </x-pos.table.container>
    </div>
</div>
