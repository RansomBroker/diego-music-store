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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <div>
                    <h4 class="font-bold text-sm tracking-wide">
                        {{ $data['selected_account'] ? ('BUKU BESAR: ' . $data['selected_account']->code . ' - ' . $data['selected_account']->name) : 'BUKU BESAR LENGKAP (ALL ACCOUNTS)' }}
                    </h4>
                    <p class="text-xs opacity-80">
                        Periode: {{ \Illuminate\Support\Carbon::parse($data['from_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->translatedFormat('d F Y') }} • Cabang: {{ $data['branch_name'] }}
                    </p>
                </div>
            </div>
            <div class="text-right flex items-center gap-6">
                <div>
                    <span class="text-xs font-medium uppercase opacity-75">Total Mutasi Debit</span>
                    <div class="text-sm font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_debit']) }}</div>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase opacity-75">Total Mutasi Kredit</span>
                    <div class="text-sm font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['grand_total_credit']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- General Ledger Accounts List --}}
    <div class="space-y-6">
        @forelse($data['ledgers'] as $ledger)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-mono font-bold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded">
                            {{ $ledger['account_code'] }}
                        </span>
                        <span class="font-extrabold tracking-wide text-gray-900 dark:text-white">{{ $ledger['account_name'] }}</span>
                    </div>
                </x-slot>

                <x-slot name="headerEnd">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 uppercase">
                        {{ $ledger['classification'] }}
                    </span>
                </x-slot>

                <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2.5 px-6">Tanggal</th>
                                <th class="py-2.5 px-6">No. Bukti Jurnal</th>
                                <th class="py-2.5 px-6">Keterangan / Deskripsi</th>
                                <th class="py-2.5 px-6 text-right">Debit</th>
                                <th class="py-2.5 px-6 text-right">Kredit</th>
                                <th class="py-2.5 px-6 text-right">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            {{-- Beginning Balance Row --}}
                            <tr class="bg-gray-50/80 font-bold dark:bg-white/5 text-gray-900 dark:text-white">
                                <td class="py-2.5 px-6 font-mono text-xs text-gray-500" colspan="2">
                                    {{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-6 font-semibold uppercase text-xs tracking-wider">
                                    SALDO AWAL (BEGINNING BALANCE)
                                </td>
                                <td class="py-2.5 px-6 text-right font-mono text-xs" colspan="2">-</td>
                                <td class="py-2.5 px-6 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($ledger['beginning_balance']) }}
                                </td>
                            </tr>

                            {{-- Transaction Rows --}}
                            @forelse($ledger['transactions'] as $tx)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                    <td class="py-2 px-6 font-mono text-xs text-gray-500 whitespace-nowrap">
                                        {{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="py-2 px-6 font-mono text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                        {{ $tx['entry_no'] }}
                                    </td>
                                    <td class="py-2 px-6 text-xs">
                                        {{ $tx['description'] }}
                                    </td>
                                    <td class="py-2 px-6 text-right font-mono text-xs">
                                        {{ $tx['debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($tx['debit']) : '-' }}
                                    </td>
                                    <td class="py-2 px-6 text-right font-mono text-xs">
                                        {{ $tx['credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($tx['credit']) : '-' }}
                                    </td>
                                    <td class="py-2 px-6 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">
                                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($tx['running_balance']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-3 px-6 text-center text-gray-400 text-xs italic">
                                        Tidak ada mutasi transaksi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Ending Balance Footer Bar (Monochrome High Contrast) --}}
                    <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                        <div class="flex items-center gap-6">
                            <span class="font-bold text-xs uppercase tracking-wider">TOTAL MUTASI PERIODE</span>
                            <span class="text-xs font-mono">Debit: <strong>{{ \App\Helpers\FinancialReportHelper::formatRupiah($ledger['total_debit']) }}</strong></span>
                            <span class="text-xs font-mono">Kredit: <strong>{{ \App\Helpers\FinancialReportHelper::formatRupiah($ledger['total_credit']) }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs uppercase tracking-wider">SALDO AKHIR:</span>
                            <span class="font-mono text-sm font-extrabold text-gray-900 dark:text-white">
                                {{ \App\Helpers\FinancialReportHelper::formatRupiah($ledger['ending_balance']) }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @empty
            <div class="p-12 text-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Belum Ada Akun Terpilih</p>
                <p class="text-xs text-gray-400 mt-1">Silakan sesuaikan filter akun atau tanggal pada form di atas.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
