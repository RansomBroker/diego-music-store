<!-- Tabel Perbandingan Performa Seluruh Cabang (Entitas Bisnis Konsolidasi) -->
<div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="ph-bold ph-chart-bar text-blue-500"></i>
                Tabel Perbandingan Performa Seluruh Cabang
            </h3>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                Konsolidasi entitas bisnis: Omset, Nilai Stok, Piutang, dan Laba Bersih antar cabang
            </p>
        </div>

        <a href="{{ route('pos.reports.sales') }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl text-xs font-extrabold transition-all flex items-center gap-2">
            <span>Lihat Laporan ERP</span>
            <i class="ph-bold ph-arrow-right"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-700 text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-4">Nama Cabang / Store</th>
                    <th class="py-3 px-4 text-right">Omset Penjualan</th>
                    <th class="py-3 px-4 text-right">HPP (COGS)</th>
                    <th class="py-3 px-4 text-right">Laba Kotor</th>
                    <th class="py-3 px-4 text-right">Nilai Stok</th>
                    <th class="py-3 px-4 text-right">Piutang AR</th>
                    <th class="py-3 px-4 text-right">Laba Bersih</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                @foreach ($branchComparison as $bc)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors {{ $bc['id'] == $selectedBranchId ? 'bg-blue-50/40 dark:bg-blue-950/20' : '' }}">
                        <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                            <div>{{ $bc['name'] }}</div>
                            <div class="text-[10px] font-normal text-slate-400 dark:text-slate-500">{{ $bc['store_name'] }} • {{ $bc['city'] }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($bc['revenue'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($bc['cogs'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                            Rp {{ number_format($bc['gross_profit'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($bc['stock_value'], 0, ',', '.') }}
                            <div class="text-[10px] text-slate-400 font-normal">({{ number_format($bc['stock_items']) }} item)</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-purple-600 dark:text-purple-400">
                            Rp {{ number_format($bc['ar_unpaid'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black {{ $bc['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($bc['net_income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('pos.switch-branch', $bc['id']) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 text-slate-700 hover:text-white dark:bg-slate-700 dark:hover:bg-blue-600 dark:text-slate-200 rounded-xl text-[11px] font-bold transition-all inline-block">
                                Pilih Cabang
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
