<!-- ================= TRANSACTIONS DETAILS MODAL ================= -->
<x-pos.modal 
    wire:model="showTransactionsModal" 
    title="Detail Transaksi Sesi #{{ str_pad($selectedSessionId ?? '', 5, '0', STR_PAD_LEFT) }}"
    subtitle="Daftar transaksi penjualan yang tercatat pada sesi kasir ini."
    icon="ph-receipt"
    maxWidth="3xl"
>
    <div class="space-y-6 text-left">
        <!-- Summary Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Transaksi</span>
                <span class="text-lg font-black text-slate-850 dark:text-white">{{ $selectedSessionSummary['transaction_count'] ?? 0 }}</span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Omset</span>
                <span class="text-lg font-black text-slate-850 dark:text-white">Rp {{ number_format($selectedSessionSummary['total_sales'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tunai (Cash)</span>
                <span class="text-lg font-black text-emerald-600 dark:text-emerald-450">Rp {{ number_format($selectedSessionSummary['cash_total'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Non-Tunai</span>
                <span class="text-lg font-black text-blue-650 dark:text-blue-400">Rp {{ number_format($selectedSessionSummary['non_cash_total'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>No. Invoice</x-pos.table.th>
                        <x-pos.table.th>Waktu</x-pos.table.th>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>Pembayaran</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Belanja</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($selectedSessionTransactions as $sale)
                        <x-pos.table.tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition duration-100">
                            <x-pos.table.td class="whitespace-nowrap text-sm font-mono font-medium text-slate-900 dark:text-slate-100">
                                {{ $sale->invoice_number ?? '-' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-350">
                                {{ $sale->created_at->format('H:i:s') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ $sale->customer->name ?? 'Umum / Walk-in' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="whitespace-nowrap text-sm">
                                @if($sale->payment_method === 'cash')
                                    <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-250/20 dark:border-emerald-800/30">Tunai</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-350 text-xs font-bold rounded-full border border-blue-250/20 dark:border-blue-800/30">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class="whitespace-nowrap text-sm font-black text-right text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="5" icon="ph-receipt" message="Tidak ada transaksi tercatat pada sesi ini" />
                    @endforelse
                </tbody>
            </x-pos.table>
        </x-pos.table.container>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-slate-800">
            <button
                type="button"
                wire:click="$set('showTransactionsModal', false)"
                class="px-5 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</x-pos.modal>
