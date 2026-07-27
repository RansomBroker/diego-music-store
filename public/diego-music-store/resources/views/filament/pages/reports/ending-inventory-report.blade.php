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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Cut-Off Tanggal</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}
            </div>
            <span class="text-xs text-gray-400">Periode Persediaan Akhir</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Variasi Produk</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_variants'], 0, ',', '.') }} SKU
            </div>
            <span class="text-xs text-gray-400">Total item terdaftar</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Qty Persediaan Akhir</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_ending_qty'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Kuantitas persediaan fisik</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Nilai Aset Persediaan</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_valuation']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Valuasi HPP Persediaan</span>
        </div>
    </div>

    {{-- Main Ending Inventory Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'summary_category' ? 'REKAPITULASI PERSEDIAAN AKHIR PER KATEGORI PRODUK' : 'RINCIAN DETAIL PERSEDIAAN AKHIR & VALUASI HPP' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Per Tanggal: <strong>{{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'summary_category')
                {{-- Mode Summary Category --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">Kategori Produk</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Jumlah SKU</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Total Qty Persediaan Akhir</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Nilai Aset (HPP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['categories'] as $cat)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-extrabold text-xs text-gray-900 dark:text-white whitespace-nowrap">{{ $cat['category_name'] }}</td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold whitespace-nowrap">{{ number_format($cat['variant_count'], 0, ',', '.') }} SKU</td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ number_format($cat['total_qty'], 0, ',', '.') }} Unit</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($cat['total_valuation']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400 text-xs">Tidak ada data persediaan akhir pada cut-off tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- Mode Detail Variant --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">SKU / Barcode</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Nama Produk & Variasi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Kategori</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Merk</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Persediaan Akhir</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Satuan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Harga Beli (HPP)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Nilai Aset (HPP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['rows'] as $row)
                            <tr 
                                wire:click="openVariantDetail({{ $row['id'] }})" 
                                class="hover:bg-primary-50/60 dark:hover:bg-primary-950/40 text-gray-700 dark:text-gray-300 cursor-pointer transition-colors"
                                title="Klik untuk melihat riwayat HPP & pergerakan stok">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $row['sku'] }}
                                    <div class="text-[10px] text-gray-400 font-normal">BC: {{ $row['barcode'] }}</div>
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap flex items-center gap-x-2">
                                    <span>{{ $row['full_name'] }}</span>
                                    <x-heroicon-o-information-circle class="w-4 h-4 text-primary-500 shrink-0" />
                                </td>
                                <td class="py-2.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $row['category'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $row['brand'] }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ number_format($row['ending_qty'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-center text-xs text-gray-500 whitespace-nowrap">
                                    {{ $row['unit'] }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['cost_price']) }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['valuation']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada data persediaan akhir yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>

    {{-- Native Filament Modal: Detail HPP & Riwayat Pergerakan Stok --}}
    @if($showDetailModal && $selectedVariantDetail)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/40">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-lg bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400">
                            <x-heroicon-o-chart-bar-square class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                {{ $selectedVariantDetail['full_name'] }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                SKU: <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $selectedVariantDetail['sku'] }}</span> &bull; Kategori: {{ $selectedVariantDetail['category'] }} &bull; Merk: {{ $selectedVariantDetail['brand'] }}
                            </p>
                        </div>
                    </div>

                    <button 
                        wire:click="closeVariantDetail" 
                        type="button" 
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    <!-- KPI Cards for Variant -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Stok Akhir</span>
                            <div class="text-lg font-bold font-mono text-gray-900 dark:text-white mt-0.5">
                                {{ number_format($selectedVariantDetail['total_stock'], 0, ',', '.') }} {{ $selectedVariantDetail['unit'] }}
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">HPP Terakhir / Unit</span>
                            <div class="text-lg font-bold font-mono text-primary-600 dark:text-primary-400 mt-0.5">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($selectedVariantDetail['current_hpp']) }}
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">Total Nilai Persediaan</span>
                            <div class="text-lg font-bold font-mono text-gray-900 dark:text-white mt-0.5">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($selectedVariantDetail['total_valuation']) }}
                            </div>
                        </div>
                    </div>

                    <!-- Audit Table HPP & Stock Movement -->
                    <div class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-xs">
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                            <h4 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wider flex items-center gap-x-2">
                                <x-heroicon-o-arrows-right-left class="w-4 h-4 text-primary-500" />
                                Audit Riwayat HPP dan Pergerakan Stok Waktu ke Waktu
                            </h4>
                            <span class="text-xs text-gray-500 font-mono">{{ count($hppHistory) }} Transaksi Recorded</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                                <thead class="bg-gray-100/70 dark:bg-gray-800/40 uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="py-2.5 px-3 whitespace-nowrap">Tanggal & Waktu</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap">Referensi Transaksi</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-center">Tipe</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-center">Qty Mutasi</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-right">Unit Cost Transaksi</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-center">Saldo Qty</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-right">Moving Avg HPP</th>
                                        <th class="py-2.5 px-3 whitespace-nowrap text-right">Total Valuasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    @forelse($hppHistory as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                            <td class="py-2.5 px-3 font-mono text-[11px] whitespace-nowrap text-gray-900 dark:text-white">
                                                {{ $item['date'] }}
                                            </td>
                                            <td class="py-2.5 px-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $item['reference_label'] }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                @if($item['type'] === 'Masuk')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300">
                                                        MASUK
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                        KELUAR
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-center font-mono font-bold whitespace-nowrap {{ $item['type'] === 'Masuk' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                {{ $item['qty_change'] }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['unit_cost']) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ number_format($item['running_qty'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['running_hpp']) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['total_valuation']) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-6 text-center text-gray-400 text-xs">
                                                Belum ada data pergerakan stok untuk varian ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex justify-end bg-gray-50/50 dark:bg-gray-800/40">
                    <button 
                        wire:click="closeVariantDetail" 
                        type="button" 
                        class="px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
