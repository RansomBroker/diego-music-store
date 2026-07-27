<x-filament-panels::page>
    @php
        $data = $this->report_data;
    @endphp

    {{-- Filter Form (Native Filament Section) --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- Status Banner (Monochrome Printer-Friendly) --}}
    <div class="p-4 border rounded-xl shadow-sm bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($data['is_balanced'])
                    <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-rose-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
                <div>
                    <h4 class="font-bold text-sm tracking-wide">
                        {{ $data['is_balanced'] ? 'STATUS: SEIMBANG (BALANCED 100%)' : 'STATUS: TIDAK SEIMBANG (OUT OF BALANCE - SELISIH Rp ' . number_format($data['difference'], 0, ',', '.') . ')' }}
                    </h4>
                    <p class="text-xs opacity-80">
                        Periode: {{ \Illuminate\Support\Carbon::parse($data['from_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->translatedFormat('d F Y') }} • Cabang: {{ $data['branch_name'] }} • Mode: 6-KOLOM
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-medium uppercase opacity-75">Total Saldo Akhir Debit / Kredit</span>
                <div class="text-base font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_ending_debit']) }}</div>
            </div>
        </div>
    </div>

    {{-- 6-Column Trial Balance Table Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                <span class="font-extrabold tracking-wide">RINCIAN NERACA SALDO 6-KOLOM</span>
            </div>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[900px]">
                <thead>
                    <tr class="bg-gray-100 dark:bg-white/5 text-gray-800 dark:text-gray-200 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                        <th class="py-3 px-4 border-r border-gray-200 dark:border-gray-800 w-28" rowspan="2">Kode</th>
                        <th class="py-3 px-4 border-r border-gray-200 dark:border-gray-800" rowspan="2">Nama Akun / Kategori</th>
                        <th class="py-1.5 px-4 text-center border-b border-r border-gray-200 dark:border-gray-800" colspan="2">Saldo Awal</th>
                        <th class="py-1.5 px-4 text-center border-b border-r border-gray-200 dark:border-gray-800" colspan="2">Mutasi Periode</th>
                        <th class="py-1.5 px-4 text-center border-b border-gray-200 dark:border-gray-800" colspan="2">Saldo Akhir</th>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400 text-[11px] uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                        <th class="py-2 px-3 text-right border-r border-gray-200 dark:border-gray-800 w-28">Debit</th>
                        <th class="py-2 px-3 text-right border-r border-gray-200 dark:border-gray-800 w-28">Kredit</th>
                        <th class="py-2 px-3 text-right border-r border-gray-200 dark:border-gray-800 w-28">Debit</th>
                        <th class="py-2 px-3 text-right border-r border-gray-200 dark:border-gray-800 w-28">Kredit</th>
                        <th class="py-2 px-3 text-right border-r border-gray-200 dark:border-gray-800 w-28">Debit</th>
                        <th class="py-2 px-3 text-right w-28">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($data['items'] as $item)
                        <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                            <td class="py-2.5 px-4 font-mono text-xs text-gray-500 dark:text-gray-400 border-r border-gray-100 dark:border-gray-800">{{ $item['code'] }}</td>
                            <td class="py-2.5 px-4 border-r border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-1.5" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                    @if($item['is_header'])
                                        <span class="font-semibold">{{ $item['name'] }}</span>
                                    @else
                                        <span class="text-gray-400">&bull;</span>
                                        <span>{{ $item['name'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs border-r border-gray-100 dark:border-gray-800">
                                {{ $item['beginning_debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['beginning_debit']) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs border-r border-gray-100 dark:border-gray-800">
                                {{ $item['beginning_credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['beginning_credit']) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs border-r border-gray-100 dark:border-gray-800">
                                {{ $item['period_debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['period_debit']) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs border-r border-gray-100 dark:border-gray-800">
                                {{ $item['period_credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['period_credit']) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs font-bold border-r border-gray-100 dark:border-gray-800 text-gray-900 dark:text-white">
                                {{ $item['ending_debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['ending_debit']) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">
                                {{ $item['ending_credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['ending_credit']) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500 dark:text-gray-400 text-xs">
                                Tidak ada akun atau saldo yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-white/10 font-extrabold text-gray-900 dark:text-white border-t-2 border-gray-400 dark:border-gray-600 text-xs">
                        <td colspan="2" class="py-3.5 px-4 uppercase tracking-wider text-left border-r border-gray-300 dark:border-gray-700">
                            TOTAL NERACA SALDO
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_beginning_debit']) }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_beginning_credit']) }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_period_debit']) }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_period_credit']) }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_ending_debit']) }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['total_ending_credit']) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
