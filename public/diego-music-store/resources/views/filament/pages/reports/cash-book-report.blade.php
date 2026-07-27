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
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Saldo Awal Periode</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['initial_balance']) }}
            </div>
            <span class="text-xs text-gray-400">Sebelum {{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Kas Masuk (Inflow)</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_inflow']) }}
            </div>
            <span class="text-xs text-gray-400">Penerimaan Kas & Bank</span>
        </div>

        <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Kas Keluar (Outflow)</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_outflow']) }}
            </div>
            <span class="text-xs text-gray-400">Pengeluaran Kas & Bank</span>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl shadow-sm">
            <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">Saldo Akhir Periode</span>
            <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['ending_balance']) }}
            </div>
            <span class="text-xs text-gray-700 dark:text-gray-300 font-semibold">Per Tanggal {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Main Cash Book Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white uppercase">
                    {{ $data['mode'] === 'summary_category' ? 'REKAPITULASI PER KATEGORI PENERIMAAN / PENGELUARAN' : 'BUKU KAS BERJALAN (RUNNING BALANCE LOG)' }}
                </span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                Akun: <strong>{{ $data['account_name'] }}</strong> &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            </span>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            @if($data['mode'] === 'summary_category')
                {{-- Mode Summary Category --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">Kategori Transaksi / Lawan Akun</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Masuk (Inflow)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Total Keluar (Outflow)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Selisih Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['categories'] as $cat)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-extrabold text-xs text-gray-900 dark:text-white whitespace-nowrap">{{ $cat['category_name'] }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $cat['inflow'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($cat['inflow']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">{{ $cat['outflow'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($cat['outflow']) : '-' }}</td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($cat['net_amount']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400 text-xs">Tidak ada mutasi kas & bank pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- Mode Running Balance --}}
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-4 whitespace-nowrap">No. Transaksi</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Akun Kas/Bank</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Lawan Akun / Kategori</th>
                            <th class="py-2.5 px-4 whitespace-nowrap">Keterangan</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Kas Masuk (In)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Kas Keluar (Out)</th>
                            <th class="py-2.5 px-4 whitespace-nowrap text-right">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        {{-- Row Saldo Awal --}}
                        <tr class="bg-gray-50/70 dark:bg-white/5 text-gray-900 dark:text-white font-semibold">
                            <td class="py-2.5 px-4 font-mono text-xs text-gray-400 whitespace-nowrap">-</td>
                            <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-4 text-xs font-mono whitespace-nowrap">{{ $data['account_name'] }}</td>
                            <td class="py-2.5 px-4 text-xs font-bold whitespace-nowrap">SALDO AWAL PERIODE</td>
                            <td class="py-2.5 px-4 text-xs text-gray-500 whitespace-nowrap">Saldo Kas/Bank sebelum {{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}</td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">-</td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs whitespace-nowrap">-</td>
                            <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['initial_balance']) }}</td>
                        </tr>

                        @forelse($data['rows'] as $row)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                <td class="py-2.5 px-4 font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $row['entry_no'] }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $row['account_name'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $row['opposing_account'] }}
                                </td>
                                <td class="py-2.5 px-4 text-xs text-gray-500 truncate max-w-xs">
                                    {{ $row['description'] }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $row['inflow'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($row['inflow']) : '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs text-gray-500 whitespace-nowrap">
                                    {{ $row['outflow'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($row['outflow']) : '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($row['running_balance']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-gray-400 text-xs">
                                    Tidak ada mutasi transaksi kas & bank pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
