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
                <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm tracking-wide">
                        LAPORAN JURNAL UMUM (TOTAL: {{ $data['total_entries'] }} BUKTI JURNAL)
                    </h4>
                    <p class="text-xs opacity-80">
                        Periode: {{ \Illuminate\Support\Carbon::parse($data['from_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->translatedFormat('d F Y') }} • Status: {{ strtoupper($data['status']) }} • Cabang: {{ $data['branch_name'] }}
                    </p>
                </div>
            </div>
            <div class="text-right flex items-center gap-6">
                <div>
                    <span class="text-xs font-medium uppercase opacity-75">Grand Total Debit</span>
                    <div class="text-sm font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_debit']) }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase opacity-75">Grand Total Kredit</span>
                    <div class="text-sm font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_credit']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Journal Entries List --}}
    <div class="space-y-6">
        @forelse($data['entries'] as $entry)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 text-xs font-mono font-extrabold bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded border border-gray-300 dark:border-gray-600">
                            {{ $entry['entry_no'] }}
                        </span>
                        <span class="text-xs text-gray-500 font-normal">
                            {{ \Illuminate\Support\Carbon::parse($entry['date'])->translatedFormat('d F Y') }}
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded border {{ $entry['status'] === 'posted' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border-gray-400 dark:border-gray-600' }}">
                            {{ strtoupper($entry['status']) }}
                        </span>
                    </div>
                </x-slot>

                <x-slot name="headerEnd">
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-3">
                        <span>Cabang: <strong>{{ $entry['branch_name'] }}</strong></span>
                        <span>Oleh: <strong>{{ $entry['creator_name'] }}</strong></span>
                    </div>
                </x-slot>

                <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                    {{-- Description --}}
                    <div class="p-3 px-6 bg-gray-50/50 dark:bg-white/5 text-xs text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800">
                        <strong>Keterangan:</strong> {{ $entry['description'] ?: '-' }}
                    </div>

                    {{-- Items Table --}}
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2 px-6 w-32">Kode Akun</th>
                                <th class="py-2 px-6">Nama Akun</th>
                                <th class="py-2 px-6">Memo Item</th>
                                <th class="py-2 px-6 text-right w-36">Debit</th>
                                <th class="py-2 px-6 text-right w-36">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($entry['items'] as $item)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                    <td class="py-2 px-6 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $item['account_code'] }}</td>
                                    <td class="py-2 px-6 font-semibold text-gray-900 dark:text-white">{{ $item['account_name'] }}</td>
                                    <td class="py-2 px-6 text-xs text-gray-500 dark:text-gray-400">{{ $item['notes'] ?: '-' }}</td>
                                    <td class="py-2 px-6 text-right font-mono text-xs">
                                        {{ $item['debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['debit']) : '-' }}
                                    </td>
                                    <td class="py-2 px-6 text-right font-mono text-xs">
                                        {{ $item['credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($item['credit']) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Entry Subtotal Footer Bar --}}
                    <div class="p-3 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                        <span class="font-bold text-xs uppercase tracking-wider">TOTAL BUKTI JURNAL</span>
                        <div class="flex items-center gap-6 text-xs font-mono font-bold">
                            <span>Debit: {{ \App\Helpers\FinancialReportHelper::formatRupiah($entry['total_debit']) }}</span>
                            <span>Kredit: {{ \App\Helpers\FinancialReportHelper::formatRupiah($entry['total_credit']) }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @empty
            <div class="p-12 text-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak Ada Bukti Jurnal Ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Tidak ada data transaksi jurnal yang sesuai dengan kriteria filter pada periode ini.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
