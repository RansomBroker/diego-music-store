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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Faktur Pembelian</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_transactions'], 0, ',', '.') }} Transaksi
            </div>
            <span class="text-xs text-gray-400">Total Qty: {{ number_format($data['total_qty'], 0, ',', '.') }} Item</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Grand Total Pembelian</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_grand_total']) }}
            </div>
            <span class="text-xs text-gray-400">Subtotal: {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_subtotal']) }}</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Terbayar</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_paid']) }}
            </div>
            <span class="text-xs text-gray-400">Diskon/Pajak: {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_discount']) }} / {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_tax']) }}</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Total Sisa Hutang Usaha</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_unpaid']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Periode {{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Main Purchase Data Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'detail' ? 'RINCIAN DETAIL PRODUK PER FAKTUR PEMBELIAN' : 'RINGKASAN FAKTUR PEMBELIAN' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Mode: <strong>{{ strtoupper($data['mode']) }}</strong> &bull; Tipe: <strong>{{ strtoupper($data['purchase_type']) }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'detail')
                {{-- Mode Detail Produk --}}
                <div class="p-6 space-y-6">
                    @forelse($data['purchases'] as $p)
                        <div class="border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                            <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/5 flex flex-col sm:flex-row justify-between sm:items-center gap-2 text-xs border-b border-gray-300 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-extrabold text-sm text-gray-900 dark:text-white">{{ $p['transaction_no'] }}</span>
                                    <span class="text-gray-500 font-mono">Inv: {{ $p['invoice_number'] }}</span>
                                    <x-filament::badge :color="$p['purchase_type'] === 'Kredit' ? 'warning' : 'gray'">
                                        {{ $p['purchase_type'] }}
                                    </x-filament::badge>
                                </div>
                                <div class="flex items-center gap-4 text-gray-700 dark:text-gray-300">
                                    <span>Supplier: <strong>{{ $p['supplier_name'] }}</strong></span>
                                    <span>Tgl: <strong>{{ \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y') }}</strong></span>
                                    <span>Total: <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($p['grand_total']) }}</strong></span>
                                </div>
                            </div>

                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-[11px] uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                        <th class="py-2 px-6 w-32">SKU</th>
                                        <th class="py-2 px-6">Nama Produk & Variasi</th>
                                        <th class="py-2 px-6 text-center w-24">Qty</th>
                                        <th class="py-2 px-6 text-center w-20">Satuan</th>
                                        <th class="py-2 px-6 text-right w-36">Harga Satuan</th>
                                        <th class="py-2 px-6 text-right w-32">Diskon Item</th>
                                        <th class="py-2 px-6 text-right w-36">Subtotal Item</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    @foreach($p['items'] as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                            <td class="py-2 px-6 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                            <td class="py-2 px-6 font-semibold text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                                            <td class="py-2 px-6 text-center font-mono font-bold">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                                            <td class="py-2 px-6 text-center text-gray-500">{{ $item['unit'] }}</td>
                                            <td class="py-2 px-6 text-right font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['price']) }}</td>
                                            <td class="py-2 px-6 text-right font-mono text-gray-500">{{ $item['discount'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['discount']) : '-' }}</td>
                                            <td class="py-2 px-6 text-right font-mono font-bold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['subtotal']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 text-xs italic">
                            Tidak ada transaksi pembelian pada periode ini.
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Mode Ringkasan Faktur --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Faktur</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Supplier</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Tipe</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Status Bayar</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Subtotal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Diskon</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Pajak/Ongkir</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Grand Total</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Sisa Hutang</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['purchases'] as $p)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $p['transaction_no'] }}
                                    <div class="text-[10px] text-gray-400 font-normal">Inv: {{ $p['invoice_number'] }}</div>
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $p['supplier_name'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge :color="$p['purchase_type'] === 'Kredit' ? 'warning' : 'gray'" class="whitespace-nowrap inline-flex">
                                        {{ $p['purchase_type'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge :color="$p['payment_status'] === 'Lunas' ? 'success' : ($p['payment_status'] === 'Sebagian' ? 'info' : 'danger')" class="whitespace-nowrap inline-flex">
                                        {{ $p['payment_status'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($p['subtotal']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ $p['discount'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($p['discount']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($p['tax'] + $p['shipping']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($p['grand_total']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($p['unpaid_amount']) }}</td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::button size="xs" color="gray" wire:click="openPurchaseDetailModal({{ $p['id'] }})">
                                        Lihat Detail
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada transaksi pembelian pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>

    {{-- Modal Popup Detail Faktur Pembelian --}}
    <x-filament::modal id="purchase-detail-modal" width="5xl">
        <x-slot name="heading">
            Rincian Faktur Pembelian: {{ $selectedPurchaseDetail['transaction_no'] ?? '' }}
        </x-slot>

        @if($selectedPurchaseDetail)
            <div class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-gray-50 dark:bg-white/5 p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div>
                        <span class="text-gray-500 block">No. Invoice Supplier:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ $selectedPurchaseDetail['invoice_number'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Tanggal & Supplier:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $selectedPurchaseDetail['date'] }} &bull; {{ $selectedPurchaseDetail['supplier_name'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Tipe Pembelian:</span>
                        <x-filament::badge :color="$selectedPurchaseDetail['purchase_type'] === 'Kredit' ? 'warning' : 'gray'" class="mt-0.5">
                            {{ $selectedPurchaseDetail['purchase_type'] }}
                        </x-filament::badge>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Grand Total:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($selectedPurchaseDetail['grand_total']) }}</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                <th class="py-2 px-4">SKU</th>
                                <th class="py-2 px-4">Nama Produk & Variasi</th>
                                <th class="py-2 px-4 text-center">Qty</th>
                                <th class="py-2 px-4 text-center">Satuan</th>
                                <th class="py-2 px-4 text-right">Harga Satuan</th>
                                <th class="py-2 px-4 text-right">Diskon Item</th>
                                <th class="py-2 px-4 text-right">Subtotal Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($selectedPurchaseDetail['items'] as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="py-2 px-4 font-mono text-gray-500">{{ $item['sku'] }}</td>
                                    <td class="py-2 px-4 font-semibold text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                                    <td class="py-2 px-4 text-center font-mono font-bold">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-4 text-center text-gray-500">{{ $item['unit'] }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['price']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-500">{{ $item['discount'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['discount']) : '-' }}</td>
                                    <td class="py-2 px-4 text-right font-mono font-bold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['subtotal']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
