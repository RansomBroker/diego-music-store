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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Transaksi Mutasi</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_transactions'], 0, ',', '.') }} Record
            </div>
            <span class="text-xs text-gray-400">Total catatan pergerakan stok</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Total Qty Masuk (+)</span>
            <div class="text-xl font-extrabold font-mono text-emerald-700 dark:text-emerald-300 mt-1">
                +{{ number_format($data['total_in_qty'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Pembelian / Inflow / Opname (+)</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">Total Qty Keluar (-)</span>
            <div class="text-xl font-extrabold font-mono text-rose-700 dark:text-rose-300 mt-1">
                -{{ number_format($data['total_out_qty'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Penjualan / Outflow / Opname (-)</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Valuasi Mutasi</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_valuation']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">
                Net Qty: <strong>{{ $data['total_net_qty'] > 0 ? '+' : '' }}{{ number_format($data['total_net_qty'], 0, ',', '.') }} Unit</strong>
            </span>
        </div>
    </div>

    {{-- Main Stock Movement Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'summary' ? 'REKAPITULASI MUTASI BARANG PER PRODUK' : 'KARTU STOK BERJALAN (STOCK MOVEMENT LOG)' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'summary')
                {{-- Mode Summary --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">SKU</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Nama Produk & Variasi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Kategori</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Masuk (+)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Keluar (-)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Net Mutasi Qty</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Satuan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Harga Beli (HPP)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Valuasi Mutasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['summary_rows'] as $row)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">{{ $row['sku'] }}</td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $row['full_name'] }}</td>
                                <td class="py-2.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $row['category'] }}</td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">+{{ number_format($row['in_qty'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">-{{ number_format($row['out_qty'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold whitespace-nowrap {{ $row['net_qty'] < 0 ? 'text-rose-600 dark:text-rose-400' : ($row['net_qty'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white') }}">
                                    {{ $row['net_qty'] > 0 ? '+' : '' }}{{ number_format($row['net_qty'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-center text-xs text-gray-500 whitespace-nowrap">{{ $row['unit'] }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($row['hpp']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($row['total_value']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-gray-400 text-xs">Tidak ada data mutasi barang pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- Mode Movement Log --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal & Waktu</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Referensi / Transaksi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">SKU</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Nama Produk & Variasi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Cabang</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Tipe</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Mutasi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Satuan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Harga Beli (HPP)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Valuasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['rows'] as $row)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $row['date'] }}</td>
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">{{ $row['ref_label'] }}</td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $row['sku'] }}</td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $row['full_name'] }}</td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $row['branch_name'] }}</td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge :color="$row['badge_color']" class="whitespace-nowrap inline-flex">
                                        {{ $row['type'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold whitespace-nowrap {{ $row['type'] === 'IN' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $row['type'] === 'IN' ? '+' : '-' }}{{ number_format($row['quantity'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-center text-xs text-gray-500 whitespace-nowrap">{{ $row['unit'] }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($row['hpp']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($row['total_value']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-6 text-center text-gray-400 text-xs">Tidak ada transaksi mutasi barang pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
