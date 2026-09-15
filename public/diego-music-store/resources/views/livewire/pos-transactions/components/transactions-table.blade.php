<!-- Table List -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-500 uppercase tracking-wider">
            <tr>
                <x-pos.table.th>Tanggal</x-pos.table.th>
                <x-pos.table.th>No. Invoice</x-pos.table.th>
                <x-pos.table.th>Cabang</x-pos.table.th>
                <x-pos.table.th>Pelanggan</x-pos.table.th>
                <x-pos.table.th>Kasir</x-pos.table.th>
                <x-pos.table.th>Metode Bayar</x-pos.table.th>
                <x-pos.table.th class="text-right">Total</x-pos.table.th>
                <x-pos.table.th class="text-center">Status</x-pos.table.th>
                <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
            @forelse ($sales as $sale)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors cursor-pointer" wire:click="showDetails({{ $sale->id }})">
                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                        {{ $sale->invoice_date->format('d/m/Y') }}
                        <span class="block text-[10px] text-slate-400 mt-0.5">{{ $sale->created_at->format('H:i') }}</span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap font-black text-slate-900 dark:text-white">
                        {{ $sale->invoice_number }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $sale->branch->name ?? '-' }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $sale->customer->name ?? 'Umum' }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-xs">
                        {{ $sale->salesRep->name ?? '-' }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300">
                            @if (str_contains(strtolower($sale->payment_method), 'tunai') || strtolower($sale->payment_method) === 'cash')
                                <i class="ph ph-money text-emerald-500 text-sm"></i>
                            @elseif (str_contains(strtolower($sale->payment_method), 'debit'))
                                <i class="ph ph-credit-card text-blue-500 text-sm"></i>
                            @else
                                <i class="ph ph-file-text text-amber-500 text-sm"></i>
                            @endif
                            {{ $sale->payment_method }}
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-right font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($sale->status === 'completed')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40 rounded-full text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Selesai
                            </span>
                        @elseif ($sale->status === 'pending' || ((str_contains(strtolower($sale->payment_method), 'piutang') || str_contains(strtolower($sale->payment_method), 'credit')) && !str_contains(strtolower($sale->payment_method), 'lunas')))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 border border-amber-200/50 dark:border-amber-800/40 rounded-full text-xs font-bold text-amber-700 dark:text-amber-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Belum Selesai (Piutang)
                            </span>
                        @elseif ($sale->status === 'draft')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full text-xs font-bold text-slate-600 dark:text-slate-405">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Draft
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40 rounded-full text-xs font-bold text-rose-700 dark:text-rose-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Dibatalkan
                            </span>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-center" wire:click.stop>
                        <div class="flex items-center justify-center gap-1.5">
                            <x-pos.utility.button
                                type="button"
                                variant="info"
                                size="xs"
                                icon="ph-eye"
                                wire:click="showDetails({{ $sale->id }})"
                                title="Lihat Detail"
                            />
                            @if ($sale->status !== 'cancelled')
                                <x-pos.utility.button
                                    href="/pos?edit={{ $sale->id }}"
                                    variant="warning"
                                    size="xs"
                                    icon="ph-pencil"
                                    title="Edit Transaksi"
                                />
                            @endif
                            @if ($sale->status !== 'cancelled' && $sale->items->sum(fn($item) => $item->quantity - $item->returnItems()->sum('quantity')) > 0)
                                <x-pos.utility.button
                                    type="button"
                                    variant="danger"
                                    size="xs"
                                    icon="ph-arrow-counter-clockwise"
                                    wire:click="startReturn({{ $sale->id }})"
                                    title="Retur Barang"
                                />
                            @endif
                            <x-pos.utility.button
                                type="button"
                                variant="primary"
                                size="xs"
                                icon="ph-printer"
                                wire:click="printReceipt({{ $sale->id }})"
                                title="Cetak Struk"
                            />
                            @if ((str_contains(strtolower($sale->payment_method), 'piutang') || str_contains(strtolower($sale->payment_method), 'credit')) && !str_contains(strtolower($sale->payment_method), 'lunas'))
                                <x-pos.utility.button
                                    type="button"
                                    variant="success"
                                    size="xs"
                                    icon="ph-hand-coins"
                                    wire:click="openSettlementModal({{ $sale->id }})"
                                    title="Pelunasan Piutang"
                                >
                                    Pelunasan
                                </x-pos.utility.button>
                            @endif
                        </div>
                    </x-pos.table.td>
                </tr>
            @empty
                <x-pos.table.empty colspan="9" icon="ph-receipt" message="Tidak ada transaksi ditemukan untuk filter ini." />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$sales" perPageModel="perPage" />
</x-pos.table.container>
