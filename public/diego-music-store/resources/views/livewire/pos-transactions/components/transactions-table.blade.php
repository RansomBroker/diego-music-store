<!-- Table List -->
<div class="w-full overflow-x-auto no-scrollbar">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider transition-colors">
                <th class="px-6 py-4">Tanggal</th>
                <th class="px-6 py-4">No. Invoice</th>
                <th class="px-6 py-4">Cabang</th>
                <th class="px-6 py-4">Pelanggan</th>
                <th class="px-6 py-4">Kasir</th>
                <th class="px-6 py-4">Metode Bayar</th>
                <th class="px-6 py-4 text-right">Total</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
            @forelse ($sales as $sale)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/30 transition-colors cursor-pointer" wire:click="showDetails({{ $sale->id }})">
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                        {{ $sale->invoice_date->format('d/m/Y') }}
                        <span class="block text-[10px] text-slate-400 mt-0.5">{{ $sale->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-black text-slate-900 dark:text-white">
                        {{ $sale->invoice_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $sale->branch->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $sale->customer->name ?? 'Umum' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                        {{ $sale->salesRep->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-900 dark:text-white">
                        Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center" wire:click.stop>
                        <div class="flex items-center justify-center gap-2">
                            <button
                                type="button"
                                wire:click="showDetails({{ $sale->id }})"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-primary-light dark:bg-slate-800 dark:hover:bg-blue-950/50 text-slate-500 hover:text-primary dark:hover:text-blue-400 flex items-center justify-center transition-colors"
                                title="Lihat Detail"
                            >
                                <i class="ph-bold ph-eye text-base"></i>
                            </button>
                            @if ($sale->status !== 'cancelled')
                                <a
                                    href="/pos?edit={{ $sale->id }}"
                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-amber-50 dark:bg-slate-800 dark:hover:bg-amber-900/40 text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 flex items-center justify-center transition-colors"
                                    title="Edit Transaksi"
                                >
                                    <i class="ph-bold ph-pencil text-base"></i>
                                </a>
                            @endif
                            @if ($sale->status !== 'cancelled' && $sale->items->sum(fn($item) => $item->quantity - $item->returnItems()->sum('quantity')) > 0)
                                <button
                                    type="button"
                                    wire:click="startReturn({{ $sale->id }})"
                                    class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 text-slate-500 hover:text-rose-600 dark:hover:text-rose-450 flex items-center justify-center transition-colors"
                                    title="Retur Barang"
                                >
                                    <i class="ph-bold ph-arrow-counter-clockwise text-base"></i>
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="printReceipt({{ $sale->id }})"
                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-emerald-50 dark:bg-slate-800 dark:hover:bg-emerald-950/40 text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center justify-center transition-colors"
                                title="Cetak Struk"
                            >
                                <i class="ph-bold ph-printer text-base"></i>
                            </button>
                            @if ((str_contains(strtolower($sale->payment_method), 'piutang') || str_contains(strtolower($sale->payment_method), 'credit')) && !str_contains(strtolower($sale->payment_method), 'lunas'))
                                <button
                                    type="button"
                                    wire:click="openSettlementModal({{ $sale->id }})"
                                    class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex items-center gap-1 transition-colors shadow-sm cursor-pointer ml-1"
                                    title="Pelunasan Piutang"
                                >
                                    <i class="ph-bold ph-hand-coins text-sm"></i>
                                    <span>Pelunasan</span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium">
                        <i class="ph ph-receipt text-4xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                        Tidak ada transaksi ditemukan untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if ($sales->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
        {{ $sales->links() }}
    </div>
@endif
