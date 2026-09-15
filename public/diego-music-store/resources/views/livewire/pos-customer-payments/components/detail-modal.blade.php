{{-- ===================== MODAL: DETAIL & HISTORI PELUNASAN PIUTANG ===================== --}}
<x-pos.modal
    wire:model="showDetailModal"
    title="Detail & Histori Pembayaran Pelunasan"
    subtitle="Rincian transaksi invoice dan riwayat pembayaran cicilan/pelunasan"
    icon="ph-receipt"
    maxWidth="8xl"
>
    @if ($selectedSale)
        @php
            $piutangRemaining = $selectedSale->getPiutangAmount();
            $totalPaidCalculated = max(0, floatval($selectedSale->grand_total) - $piutangRemaining);
        @endphp
        <div class="space-y-6">
            <!-- Summary Meta Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">No. Invoice</span>
                    <span class="text-base  font-extrabold text-slate-900 dark:text-white">{{ $selectedSale->invoice_number }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</span>
                    <span class="text-base font-bold text-slate-900 dark:text-white">{{ $selectedSale->customer->name ?? 'Pelanggan Umum' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Transaksi</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $selectedSale->invoice_date->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status Pembayaran</span>
                    @if ($piutangRemaining <= 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-900/40">
                            Lunas (Completed)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full border border-amber-200 dark:border-amber-900/40">
                            Belum Lunas (Piutang)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Financial Calculation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Total Belanja Invoice</span>
                    <span class="text-lg  font-black text-slate-900 dark:text-white">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="bg-emerald-50/40 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 text-center">
                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider block mb-1">Total Sudah Dibayar</span>
                    <span class="text-lg  font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPaidCalculated, 0, ',', '.') }}</span>
                </div>
                <div class="bg-rose-50/40 dark:bg-rose-950/20 p-4 rounded-xl border border-rose-200/60 dark:border-rose-900/40 text-center">
                    <span class="text-xs text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider block mb-1">Sisa Piutang / Tagihan</span>
                    <span class="text-xl  font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($piutangRemaining, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Settlement History Table -->
            <div>
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Histori Riwayat Pembayaran / Cicilan</h4>
                <x-pos.table.container>
                    <x-pos.table>
                        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                            <tr>
                                <x-pos.table.th>No. Jurnal / Ref</x-pos.table.th>
                                <x-pos.table.th>Tanggal Pelunasan</x-pos.table.th>
                                <x-pos.table.th>Akun Penerima (Kas/Bank)</x-pos.table.th>
                                <x-pos.table.th>Keterangan</x-pos.table.th>
                                <x-pos.table.th>Kasir / Operator</x-pos.table.th>
                                <x-pos.table.th class="text-right">Nominal Dibayar</x-pos.table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse ($settlementHistory as $history)
                                <x-pos.table.tr>
                                    <x-pos.table.td class="whitespace-nowrap  font-bold text-xs text-slate-900 dark:text-slate-100">
                                        {{ $history['entry_number'] }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                        {{ $history['date'] }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-xs font-bold text-blue-600 dark:text-blue-400">
                                        {{ $history['account_name'] }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                        {{ $history['description'] }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                        {{ $history['user_name'] }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right  font-extrabold text-sm text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($history['amount'], 0, ',', '.') }}
                                    </x-pos.table.td>
                                </x-pos.table.tr>
                            @empty
                                <x-pos.table.empty colspan="6" icon="ph-receipt" message="Belum ada riwayat cicilan/pelunasan yang tercatat untuk invoice ini." />
                            @endforelse
                        </tbody>
                    </x-pos.table>
                    <x-pos.table.footer :total="count($settlementHistory ?? [])" />
                </x-pos.table.container>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="closeDetails"
                >
                    Tutup
                </x-pos.utility.button>
            </div>
        </div>
    @endif
</x-pos.modal>
