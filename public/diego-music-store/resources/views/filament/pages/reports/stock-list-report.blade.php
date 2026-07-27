<x-filament-panels::page>
    @php
        $data = $this->report_data;
    @endphp

    {{-- Filter Form (Native Filament Section) --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- KPI Summary Cards (Monochrome High Contrast) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Variasi Produk</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_variants'], 0, ',', '.') }} SKU
            </div>
            <span class="text-xs text-gray-400">Total item terdaftar</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Qty Stok Fisik</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_physical_qty'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Kuantitas fisik gudang</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Peringatan Stok</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_low_stock_count'], 0, ',', '.') }} <span class="text-xs font-normal text-amber-600 dark:text-amber-400">Rendah</span> / {{ number_format($data['total_out_of_stock_count'], 0, ',', '.') }} <span class="text-xs font-normal text-rose-600 dark:text-rose-400">Habis</span>
            </div>
            <span class="text-xs text-gray-400">Memerlukan Restok / PO</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Nilai Aset Stok</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_valuation']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Valuasi HPP Persediaan</span>
        </div>
    </div>

    {{-- Main Stock List Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    DAFTAR STOK BARANG & VALUASI PERSEDIAAN
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Cabang: <strong>{{ $data['branch_name'] }}</strong> &bull; Kategori: <strong>{{ $data['category'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                        <th class="py-2.5 px-4 whitespace-nowrap">SKU / Barcode</th>
                        <th class="py-2.5 px-4 whitespace-nowrap">Nama Produk & Variasi</th>
                        <th class="py-2.5 px-4 whitespace-nowrap">Kategori</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">Stok</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">Min</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">Satuan</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">Status</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">Diskon</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-center">PPN</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-right">Harga Beli (HPP)</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-right">Harga Jual</th>
                        <th class="py-2.5 px-4 whitespace-nowrap text-right">Nilai Aset (HPP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($data['rows'] as $row)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                            <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $row['sku'] }}
                                <div class="text-[10px] text-gray-400 font-normal">BC: {{ $row['barcode'] }}</div>
                            </td>
                            <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $row['full_name'] }}
                                @if($row['brand'] !== '-')
                                    <span class="text-[10px] font-normal text-gray-500 block">Brand: {{ $row['brand'] }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $row['category'] }}
                            </td>
                            <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ number_format($row['stock'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-center font-mono text-xs text-gray-500 whitespace-nowrap">
                                {{ number_format($row['min_stock'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-center text-xs text-gray-500 whitespace-nowrap">
                                {{ $row['unit'] }}
                            </td>
                            <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                <x-filament::badge :color="$row['badge_color']" class="whitespace-nowrap inline-flex">
                                    {{ $row['status_label'] }}
                                </x-filament::badge>
                            </td>
                            <td class="py-2.5 px-4 text-center font-mono text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $row['discount'] }}
                            </td>
                            <td class="py-2.5 px-4 text-center font-mono text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ $row['tax'] }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['cost_price']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-900 dark:text-white whitespace-nowrap">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['retail_price']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['valuation']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-6 text-center text-gray-400 text-xs">
                                Tidak ada data stok barang yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
