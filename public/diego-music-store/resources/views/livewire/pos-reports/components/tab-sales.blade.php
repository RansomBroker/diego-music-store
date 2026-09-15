<!-- ========================================================================= -->
<!-- TAB 1: LAPORAN PENJUALAN (SALES REPORT)                                    -->
<!-- ========================================================================= -->
<div class="space-y-6">
    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total Omzet Penjualan</span>
            <div class="text-xl font-black text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['grand_total'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['total_transactions'] ?? 0 }} Transaksi Selesai</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total HPP Barang</span>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_cogs'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Harga Pokok Penjualan</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Laba Kotor (Gross Profit)</span>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['gross_profit'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Margin: {{ $reportData['profit_margin'] ?? 0 }}%</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Diskon & Pajak</span>
            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1">Disc: Rp {{ number_format($reportData['total_discount'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">Pajak: Rp {{ number_format($reportData['total_tax'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Sales Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
            <span>Rincian Transaksi Penjualan</span>
            <span class="text-xs text-slate-400 font-normal">Menampilkan {{ count($reportData['sales'] ?? []) }} baris</span>
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>No Invoice</x-pos.table.th>
                        <x-pos.table.th>Tanggal</x-pos.table.th>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>Kategori</x-pos.table.th>
                        <x-pos.table.th>Metode Bayar</x-pos.table.th>
                        <x-pos.table.th class="text-right">Grand Total</x-pos.table.th>
                        <x-pos.table.th class="text-right">Est. HPP</x-pos.table.th>
                        <x-pos.table.th class="text-right">Laba Kotor</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @php
                        $displaySales = $reportData['paginated_sales'] ?? ($reportData['sales'] ?? []);
                    @endphp
                    @forelse ($displaySales as $sale)
                        @php
                            $saleCOGS = 0;
                            foreach ($sale->items as $item) {
                                $hpp = $item->variant ? ($item->variant->hpp ?: $item->variant->cost_price ?: 0) : 0;
                                $saleCOGS += ($hpp * $item->quantity);
                            }
                            $profit = $sale->grand_total - $saleCOGS;
                        @endphp
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-xs text-primary dark:text-blue-400 font-mono">{{ $sale->invoice_number }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $sale->invoice_date->format('d/m/Y') }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs font-semibold text-slate-900 dark:text-white">{{ $sale->customer->name ?? 'Walk-in / Umum' }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs"><span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded font-bold">{{ $sale->sale_category ?: 'Store' }}</span></x-pos.table.td>
                            <x-pos.table.td class="text-xs uppercase font-bold text-slate-600 dark:text-slate-300">{{ $sale->payment_method }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-slate-900 dark:text-white">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($saleCOGS, 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($profit, 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="8" icon="ph-receipt" message="Belum ada data transaksi penjualan pada periode ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :paginator="$reportData['paginated_sales'] ?? null" :total="count($reportData['sales'] ?? [])" perPageModel="perPage" />
        </x-pos.table.container>
    </div>
</div>
