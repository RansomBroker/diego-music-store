<!-- Table Card (Identik dengan Kolom Back Office StockListReport) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
        <span>LAPORAN DAFTAR STOK & NILAI PERSEDIAAN BARANG</span>
        <span class="text-xs text-slate-400 font-normal">Cabang: {{ $reportData['branch_name'] ?? 'Semua Cabang' }} &bull; Kategori: {{ $reportData['category'] ?? 'Semua Kategori' }}</span>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>SKU / Barcode</x-pos.table.th>
                    <x-pos.table.th>Nama Produk & Variasi</x-pos.table.th>
                    <x-pos.table.th>Kategori & Merk</x-pos.table.th>
                    <x-pos.table.th class="text-center">Stok Fisik</x-pos.table.th>
                    <x-pos.table.th class="text-center">Batas Min</x-pos.table.th>
                    <x-pos.table.th class="text-center">Satuan</x-pos.table.th>
                    <x-pos.table.th class="text-center">Status Stok</x-pos.table.th>
                    <x-pos.table.th class="text-center">Diskon</x-pos.table.th>
                    <x-pos.table.th class="text-center">PPN</x-pos.table.th>
                    <x-pos.table.th class="text-right">Harga Beli (HPP)</x-pos.table.th>
                    <x-pos.table.th class="text-right">Harga Jual</x-pos.table.th>
                    <x-pos.table.th class="text-right">Total Nilai HPP</x-pos.table.th>
                    <x-pos.table.th class="text-right">Total Nilai Jual</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($reportData['rows'] ?? [] as $row)
                    <x-pos.table.tr>
                        <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                            <div class="font-bold text-primary dark:text-blue-400">{{ $row['sku'] }}</div>
                            <div class="text-[10px] text-slate-400">BC: {{ $row['barcode'] }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">
                            {{ $row['full_name'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-200 block w-fit">{{ $row['category'] }}</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $row['brand'] }}</span>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center font-mono font-extrabold text-xs text-slate-900 dark:text-white">
                            {{ number_format($row['stock'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center font-mono text-xs text-slate-500">
                            {{ number_format($row['min_stock'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center text-xs text-slate-500 font-semibold">
                            {{ $row['unit'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            @if ($row['badge_color'] === 'danger')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif ($row['badge_color'] === 'warning')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif ($row['badge_color'] === 'info')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    {{ $row['status_label'] }}
                                </span>
                            @endif
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                            {{ $row['discount'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                            {{ $row['tax'] }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right font-mono text-xs text-rose-600 dark:text-rose-400 font-semibold">
                            Rp {{ number_format($row['cost_price'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right font-mono text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                            Rp {{ number_format($row['retail_price'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right font-mono font-extrabold text-xs text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($row['valuation'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right font-mono font-extrabold text-xs text-purple-600 dark:text-purple-400">
                            Rp {{ number_format($row['retail_value'], 0, ',', '.') }}
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="13" icon="ph-package" message="Belum ada data stok produk ditemukan." />
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>
</div>
