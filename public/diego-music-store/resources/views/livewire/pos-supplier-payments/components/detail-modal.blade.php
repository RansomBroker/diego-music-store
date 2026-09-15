{{-- ===================== MODAL: DETAIL PELUNASAN HUTANG ===================== --}}
<x-pos.modal
    wire:model="showDetailModal"
    title="Detail Pelunasan Hutang"
    subtitle="Rincian pembayaran hutang pembelian yang sudah tersimpan"
    icon="ph-eye"
    maxWidth="4xl"
>
    @if ($detailPayment)
        <div class="space-y-6">
            <!-- Meta Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-slate-50 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200/50 dark:border-slate-800 transition-colors">
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">No. Pembayaran</span>
                    <span class="text-sm  font-bold text-slate-900 dark:text-white">{{ $detailPayment->payment_no }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Pembayaran</span>
                    <span class="text-sm font-semibold text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_date->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Supplier</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white">{{ $detailPayment->supplier->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status</span>
                    @if ($detailPayment->status === 'draft')
                        <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-350 text-xs font-bold rounded-full border border-amber-200/50 dark:border-amber-850/30">
                            Draft
                        </span>
                    @else
                        <span class="inline-flex px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30">
                            Posted
                        </span>
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Akun Kas / Bank</span>
                    <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->account->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Metode Pembayaran</span>
                    <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_method }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Referensi</span>
                    <span class="text-sm text-slate-850 dark:text-slate-250">{{ $detailPayment->payment_reference ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Journal No</span>
                    <span class="text-sm  text-slate-850 dark:text-slate-250">{{ $detailPayment->journal_no ?? '-' }}</span>
                </div>
                @if ($detailPayment->notes)
                    <div class="col-span-full border-t border-slate-200 dark:border-slate-800 pt-3 mt-1">
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Catatan</span>
                        <p class="text-sm text-slate-650 dark:text-slate-355 leading-relaxed">{{ $detailPayment->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items Details -->
            <div>
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Invoice yang Dilunasi</h4>
                <x-pos.table.container>
                    <x-pos.table>
                        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                            <tr>
                                <x-pos.table.th>No. Invoice / Transaksi</x-pos.table.th>
                                <x-pos.table.th>Tanggal</x-pos.table.th>
                                <x-pos.table.th class="text-right">Sisa Hutang Awal</x-pos.table.th>
                                <x-pos.table.th class="text-right">Jumlah Dibayar</x-pos.table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($detailPayment->items as $det)
                                <x-pos.table.tr>
                                    <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                        <div class=" font-bold">{{ $det->purchaseTransaction->transaction_no }}</div>
                                        @if ($det->purchaseTransaction->invoice_number)
                                            <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 block ">Inv: {{ $det->purchaseTransaction->invoice_number }}</span>
                                        @endif
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-650 dark:text-slate-350">
                                        {{ $det->purchaseTransaction->transaction_date->format('d/m/Y') }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right text-sm text-slate-650 dark:text-slate-350 ">
                                        Rp {{ number_format($det->amount_due, 0, ',', '.') }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right font-black text-primary dark:text-blue-400 text-sm ">
                                        Rp {{ number_format($det->amount_paid, 0, ',', '.') }}
                                    </x-pos.table.td>
                                </x-pos.table.tr>
                            @endforeach
                        </tbody>
                    </x-pos.table>
                    <x-pos.table.footer :total="count($detailPayment->items ?? [])" />
                </x-pos.table.container>

                <div class="mt-4 p-5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 flex justify-between items-center transition-colors">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Pelunasan</span>
                    <span class="text-2xl font-black text-primary dark:text-blue-400 ">Rp {{ number_format($detailPayment->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
                <x-pos.utility.button
                    type="button"
                    variant="primary"
                    wire:click="$set('showDetailModal', false)"
                >
                    Tutup
                </x-pos.utility.button>
            </div>
        </div>
    @endif
</x-pos.modal>
