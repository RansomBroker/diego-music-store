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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Faktur Belum Lunas</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ number_format($data['total_invoices'], 0, ',', '.') }} Faktur
            </div>
            <span class="text-xs text-gray-400">Total Supplier: {{ count($data['suppliers']) }} Supplier</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Belum Jatuh Tempo (Current)</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_current']) }}
            </div>
            <span class="text-xs text-gray-400">Hutang Aktif Berjalan</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Sudah Jatuh Tempo (Overdue)</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_overdue']) }}
            </div>
            <span class="text-xs text-gray-400">Memerlukan Pelunasan Immediate</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Grand Total Sisa Hutang</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_unpaid']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Per Tanggal {{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Main Accounts Payable Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    @if($data['mode'] === 'summary_supplier')
                        REKAPITULASI TOTAL HUTANG PER SUPPLIER
                    @elseif($data['mode'] === 'aging')
                        ANALISIS UMUR HUTANG USAHA (AP AGING MATRIX)
                    @else
                        RINCIAN FAKTUR HUTANG BELUM LUNAS
                    @endif
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Per Tanggal: <strong>{{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'summary_supplier')
                {{-- Mode Summary Supplier --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">Supplier</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Telepon</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Jumlah Faktur</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Pembelian</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Terbayar</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Sisa Hutang Usaha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['suppliers'] as $sup)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-extrabold text-xs text-gray-900 dark:text-white whitespace-nowrap">{{ $sup['supplier_name'] }}</td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $sup['supplier_phone'] }}</td>
                                <td class="py-2.5 px-4 text-center font-mono font-bold whitespace-nowrap">{{ number_format($sup['count_invoices'], 0, ',', '.') }} Faktur</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($sup['grand_total']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($sup['paid_amount']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($sup['unpaid_amount']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-400 text-xs">Tidak ada saldo hutang supplier pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($data['mode'] === 'aging')
                {{-- Mode Aging Matrix --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">Supplier</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Belum Jatuh Tempo</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">1 - 30 Hari</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">31 - 60 Hari</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">61 - 90 Hari</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">> 90 Hari</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['suppliers'] as $sup)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-extrabold text-xs text-gray-900 dark:text-white whitespace-nowrap">{{ $sup['supplier_name'] }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $sup['current'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($sup['current']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $sup['aging_1_30'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($sup['aging_1_30']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $sup['aging_31_60'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($sup['aging_31_60']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $sup['aging_61_90'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($sup['aging_61_90']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $sup['aging_90_plus'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($sup['aging_90_plus']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($sup['unpaid_amount']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-400 text-xs">Tidak ada saldo hutang supplier pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- Mode Detail Invoice --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Faktur</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Jatuh Tempo</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Supplier</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Status Overdue</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-center">Terlambat</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Grand Total</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Terbayar</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['invoices'] as $inv)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $inv['transaction_no'] }}
                                    <div class="text-[10px] text-gray-400 font-normal">Inv: {{ $inv['invoice_number'] }}</div>
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($inv['date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($inv['due_date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $inv['supplier_name'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center whitespace-nowrap">
                                    <x-filament::badge :color="$inv['is_overdue'] ? 'danger' : 'success'" class="whitespace-nowrap inline-flex">
                                        {{ $inv['is_overdue'] ? 'JATUH TEMPO' : 'LANCAR' }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2.5 px-4 text-xs text-center font-mono font-bold whitespace-nowrap">
                                    {{ $inv['is_overdue'] ? ($inv['overdue_days'] . ' Hari') : '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($inv['grand_total']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($inv['paid_amount']) }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($inv['unpaid_amount']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada faktur hutang belum lunas pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
