<div class="space-y-6">
    {{-- Ringkasan Metrik Penjualan --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Terjual (6 Bulan)</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                {{ number_format($salesSummary['total_qty']) }} <span class="text-sm font-normal text-gray-500">unit</span>
            </div>
        </div>
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Omzet Penjualan</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                Rp {{ number_format($salesSummary['total_amount'], 0, ',', '.') }}
            </div>
        </div>
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Transaksi Terakhir</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">
                {{ $salesSummary['last_sale_date'] ? \Carbon\Carbon::parse($salesSummary['last_sale_date'])->translatedFormat('d F Y') : 'Belum Ada Transaksi' }}
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Penjualan Terakhir --}}
    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="bg-gray-100 dark:bg-gray-800/80 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Riwayat Penjualan di {{ $branchName }}</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">No Invoice</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Pelanggan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentSales as $saleItem)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 font-mono font-medium text-gray-900 dark:text-gray-100">
                                {{ $saleItem->sale?->invoice_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $saleItem->sale?->invoice_date ? \Carbon\Carbon::parse($saleItem->sale->invoice_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">
                                {{ number_format($saleItem->quantity) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                Rp {{ number_format($saleItem->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-red-500">
                                {{ $saleItem->discount_amount > 0 ? 'Rp ' . number_format($saleItem->discount_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-gray-100">
                                Rp {{ number_format($saleItem->total_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $saleItem->sale?->customer?->name ?? 'Umum / Retail' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center space-y-1">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Tidak ada riwayat transaksi penjualan dalam 6 bulan terakhir.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
