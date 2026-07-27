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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Sesi Stok Opname</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_opname_sessions'], 0, ',', '.') }} Sesi
            </div>
            <span class="text-xs text-gray-400">Total Audit Periode Ini</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Item Di-Audit</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_items_audited'], 0, ',', '.') }} Item
            </div>
            <span class="text-xs text-gray-400">Kuantitas Barang Terperiksa</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Net Selisih Qty</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ $data['total_net_variance_qty'] > 0 ? '+' : '' }}{{ number_format($data['total_net_variance_qty'], 0, ',', '.') }} Unit
            </div>
            <span class="text-xs text-gray-400">Physical vs System Variance</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Stock Adjustment</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_adjustment_value']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Valuasi Penyesuaian HPP</span>
        </div>
    </div>

    {{-- Main Stock Opname Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'detail' ? 'RINCIAN DETAIL BARANG DI-OPNAME (VARIANCE AUDIT)' : 'RINGKASAN SESI STOK OPNAME' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'detail')
                {{-- Mode Detail Items --}}
                <div class="p-6 space-y-6">
                    @forelse($data['opnames'] as $op)
                        <div class="border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                            <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/5 flex flex-col sm:flex-row justify-between sm:items-center gap-2 text-xs border-b border-gray-300 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-extrabold text-sm text-gray-900 dark:text-white">{{ $op['opname_number'] }}</span>
                                    <span class="text-gray-500 font-mono">Tgl: {{ \Illuminate\Support\Carbon::parse($op['opname_date'])->format('d/m/Y') }}</span>
                                    <x-filament::badge :color="$op['status_badge_color']">
                                        {{ $op['status'] }}
                                    </x-filament::badge>
                                </div>
                                <div class="flex items-center gap-4 text-gray-700 dark:text-gray-300">
                                    <span>Cabang: <strong>{{ $op['branch_name'] }}</strong></span>
                                    <span>Item Di-audit: <strong>{{ number_format($op['items_count'], 0, ',', '.') }} SKU</strong></span>
                                    <span>Adjustment: <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($op['session_adjustment_value']) }}</strong></span>
                                </div>
                            </div>

                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-[11px] uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                        <th class="py-2 px-6 w-32">SKU</th>
                                        <th class="py-2 px-6">Nama Produk & Variasi</th>
                                        <th class="py-2 px-6 text-center w-24">Qty Sistem</th>
                                        <th class="py-2 px-6 text-center w-24">Qty Fisik</th>
                                        <th class="py-2 px-6 text-center w-24">Selisih</th>
                                        <th class="py-2 px-6 text-center w-36">Status Audit</th>
                                        <th class="py-2 px-6 text-right w-32">Harga Beli/HPP</th>
                                        <th class="py-2 px-6 text-right w-36">Nilai Adjustment</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    @foreach($op['items'] as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                            <td class="py-2 px-6 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                            <td class="py-2 px-6 font-semibold text-gray-900 dark:text-white">{{ $item['full_name'] }}</td>
                                            <td class="py-2 px-6 text-center font-mono text-gray-500">{{ number_format($item['system_qty'], 0, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td class="py-2 px-6 text-center font-mono font-bold text-gray-900 dark:text-white">{{ number_format($item['physical_qty'], 0, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td class="py-2 px-6 text-center font-mono font-extrabold {{ $item['difference'] < 0 ? 'text-rose-600 dark:text-rose-400' : ($item['difference'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white') }}">
                                                {{ $item['difference'] > 0 ? '+' : '' }}{{ number_format($item['difference'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-2 px-6 text-center whitespace-nowrap">
                                                <x-filament::badge :color="$item['item_badge_color']" class="whitespace-nowrap inline-flex">
                                                    {{ $item['item_status_label'] }}
                                                </x-filament::badge>
                                            </td>
                                            <td class="py-2 px-6 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['cost_price']) }}</td>
                                            <td class="py-2 px-6 text-right font-mono font-extrabold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['adjustment_value']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 text-xs italic">
                            Tidak ada data audit stok opname yang sesuai dengan filter.
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Mode Summary Opname --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Opname</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Cabang</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Status</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Total Item</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Sistem</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Qty Fisik</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Selisih Qty</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Nilai Adjustments</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Catatan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['opnames'] as $op)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $op['opname_number'] }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($op['opname_date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $op['branch_name'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge :color="$op['status_badge_color']" class="whitespace-nowrap inline-flex">
                                        {{ $op['status'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold whitespace-nowrap">
                                    {{ number_format($op['items_count'], 0, ',', '.') }} SKU
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ number_format($op['session_system_qty'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ number_format($op['session_physical_qty'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-extrabold whitespace-nowrap {{ $op['session_diff_qty'] < 0 ? 'text-rose-600 dark:text-rose-400' : ($op['session_diff_qty'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white') }}">
                                    {{ $op['session_diff_qty'] > 0 ? '+' : '' }}{{ number_format($op['session_diff_qty'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($op['session_adjustment_value']) }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-gray-500 truncate max-w-xs">
                                    {{ $op['notes'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::button size="xs" color="gray" wire:click="openOpnameDetailModal({{ $op['id'] }})">
                                        Lihat Detail
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada data stok opname yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>

    {{-- Modal Popup Detail Stok Opname --}}
    <x-filament::modal id="opname-detail-modal" width="5xl">
        <x-slot name="heading">
            Rincian Audit Sesi Stok Opname: {{ $selectedOpnameDetail['opname_number'] ?? '' }}
        </x-slot>

        @if($selectedOpnameDetail)
            <div class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-gray-50 dark:bg-white/5 p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div>
                        <span class="text-gray-500 block">Tanggal Audit:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ $selectedOpnameDetail['opname_date'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Cabang:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $selectedOpnameDetail['branch_name'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Status:</span>
                        <x-filament::badge :color="$selectedOpnameDetail['status_badge_color']" class="mt-0.5">
                            {{ $selectedOpnameDetail['status'] }}
                        </x-filament::badge>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Catatan:</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $selectedOpnameDetail['notes'] }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                <th class="py-2 px-4">SKU</th>
                                <th class="py-2 px-4">Nama Produk & Variasi</th>
                                <th class="py-2 px-4 text-center">Sistem</th>
                                <th class="py-2 px-4 text-center">Fisik</th>
                                <th class="py-2 px-4 text-center">Selisih</th>
                                <th class="py-2 px-4 text-center">Status Audit</th>
                                <th class="py-2 px-4 text-right">Harga Beli/HPP</th>
                                <th class="py-2 px-4 text-right">Nilai Adjustment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($selectedOpnameDetail['items'] as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="py-2 px-4 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                    <td class="py-2 px-4 font-semibold text-gray-900 dark:text-white">{{ $item['full_name'] }}</td>
                                    <td class="py-2 px-4 text-center font-mono text-gray-500">{{ number_format($item['system_qty'], 0, ',', '.') }} {{ $item['unit'] }}</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold text-gray-900 dark:text-white">{{ number_format($item['physical_qty'], 0, ',', '.') }} {{ $item['unit'] }}</td>
                                    <td class="py-2 px-4 text-center font-mono font-extrabold {{ $item['difference'] < 0 ? 'text-rose-600 dark:text-rose-400' : ($item['difference'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white') }}">
                                        {{ $item['difference'] > 0 ? '+' : '' }}{{ number_format($item['difference'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-4 text-center">
                                        <x-filament::badge :color="$item['item_badge_color']">
                                            {{ $item['item_status_label'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['cost_price']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono font-extrabold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['adjustment_value']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
