<!-- ========================================================================= -->
<!-- TAB 2: LAPORAN PIUTANG (ACCOUNTS RECEIVABLE / AR AGING)                   -->
<!-- ========================================================================= -->
<div class="space-y-6">
    <!-- AR Aging Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm col-span-2 md:col-span-1">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total Saldo Piutang</span>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_outstanding'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['count_invoices'] ?? 0 }} Invoice Belum Lunas</span>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/40 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">0 - 30 Hari (Lancar)</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_0_30'] ?? 0, 0, ',', '.') }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">31 - 60 Hari</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_31_60'] ?? 0, 0, ',', '.') }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/40 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase">61 - 90 Hari</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_61_90'] ?? 0, 0, ',', '.') }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/40 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase">> 90 Hari (Menunggak)</span>
            <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_over_90'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- AR Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
            <span>Rincian Saldo Piutang Usaha per Pelanggan</span>
            <span class="text-xs text-slate-400 font-normal">Standard ERP AR Aging Schedule</span>
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>No Invoice</x-pos.table.th>
                        <x-pos.table.th>Tgl Invoice</x-pos.table.th>
                        <x-pos.table.th>Jatuh Tempo</x-pos.table.th>
                        <x-pos.table.th class="text-right">Total Inv</x-pos.table.th>
                        <x-pos.table.th class="text-right">Sudah Dibayar</x-pos.table.th>
                        <x-pos.table.th class="text-right">Sisa Piutang</x-pos.table.th>
                        <x-pos.table.th class="text-center">Umur (Hari)</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @php
                        $displayItems = $reportData['paginated_items'] ?? ($reportData['items'] ?? []);
                    @endphp
                    @forelse ($displayItems as $ar)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">{{ $ar['customer_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-mono text-xs font-bold text-primary dark:text-blue-400">{{ $ar['invoice_number'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $ar['invoice_date'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $ar['due_date'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold text-xs text-slate-900 dark:text-white">Rp {{ number_format($ar['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold text-xs text-emerald-600">Rp {{ number_format($ar['paid_amount'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($ar['outstanding'], 0, ',', '.') }}</x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $ar['age_days'] <= 30 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40' : ($ar['age_days'] <= 60 ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40') }}">
                                    {{ $ar['age_days'] }} Hari ({{ $ar['aging_group'] }})
                                </span>
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="8" icon="ph-check-circle" message="Tidak ada piutang aktif yang menunggak saat ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :paginator="$reportData['paginated_items'] ?? null" :total="count($reportData['items'] ?? [])" perPageModel="perPage" />
        </x-pos.table.container>
    </div>
</div>
