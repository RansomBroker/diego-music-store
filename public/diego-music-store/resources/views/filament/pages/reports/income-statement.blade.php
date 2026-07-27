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
                @if($data['is_profit'])
                    <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                @endif
                <div>
                    <h4 class="font-bold text-sm tracking-wide">
                        {{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}
                    </h4>
                    <p class="text-xs opacity-80">
                        Periode: {{ \Illuminate\Support\Carbon::parse($data['from_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->translatedFormat('d F Y') }} • Cabang: {{ $data['branch_name'] }} • Mode: {{ strtoupper($data['view_type']) }}
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-medium uppercase opacity-75">Nominal Akhir</span>
                <div class="text-base font-extrabold">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['net_income']) }}</div>
            </div>
        </div>
    </div>

    {{-- Multi-Step Income Statement Sections (Monochrome Printer-Friendly) --}}
    <div class="space-y-6">

        {{-- 1. PENDAPATAN OPERASIONAL --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                    <span class="font-extrabold tracking-wide">PENDAPATAN OPERASIONAL</span>
                </div>
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-6">Kode</th>
                            <th class="py-2.5 px-6">Nama Akun / Kategori</th>
                            <th class="py-2.5 px-6 text-right">Nominal</th>
                            <th class="py-2.5 px-4 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['revenue']['items'] as $item)
                            <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                                <td class="py-2.5 px-6 font-mono text-xs text-gray-500 dark:text-gray-400 w-32">{{ $item['code'] }}</td>
                                <td class="py-2.5 px-6">
                                    <div class="flex items-center gap-1.5" style="padding-left: {{ max(0, ($item['level'] - 1) * 1.25) }}rem;">
                                        @if($item['is_header'])
                                            <span class="font-semibold">{{ $item['name'] }}</span>
                                        @else
                                            <span class="text-gray-400">&bull;</span>
                                            <span>{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-6 text-right font-mono text-xs {{ $item['is_header'] ? 'font-bold' : '' }}">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['balance']) }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="inline-flex items-center justify-center p-1 text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                                    Tidak ada data Pendapatan Operasional pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                    <span class="font-bold text-xs uppercase tracking-wider">TOTAL PENDAPATAN OPERASIONAL</span>
                    <span class="font-mono text-sm font-bold">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['revenue']['total']) }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- 2. HARGA POKOK PENJUALAN (COGS) --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                    <span class="font-extrabold tracking-wide">HARGA POKOK PENJUALAN (COGS)</span>
                </div>
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-6">Kode</th>
                            <th class="py-2.5 px-6">Nama Akun / Kategori</th>
                            <th class="py-2.5 px-6 text-right">Nominal</th>
                            <th class="py-2.5 px-4 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['cogs']['items'] as $item)
                            <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                                <td class="py-2.5 px-6 font-mono text-xs text-gray-500 dark:text-gray-400 w-32">{{ $item['code'] }}</td>
                                <td class="py-2.5 px-6">
                                    <div class="flex items-center gap-1.5" style="padding-left: {{ max(0, ($item['level'] - 1) * 1.25) }}rem;">
                                        @if($item['is_header'])
                                            <span class="font-semibold">{{ $item['name'] }}</span>
                                        @else
                                            <span class="text-gray-400">&bull;</span>
                                            <span>{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-6 text-right font-mono text-xs {{ $item['is_header'] ? 'font-bold' : '' }}">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['balance']) }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="inline-flex items-center justify-center p-1 text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                                    Tidak ada data Harga Pokok Penjualan pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                    <span class="font-bold text-xs uppercase tracking-wider">TOTAL HARGA POKOK PENJUALAN</span>
                    <span class="font-mono text-sm font-bold">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['cogs']['total']) }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- SUMMARY BAR: LABA KOTOR (GROSS PROFIT) --}}
        <div class="p-4 px-6 bg-gray-200/80 dark:bg-white/15 border-2 border-gray-400 dark:border-gray-600 rounded-xl flex justify-between items-center text-gray-900 dark:text-white shadow-sm">
            <span class="font-extrabold text-sm uppercase tracking-wider">LABA KOTOR (GROSS PROFIT)</span>
            <span class="font-mono text-base font-extrabold">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['gross_profit']) }}
            </span>
        </div>

        {{-- 3. BEBAN OPERASIONAL --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                    <span class="font-extrabold tracking-wide">BEBAN OPERASIONAL</span>
                </div>
            </x-slot>

            <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                            <th class="py-2.5 px-6">Kode</th>
                            <th class="py-2.5 px-6">Nama Akun / Kategori</th>
                            <th class="py-2.5 px-6 text-right">Nominal</th>
                            <th class="py-2.5 px-4 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['operating_expenses']['items'] as $item)
                            <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                                <td class="py-2.5 px-6 font-mono text-xs text-gray-500 dark:text-gray-400 w-32">{{ $item['code'] }}</td>
                                <td class="py-2.5 px-6">
                                    <div class="flex items-center gap-1.5" style="padding-left: {{ max(0, ($item['level'] - 1) * 1.25) }}rem;">
                                        @if($item['is_header'])
                                            <span class="font-semibold">{{ $item['name'] }}</span>
                                        @else
                                            <span class="text-gray-400">&bull;</span>
                                            <span>{{ $item['name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-6 text-right font-mono text-xs {{ $item['is_header'] ? 'font-bold' : '' }}">
                                    {{ \App\Helpers\FinancialReportHelper::formatRupiah($item['balance']) }}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="inline-flex items-center justify-center p-1 text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-all"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500 dark:text-gray-400 text-xs">
                                    Tidak ada data Beban Operasional pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                    <span class="font-bold text-xs uppercase tracking-wider">TOTAL BEBAN OPERASIONAL</span>
                    <span class="font-mono text-sm font-bold">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['operating_expenses']['total']) }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- SUMMARY BAR: LABA OPERASIONAL (OPERATING INCOME) --}}
        <div class="p-4 px-6 bg-gray-200/80 dark:bg-white/15 border-2 border-gray-400 dark:border-gray-600 rounded-xl flex justify-between items-center text-gray-900 dark:text-white shadow-sm">
            <span class="font-extrabold text-sm uppercase tracking-wider">LABA OPERASIONAL (OPERATING INCOME)</span>
            <span class="font-mono text-base font-extrabold">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['operating_income']) }}
            </span>
        </div>

        {{-- 4. PENDAPATAN & BEBAN NON-OPERASIONAL (JIKA ADA) --}}
        @if(count($data['other_revenue']['items']) > 0 || count($data['other_expenses']['items']) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                        <span class="font-extrabold tracking-wide">PENDAPATAN & BEBAN NON-OPERASIONAL</span>
                    </div>
                </x-slot>

                <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                                <th class="py-2.5 px-6">Kode</th>
                                <th class="py-2.5 px-6">Nama Akun / Kategori</th>
                                <th class="py-2.5 px-6 text-right">Nominal</th>
                                <th class="py-2.5 px-4 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($data['other_revenue']['items'] as $item)
                                <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                                    <td class="py-2.5 px-6 font-mono text-xs text-gray-500 dark:text-gray-400 w-32">{{ $item['code'] }}</td>
                                    <td class="py-2.5 px-6">{{ $item['name'] }}</td>
                                    <td class="py-2.5 px-6 text-right font-mono text-xs font-semibold">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['balance']) }}</td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if(!$item['is_header'])
                                            <button type="button" wire:click="openAccountLedgerModal({{ $item['id'] }})" class="p-1 text-gray-500 hover:text-gray-900 dark:hover:text-white">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @foreach($data['other_expenses']['items'] as $item)
                                <tr class="{{ $item['is_header'] ? 'bg-gray-100/50 font-bold dark:bg-white/5 text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300' }}">
                                    <td class="py-2.5 px-6 font-mono text-xs text-gray-500 dark:text-gray-400 w-32">{{ $item['code'] }}</td>
                                    <td class="py-2.5 px-6">{{ $item['name'] }}</td>
                                    <td class="py-2.5 px-6 text-right font-mono text-xs font-semibold">{{ \App\Helpers\FinancialReportHelper::formatRupiah(-$item['balance']) }}</td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if(!$item['is_header'])
                                            <button type="button" wire:click="openAccountLedgerModal({{ $item['id'] }})" class="p-1 text-gray-500 hover:text-gray-900 dark:hover:text-white">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-3.5 px-6 bg-gray-100 dark:bg-white/10 flex justify-between items-center text-gray-900 dark:text-white">
                        <span class="font-bold text-xs uppercase tracking-wider">NET NON-OPERATIONAL</span>
                        <span class="font-mono text-sm font-bold">
                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['net_other']) }}
                        </span>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- GRAND TOTAL BAR: LABA / (RUGI) BERSIH (NET INCOME) --}}
        <div class="p-5 px-6 bg-gray-100 dark:bg-white/10 border-2 border-gray-500 dark:border-gray-400 rounded-xl flex justify-between items-center text-gray-900 dark:text-white shadow-md">
            <div>
                <span class="font-extrabold text-base uppercase tracking-wider block">
                    {{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Akumulasi Laba / (Rugi) Periode Terpilih</span>
            </div>
            <span class="font-mono text-xl font-extrabold text-gray-900 dark:text-white">
                {{ \App\Helpers\FinancialReportHelper::formatRupiah($data['net_income']) }}
            </span>
        </div>

    </div>

    {{-- INTERACTIVE DRILL-DOWN TRANSACTION LEDGER MODAL DIALOG --}}
    @if($this->showLedgerModal && $this->selectedAccount)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-4xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="p-5 bg-gray-50/80 dark:bg-white/5 border-b border-gray-200 dark:border-white/10 flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-xs font-mono font-bold bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-700 rounded">
                                {{ $this->selectedAccount['code'] }}
                            </span>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                                {{ $this->selectedAccount['name'] }}
                            </h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Rincian Mutasi Jurnal Laba Rugi • Klasifikasi: {{ strtoupper($this->selectedAccount['classification']) }}
                        </p>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeLedgerModal"
                        class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-lg hover:bg-gray-200/50 dark:hover:bg-white/10 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Content Table --}}
                <div class="p-6 overflow-y-auto flex-1 bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-white/10">
                    @if(count($this->ledgerTransactions) > 0)
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-200 dark:border-white/10">
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">No. Bukti</th>
                                    <th class="py-2.5 px-3">Keterangan</th>
                                    <th class="py-2.5 px-3 text-right">Debit</th>
                                    <th class="py-2.5 px-3 text-right">Kredit</th>
                                    <th class="py-2.5 px-3 text-right">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                @foreach($this->ledgerTransactions as $tx)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                                        <td class="py-2 px-3 font-mono text-xs whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d/m/Y') }}</td>
                                        <td class="py-2 px-3 font-mono text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $tx['entry_no'] }}</td>
                                        <td class="py-2 px-3 text-xs">{{ $tx['description'] }}</td>
                                        <td class="py-2 px-3 text-right font-mono text-xs">{{ $tx['debit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($tx['debit']) : '-' }}</td>
                                        <td class="py-2 px-3 text-right font-mono text-xs">{{ $tx['credit'] > 0 ? \App\Helpers\FinancialReportHelper::formatRupiah($tx['credit']) : '-' }}</td>
                                        <td class="py-2 px-3 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">
                                            {{ \App\Helpers\FinancialReportHelper::formatRupiah($tx['running_balance']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm font-semibold">Belum Ada Transaksi Jurnal Posted</p>
                            <p class="text-xs text-gray-400 mt-1">Tidak ada transaksi jurnal ter-posting untuk akun ini pada periode terpilih.</p>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-gray-50/80 dark:bg-white/5 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Total Mutasi Periode: <strong class="font-mono text-gray-900 dark:text-white text-sm ml-1">{{ \App\Helpers\FinancialReportHelper::formatRupiah($this->selectedAccount['total_balance']) }}</strong>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeLedgerModal"
                        class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-gray-700 rounded-lg transition-all"
                    >
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif
</x-filament-panels::page>
