<!-- Table Card -->
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
                @forelse ($reportData['transactions'] ?? [] as $tx)
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
    </x-pos.table.container>
</div>
