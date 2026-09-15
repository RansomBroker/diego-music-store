<!-- Tabel Perbandingan Performa Seluruh Cabang (Entitas Bisnis Konsolidasi) -->
<div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="ph-bold ph-chart-bar text-primary"></i>
                Tabel Perbandingan Performa Seluruh Cabang
            </h3>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                Konsolidasi entitas bisnis: Omset, Nilai Stok, Piutang, dan Laba Bersih antar cabang
            </p>
        </div>

        <x-pos.utility.button href="{{ route('pos.reports.sales') }}" variant="info" size="sm" icon="ph-arrow-right">
            Lihat Laporan ERP
        </x-pos.utility.button>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    <x-pos.table.th>Nama Cabang / Store</x-pos.table.th>
                    <x-pos.table.th class="text-right">Omset Penjualan</x-pos.table.th>
                    <x-pos.table.th class="text-right">HPP (COGS)</x-pos.table.th>
                    <x-pos.table.th class="text-right">Laba Kotor</x-pos.table.th>
                    <x-pos.table.th class="text-right">Nilai Stok</x-pos.table.th>
                    <x-pos.table.th class="text-right">Piutang AR</x-pos.table.th>
                    <x-pos.table.th class="text-right">Laba Bersih</x-pos.table.th>
                    <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse ($branchComparison ?? [] as $bc)
                    <x-pos.table.tr class="{{ $bc['id'] == $selectedBranchId ? 'bg-blue-50/40 dark:bg-blue-950/20' : '' }}">
                        <x-pos.table.td class="font-bold text-slate-800 dark:text-slate-200">
                            <div>{{ $bc['name'] }}</div>
                            <div class="text-[10px] font-normal text-slate-400 dark:text-slate-500">{{ $bc['store_name'] }} • {{ $bc['city'] }}</div>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($bc['revenue'], 0, ',', '.') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-bold text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($bc['cogs'], 0, ',', '.') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-bold text-slate-800 dark:text-slate-200">
                            Rp {{ number_format($bc['gross_profit'], 0, ',', '.') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($bc['stock_value'], 0, ',', '.') }}
                            <div class="text-[10px] text-slate-400 font-normal">({{ number_format($bc['stock_items']) }} item)</div>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-bold text-purple-600 dark:text-purple-400">
                            Rp {{ number_format($bc['ar_unpaid'], 0, ',', '.') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black {{ $bc['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            Rp {{ number_format($bc['net_income'], 0, ',', '.') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="text-center">
                            <x-pos.utility.button href="{{ route('pos.switch-branch', $bc['id']) }}" variant="primary" size="xs">
                                Pilih Cabang
                            </x-pos.utility.button>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="8" icon="ph-buildings" message="Belum ada data cabang." />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :total="count($branchComparison ?? [])" />
    </x-pos.table.container>
</div>
