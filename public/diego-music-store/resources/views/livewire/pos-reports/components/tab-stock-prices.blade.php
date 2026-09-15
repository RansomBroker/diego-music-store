<!-- ========================================================================= -->
<!-- TAB 5: DAFTAR STOK DAN HARGA (STOCK & PRICE VALUATION)                   -->
<!-- ========================================================================= -->
<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total SKU / Varian</span>
            <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($reportData['total_sku'] ?? 0, 0, ',', '.') }} Item</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Total Fisik: {{ number_format($reportData['total_qty'] ?? 0, 0, ',', '.') }} pcs</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Nilai Persediaan (HPP)</span>
            <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1">Rp {{ number_format($reportData['total_hpp_valuation'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Asset Inventory Valuation</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total Potensi Omzet</span>
            <div class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1">Rp {{ number_format($reportData['total_retail_valuation'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Retail Sales Value</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Potensi Profit Kotor</span>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['potential_profit'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Potensi Margin Bersih</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
            Laporan Daftar Stok & Penilaian Harga Barang
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>SKU / Barcode</x-pos.table.th>
                        <x-pos.table.th>Nama Produk</x-pos.table.th>
                        <x-pos.table.th class="text-center">Stok (Pcs)</x-pos.table.th>
                        <x-pos.table.th class="text-right">Harga HPP</x-pos.table.th>
                        <x-pos.table.th class="text-right">Harga Jual</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Nilai HPP</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Nilai Jual</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($reportData['items'] ?? [] as $st)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                                <div class="font-bold text-primary dark:text-blue-400">{{ $st['sku'] }}</div>
                                <div class="text-[10px] text-slate-400">BC: {{ $st['barcode'] }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">
                                {{ $st['product_name'] }}
                                <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded ml-1">{{ $st['type'] }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-xs text-slate-900 dark:text-white">{{ number_format($st['stock'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($st['hpp'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($st['price'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-blue-600 dark:text-blue-400">Rp {{ number_format($st['total_hpp_valuation'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-purple-600 dark:text-purple-400">Rp {{ number_format($st['total_retail_valuation'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="7" icon="ph-package" message="Belum ada data stok produk ditemukan." />
                    @endforelse
                </tbody>
            </x-pos.table>
        </x-pos.table.container>
    </div>
</div>
