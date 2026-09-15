<!-- ================= SHIFT HISTORY TABLE ================= -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <h3 class="text-base font-black text-slate-850 dark:text-white leading-tight">Riwayat Sesi Kasir Anda (10 Terakhir)</h3>
        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Total riwayat shift kasir aktif</span>
    </div>
    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>No Sesi</x-pos.table.th>
                    <x-pos.table.th>Cabang</x-pos.table.th>
                    <x-pos.table.th>Waktu Mulai</x-pos.table.th>
                    <x-pos.table.th>Waktu Selesai</x-pos.table.th>
                    <x-pos.table.th>Modal Awal</x-pos.table.th>
                    <x-pos.table.th>Ekspektasi</x-pos.table.th>
                    <x-pos.table.th>Kas Fisik</x-pos.table.th>
                    <x-pos.table.th>Selisih</x-pos.table.th>
                    <x-pos.table.th>Status</x-pos.table.th>
                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($history as $item)
                    <x-pos.table.tr wire:key="session-{{ $item->id }}" wire:click="showTransactions({{ $item->id }})" class="cursor-pointer">
                        <x-pos.table.td class="whitespace-nowrap text-sm  font-medium text-slate-900 dark:text-slate-100">#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->branch->name }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">{{ $item->opened_at->format('d/m H:i') }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">{{ $item->closed_at ? $item->closed_at->format('d/m H:i') : '-' }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($item->opening_cash, 0, ',', '.') }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">Rp {{ number_format($item->expected_cash, 0, ',', '.') }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->actual_cash !== null ? 'Rp ' . number_format($item->actual_cash, 0, ',', '.') : '-' }}</x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm font-semibold">
                            @if($item->difference === null)
                                -
                            @elseif($item->difference === 0)
                                <span class="text-emerald-600 dark:text-emerald-455 font-bold">Seimbang</span>
                            @elseif($item->difference < 0)
                                <span class="text-rose-600 dark:text-rose-455 font-bold">-Rp {{ number_format(abs($item->difference), 0, ',', '.') }}</span>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-455 font-bold">+Rp {{ number_format($item->difference, 0, ',', '.') }}</span>
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-sm">
                            @if($item->status === 'open')
                                <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30 animate-pulse">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-655 dark:text-slate-350 text-xs font-bold rounded-full border border-slate-200 dark:border-slate-700">Closed</span>
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($item->status === 'closed')
                                    <x-pos.utility.button
                                        type="button"
                                        variant="primary"
                                        size="xs"
                                        icon="ph-printer"
                                        onclick="window.open('{{ route('pos.z-report', $item->id) }}', '_blank', 'width=400,height=600,menubar=no,toolbar=no')"
                                        @click.stop
                                        title="Cetak Z-Report"
                                    />
                                    @if($loop->first)
                                        <x-pos.utility.button
                                            type="button"
                                            variant="warning"
                                            size="xs"
                                            icon="ph-key-return"
                                            wire:click.stop="requestReopenSession({{ $item->id }})"
                                            title="Buka Kembali Sesi"
                                        />
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="10" icon="ph-calendar" message="Belum ada riwayat sesi kasir" />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :total="count($history)" />
    </x-pos.table.container>
</div>
