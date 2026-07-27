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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Bukti Pelunasan Supplier</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_payments_count'], 0, ',', '.') }} Transaksi
            </div>
            <span class="text-xs text-gray-400">Total Supplier: {{ number_format($data['total_suppliers_paid'], 0, ',', '.') }} Supplier</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Faktur Dilunasi</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_invoices_paid'], 0, ',', '.') }} Faktur
            </div>
            <span class="text-xs text-gray-400">Alokasi Pelunasan Faktur</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Akun Kas/Bank Terpakai</span>
            <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-1 truncate">
                {{ $data['account_name'] }}
            </div>
            <span class="text-xs text-gray-400">Pengeluaran Kas/Bank</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Nominal Pelunasan</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_amount_paid']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Periode {{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Main Supplier Payment Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'detail' ? 'RINCIAN ALOKASI FAKTUR TERBAYAR' : 'RINGKASAN BUKTI PELUNASAN HUTANG' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Mode: <strong>{{ strtoupper($data['mode']) }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'detail')
                {{-- Mode Detail Alokasi Faktur --}}
                <div class="p-6 space-y-6">
                    @forelse($data['payments'] as $pay)
                        <div class="border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                            <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/5 flex flex-col sm:flex-row justify-between sm:items-center gap-2 text-xs border-b border-gray-300 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-extrabold text-sm text-gray-900 dark:text-white">{{ $pay['payment_no'] }}</span>
                                    <span class="text-gray-500 font-mono">Ref: {{ $pay['payment_reference'] }}</span>
                                    <x-filament::badge color="info">
                                        {{ $pay['payment_method'] }}
                                    </x-filament::badge>
                                </div>
                                <div class="flex items-center gap-4 text-gray-700 dark:text-gray-300">
                                    <span>Supplier: <strong>{{ $pay['supplier_name'] }}</strong></span>
                                    <span>Tgl: <strong>{{ \Illuminate\Support\Carbon::parse($pay['payment_date'])->format('d/m/Y') }}</strong></span>
                                    <span>Total: <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($pay['total_amount']) }}</strong></span>
                                </div>
                            </div>

                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-[11px] uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                        <th class="py-2 px-6 w-36">No. Faktur Beli</th>
                                        <th class="py-2 px-6 w-32">Inv Supplier</th>
                                        <th class="py-2 px-6 w-28">Tgl Faktur</th>
                                        <th class="py-2 px-6 text-right w-36">Total Faktur</th>
                                        <th class="py-2 px-6 text-right w-36">Hutang Sebelum Bayar</th>
                                        <th class="py-2 px-6 text-right w-36">Nominal Dilunasi</th>
                                        <th class="py-2 px-6 text-right w-36">Sisa Hutang</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    @foreach($pay['items'] as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                            <td class="py-2 px-6 font-mono font-bold text-gray-900 dark:text-white">{{ $item['transaction_no'] }}</td>
                                            <td class="py-2 px-6 font-mono text-gray-500">{{ $item['invoice_number'] }}</td>
                                            <td class="py-2 px-6 font-mono text-gray-500">{{ \Illuminate\Support\Carbon::parse($item['purchase_date'])->format('d/m/Y') }}</td>
                                            <td class="py-2 px-6 text-right font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['grand_total']) }}</td>
                                            <td class="py-2 px-6 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['amount_due']) }}</td>
                                            <td class="py-2 px-6 text-right font-mono font-extrabold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['amount_paid']) }}</td>
                                            <td class="py-2 px-6 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['remaining_balance']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 text-xs italic">
                            Tidak ada pelunasan hutang supplier pada periode ini.
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Mode Summary Bukti Pelunasan --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Bukti</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Supplier</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Metode</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Akun Kas/Bank</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Ref</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Faktur Dilunasi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Nominal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Keterangan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['payments'] as $pay)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $pay['payment_no'] }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($pay['payment_date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $pay['supplier_name'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge color="info" class="whitespace-nowrap inline-flex">
                                        {{ $pay['payment_method'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $pay['account_name'] }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ $pay['payment_reference'] }}
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono text-xs font-bold whitespace-nowrap">
                                    {{ number_format($pay['items_count'], 0, ',', '.') }} Faktur
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($pay['total_amount']) }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-gray-500 truncate max-w-xs">
                                    {{ $pay['notes'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::button size="xs" color="gray" wire:click="openPaymentDetailModal({{ $pay['id'] }})">
                                        Lihat Detail
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada transaksi pelunasan hutang supplier pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>

    {{-- Modal Popup Detail Pelunasan Hutang --}}
    <x-filament::modal id="payment-detail-modal" width="5xl">
        <x-slot name="heading">
            Rincian Bukti Pelunasan Hutang: {{ $selectedPaymentDetail['payment_no'] ?? '' }}
        </x-slot>

        @if($selectedPaymentDetail)
            <div class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-gray-50 dark:bg-white/5 p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div>
                        <span class="text-gray-500 block">Tanggal & Supplier:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $selectedPaymentDetail['payment_date'] }} &bull; {{ $selectedPaymentDetail['supplier_name'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Metode & No. Ref:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ $selectedPaymentDetail['payment_method'] }} ({{ $selectedPaymentDetail['payment_reference'] }})</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Akun Pembayaran:</span>
                        <span class="font-mono text-gray-900 dark:text-white">{{ $selectedPaymentDetail['account_name'] }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Total Nominal:</span>
                        <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($selectedPaymentDetail['total_amount']) }}</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase font-bold border-b border-gray-200 dark:border-gray-800">
                                <th class="py-2 px-4">No. Faktur Beli</th>
                                <th class="py-2 px-4">Inv Supplier</th>
                                <th class="py-2 px-4">Tgl Faktur</th>
                                <th class="py-2 px-4 text-right">Total Faktur</th>
                                <th class="py-2 px-4 text-right">Hutang Sebelum Bayar</th>
                                <th class="py-2 px-4 text-right">Nominal Dilunasi</th>
                                <th class="py-2 px-4 text-right">Sisa Hutang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($selectedPaymentDetail['items'] as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="py-2 px-4 font-mono font-bold text-gray-900 dark:text-white">{{ $item['transaction_no'] }}</td>
                                    <td class="py-2 px-4 font-mono text-gray-500">{{ $item['invoice_number'] }}</td>
                                    <td class="py-2 px-4 font-mono text-gray-500">{{ $item['purchase_date'] }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['grand_total']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['amount_due']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono font-extrabold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['amount_paid']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-500">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['remaining_balance']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::modal>
</x-filament-panels::page>
