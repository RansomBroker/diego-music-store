<x-filament-panels::page>
    @php
        $data = $this->report_data;
        $monthLabel = strtoupper($data['period_month_label'] ?? 'PERIODE');

        $fmt = function ($val) {
            return \App\Helpers\FinancialReportHelper::formatNumber($val);
        };
    @endphp

    {{-- Filter Form (Native Filament Section) --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- Status Banner (Monochrome High Contrast) --}}
    <div class="p-4 border rounded-xl shadow-sm bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($data['is_profit'])
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <span class="text-xs font-medium uppercase opacity-75">Net Income (Current Period)</span>
                <div class="text-base font-extrabold font-mono">{{ \App\Helpers\FinancialReportHelper::formatRupiah($data['net_income']) }}</div>
            </div>
        </div>
    </div>

    {{-- Comprehensive Multi-Column Comparative Income Statement Table matching reference image --}}
    <div class="overflow-hidden border border-gray-300 dark:border-gray-700 rounded-xl shadow-sm bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-sky-100 dark:bg-sky-950/80 text-gray-900 dark:text-gray-100 font-extrabold border-b border-gray-300 dark:border-gray-700">
                        <th rowspan="2" class="py-3 px-4 border-r border-gray-300 dark:border-gray-700 text-sm uppercase tracking-wider min-w-[280px]">
                            Description
                        </th>
                        <th colspan="3" class="py-2.5 px-3 text-center border-r border-gray-300 dark:border-gray-700 text-xs sm:text-sm font-black uppercase tracking-wide bg-sky-200/70 dark:bg-sky-900/70">
                            {{ $monthLabel }}
                        </th>
                        <th colspan="3" class="py-2.5 px-3 text-center text-xs sm:text-sm font-black uppercase tracking-wide bg-sky-200/50 dark:bg-sky-900/50">
                            YTD (YEAR TO DATE)
                        </th>
                    </tr>
                    <tr class="bg-sky-50 dark:bg-sky-900/40 text-gray-900 dark:text-gray-100 font-extrabold border-b border-gray-300 dark:border-gray-700 uppercase">
                        <th class="py-2 px-3 text-center border-r border-gray-300 dark:border-gray-700 w-28">TOKO</th>
                        <th class="py-2 px-3 text-center border-r border-gray-300 dark:border-gray-700 w-28">GUDANG</th>
                        <th class="py-2 px-3 text-center border-r border-gray-300 dark:border-gray-700 w-32 font-black text-gray-900 dark:text-white bg-sky-100/50 dark:bg-sky-900/60">TOTAL</th>
                        <th class="py-2 px-3 text-center border-r border-gray-300 dark:border-gray-700 w-28">TOKO</th>
                        <th class="py-2 px-3 text-center border-r border-gray-300 dark:border-gray-700 w-28">GUDANG</th>
                        <th class="py-2 px-3 text-center w-32 font-black text-gray-900 dark:text-white bg-sky-100/30 dark:bg-sky-900/40">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-800 dark:text-gray-200">

                    {{-- 1. PENJUALAN / REVENUE --}}
                    <tr class="bg-gray-100/80 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                        <td colspan="7" class="py-2 px-4 uppercase tracking-wider text-xs border-b border-gray-300 dark:border-gray-700">
                            PENJUALAN (REVENUE)
                        </td>
                    </tr>
                    @forelse($data['revenue']['items'] as $item)
                        <tr class="{{ $item['is_header'] ? 'bg-gray-50/50 dark:bg-white/5 font-bold text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5' }}">
                            <td class="py-2 px-4 border-r border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-2" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mr-1">{{ $item['code'] }}</span>
                                        <span class="{{ $item['is_header'] ? 'font-bold' : '' }}">{{ $item['name'] }}</span>
                                    </div>
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="p-1 text-gray-400 hover:text-gray-800 dark:hover:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700 font-bold bg-sky-50/40 dark:bg-sky-950/30 text-gray-900 dark:text-white">{{ $fmt($item['balance']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold bg-sky-50/20 dark:bg-sky-950/20 text-gray-900 dark:text-white">{{ $fmt($item['ytd_total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-3 text-center text-gray-500 italic">Tidak ada data Penjualan.</td>
                        </tr>
                    @endforelse

                    {{-- TOTAL PENJUALAN --}}
                    <tr class="bg-emerald-100/70 dark:bg-emerald-950/50 text-emerald-950 dark:text-emerald-100 font-extrabold border-t-2 border-b-2 border-emerald-300 dark:border-emerald-700">
                        <td class="py-2.5 px-4 uppercase tracking-wide border-r border-emerald-300 dark:border-emerald-700">TOTAL PENJUALAN</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-emerald-300 dark:border-emerald-700">{{ $fmt($data['revenue']['toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-emerald-300 dark:border-emerald-700">{{ $fmt($data['revenue']['gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-emerald-400 dark:border-emerald-600 bg-emerald-200/50 dark:bg-emerald-900/60 font-black text-sm">{{ $fmt($data['revenue']['total']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-emerald-300 dark:border-emerald-700">{{ $fmt($data['revenue']['ytd_toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-emerald-300 dark:border-emerald-700">{{ $fmt($data['revenue']['ytd_gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono bg-emerald-200/40 dark:bg-emerald-900/40 font-black text-sm">{{ $fmt($data['revenue']['ytd_total']) }}</td>
                    </tr>

                    {{-- 2. PEMBELIAN DAN HPP / COGS --}}
                    <tr class="bg-gray-100/80 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                        <td colspan="7" class="py-2 px-4 uppercase tracking-wider text-xs border-b border-gray-300 dark:border-gray-700">
                            PEMBELIAN DAN HPP (COST OF GOODS SOLD)
                        </td>
                    </tr>
                    @forelse($data['cogs']['items'] as $item)
                        <tr class="{{ $item['is_header'] ? 'bg-gray-50/50 dark:bg-white/5 font-bold text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5' }}">
                            <td class="py-2 px-4 border-r border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-2" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mr-1">{{ $item['code'] }}</span>
                                        <span class="{{ $item['is_header'] ? 'font-bold' : '' }}">{{ $item['name'] }}</span>
                                    </div>
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="p-1 text-gray-400 hover:text-gray-800 dark:hover:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700 font-bold bg-sky-50/40 dark:bg-sky-950/30 text-gray-900 dark:text-white">{{ $fmt($item['balance']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold bg-sky-50/20 dark:bg-sky-950/20 text-gray-900 dark:text-white">{{ $fmt($item['ytd_total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-3 text-center text-gray-500 italic">Tidak ada data Pembelian & HPP.</td>
                        </tr>
                    @endforelse

                    {{-- TOTAL PEMBELIAN DAN HPP --}}
                    <tr class="bg-slate-200/80 dark:bg-slate-800/80 text-gray-900 dark:text-white font-extrabold border-t-2 border-b-2 border-gray-300 dark:border-gray-700">
                        <td class="py-2.5 px-4 uppercase tracking-wide border-r border-gray-300 dark:border-gray-700">TOTAL PEMBELIAN DAN HPP</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['cogs']['toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['cogs']['gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600 bg-slate-300/50 dark:bg-slate-700/60 font-black text-sm">{{ $fmt($data['cogs']['total']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['cogs']['ytd_toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['cogs']['ytd_gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono bg-slate-300/30 dark:bg-slate-700/40 font-black text-sm">{{ $fmt($data['cogs']['ytd_total']) }}</td>
                    </tr>

                    {{-- SUMMARY ROW: LABA KOTOR --}}
                    @php $gp = $data['gross_profit_details']; @endphp
                    <tr class="bg-amber-100 dark:bg-amber-950/60 text-amber-950 dark:text-amber-100 font-black border-y-2 border-amber-400 dark:border-amber-600 text-sm">
                        <td class="py-3 px-4 uppercase tracking-wider border-r border-amber-300 dark:border-amber-700">LABA KOTOR (GROSS PROFIT)</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-amber-300 dark:border-amber-700">{{ $fmt($gp['toko']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-amber-300 dark:border-amber-700">{{ $fmt($gp['gudang']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-amber-400 dark:border-amber-600 bg-amber-200/60 dark:bg-amber-900/60 font-extrabold text-base">{{ $fmt($gp['total']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-amber-300 dark:border-amber-700">{{ $fmt($gp['ytd_toko']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-amber-300 dark:border-amber-700">{{ $fmt($gp['ytd_gudang']) }}</td>
                        <td class="py-3 px-3 text-right font-mono bg-amber-200/40 dark:bg-amber-900/40 font-extrabold text-base">{{ $fmt($gp['ytd_total']) }}</td>
                    </tr>

                    {{-- 3. BEBAN OPERASIONAL --}}
                    <tr class="bg-gray-100/80 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                        <td colspan="7" class="py-2 px-4 uppercase tracking-wider text-xs border-b border-gray-300 dark:border-gray-700">
                            BEBAN OPERASIONAL (OPERATING EXPENSES)
                        </td>
                    </tr>
                    @forelse($data['operating_expenses']['items'] as $item)
                        <tr class="{{ $item['is_header'] ? 'bg-gray-50/50 dark:bg-white/5 font-bold text-gray-900 dark:text-white' : 'hover:bg-gray-50/80 dark:hover:bg-white/5' }}">
                            <td class="py-2 px-4 border-r border-gray-200 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-2" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mr-1">{{ $item['code'] }}</span>
                                        <span class="{{ $item['is_header'] ? 'font-bold' : '' }}">{{ $item['name'] }}</span>
                                    </div>
                                    @if(!$item['is_header'])
                                        <button 
                                            type="button" 
                                            wire:click="openAccountLedgerModal({{ $item['id'] }})"
                                            title="Drill-down Rincian Mutasi Jurnal"
                                            class="p-1 text-gray-400 hover:text-gray-800 dark:hover:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700 font-bold bg-sky-50/40 dark:bg-sky-950/30 text-gray-900 dark:text-white">{{ $fmt($item['balance']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_toko']) }}</td>
                            <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_gudang']) }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold bg-sky-50/20 dark:bg-sky-950/20 text-gray-900 dark:text-white">{{ $fmt($item['ytd_total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-3 text-center text-gray-500 italic">Tidak ada data Beban Operasional.</td>
                        </tr>
                    @endforelse

                    {{-- TOTAL BEBAN OPERASIONAL --}}
                    <tr class="bg-slate-200/80 dark:bg-slate-800/80 text-gray-900 dark:text-white font-extrabold border-t-2 border-b-2 border-gray-300 dark:border-gray-700">
                        <td class="py-2.5 px-4 uppercase tracking-wide border-r border-gray-300 dark:border-gray-700">TOTAL BEBAN OPERASIONAL</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['operating_expenses']['toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['operating_expenses']['gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600 bg-slate-300/50 dark:bg-slate-700/60 font-black text-sm">{{ $fmt($data['operating_expenses']['total']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['operating_expenses']['ytd_toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700">{{ $fmt($data['operating_expenses']['ytd_gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono bg-slate-300/30 dark:bg-slate-700/40 font-black text-sm">{{ $fmt($data['operating_expenses']['ytd_total']) }}</td>
                    </tr>

                    {{-- SUMMARY ROW: LABA / (RUGI) OPERASIONAL --}}
                    @php $opInc = $data['operating_income_details']; @endphp
                    <tr class="bg-sky-100/70 dark:bg-sky-950/60 text-gray-900 dark:text-white font-extrabold border-y-2 border-sky-300 dark:border-sky-700 text-xs sm:text-sm">
                        <td class="py-2.5 px-4 uppercase tracking-wider border-r border-sky-300 dark:border-sky-700">LABA / (RUGI) OPERASIONAL</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-sky-300 dark:border-sky-700">{{ $fmt($opInc['toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-sky-300 dark:border-sky-700">{{ $fmt($opInc['gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-sky-400 dark:border-sky-600 bg-sky-200/50 dark:bg-sky-900/60 font-black text-sm">{{ $fmt($opInc['total']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-sky-300 dark:border-sky-700">{{ $fmt($opInc['ytd_toko']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono border-r border-sky-300 dark:border-sky-700">{{ $fmt($opInc['ytd_gudang']) }}</td>
                        <td class="py-2.5 px-3 text-right font-mono bg-sky-200/30 dark:bg-sky-900/40 font-black text-sm">{{ $fmt($opInc['ytd_total']) }}</td>
                    </tr>

                    {{-- 4. PENDAPATAN & BEBAN LAIN-LAIN (IF ANY) --}}
                    @if(count($data['other_revenue']['items']) > 0 || count($data['other_expenses']['items']) > 0)
                        <tr class="bg-gray-100/80 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                            <td colspan="7" class="py-2 px-4 uppercase tracking-wider text-xs border-b border-gray-300 dark:border-gray-700">
                                PENDAPATAN & BEBAN LAIN-LAIN (OTHER REVENUE & EXPENSES)
                            </td>
                        </tr>
                        @foreach($data['other_revenue']['items'] as $item)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5">
                                <td class="py-2 px-4 border-r border-gray-200 dark:border-gray-800">
                                    <div class="flex items-center justify-between gap-2" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mr-1">{{ $item['code'] }}</span>
                                            <span>{{ $item['name'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_toko']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_gudang']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700 font-bold bg-sky-50/40 dark:bg-sky-950/30 text-gray-900 dark:text-white">{{ $fmt($item['balance']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_toko']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_gudang']) }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold bg-sky-50/20 dark:bg-sky-950/20 text-gray-900 dark:text-white">{{ $fmt($item['ytd_total']) }}</td>
                            </tr>
                        @endforeach
                        @foreach($data['other_expenses']['items'] as $item)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5">
                                <td class="py-2 px-4 border-r border-gray-200 dark:border-gray-800">
                                    <div class="flex items-center justify-between gap-2" style="padding-left: {{ max(0, ($item['level'] - 1) * 1) }}rem;">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400 mr-1">{{ $item['code'] }}</span>
                                            <span>{{ $item['name'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_toko']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['balance_gudang']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-300 dark:border-gray-700 font-bold bg-sky-50/40 dark:bg-sky-950/30 text-gray-900 dark:text-white">{{ $fmt($item['balance']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_toko']) }}</td>
                                <td class="py-2 px-3 text-right font-mono border-r border-gray-200 dark:border-gray-800 font-semibold">{{ $fmt($item['ytd_gudang']) }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold bg-sky-50/20 dark:bg-sky-950/20 text-gray-900 dark:text-white">{{ $fmt($item['ytd_total']) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- FINAL GRAND TOTAL ROW: LABA / (RUGI) BERSIH --}}
                    @php $netInc = $data['net_income_details']; @endphp
                    <tr class="bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white font-black border-2 border-gray-700 dark:border-gray-300 text-sm">
                        <td class="py-3 px-4 uppercase tracking-wider border-r border-gray-400 dark:border-gray-600">
                            {{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600">{{ $fmt($netInc['toko']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600">{{ $fmt($netInc['gudang']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-gray-500 dark:border-gray-500 bg-gray-300/70 dark:bg-gray-700/80 font-black text-base">{{ $fmt($netInc['total']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600">{{ $fmt($netInc['ytd_toko']) }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-gray-400 dark:border-gray-600">{{ $fmt($netInc['ytd_gudang']) }}</td>
                        <td class="py-3 px-3 text-right font-mono bg-gray-300/50 dark:bg-gray-700/50 font-black text-base">{{ $fmt($netInc['ytd_total']) }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    {{-- INTERACTIVE DRILL-DOWN TRANSACTION LEDGER MODAL DIALOG (MATCHING NATIVE FILAMENT THEME) --}}
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
                            Rincian Mutasi Jurnal & Buku Besar • Klasifikasi: {{ strtoupper($this->selectedAccount['classification']) }}
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
                                        <td class="py-2 px-3 text-right font-mono text-xs font-extrabold text-gray-900 dark:text-white">
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
                            <p class="text-xs text-gray-400 mt-1">Tidak ada transaksi jurnal ter-posting untuk akun ini pada periode terfilter.</p>
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
