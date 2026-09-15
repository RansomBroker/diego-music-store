<!-- ========================================================================= -->
<!-- TAB 3: LAPORAN PELUNASAN PIUTANG (AR SETTLEMENT)                         -->
<!-- ========================================================================= -->
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">Total Pelunasan Piutang Diterima</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['total_settled'] ?? 0, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['total_count'] ?? 0 }} Transaksi Pelunasan</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
            Riwayat Penerimaan & Pelunasan Piutang Pelanggan
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                    <tr>
                        <x-pos.table.th>No Bukti / Jurnal</x-pos.table.th>
                        <x-pos.table.th>Tanggal Pelunasan</x-pos.table.th>
                        <x-pos.table.th>Pelanggan</x-pos.table.th>
                        <x-pos.table.th>No Invoice Terkait</x-pos.table.th>
                        <x-pos.table.th>Akun Masuk (Kas/Bank)</x-pos.table.th>
                        <x-pos.table.th class="text-right">Jumlah Pelunasan</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($reportData['settlements'] ?? [] as $st)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $st['entry_no'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $st['date'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">{{ $st['customer_name'] }}</x-pos.table.td>
                            <x-pos.table.td class="font-mono text-xs font-bold text-primary dark:text-blue-400">{{ $st['invoice_no'] }}</x-pos.table.td>
                            <x-pos.table.td class="text-xs text-slate-700 dark:text-slate-300"><span class="bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded font-bold">{{ $st['account_name'] }}</span></x-pos.table.td>
                            <x-pos.table.td class="text-right font-bold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($st['amount'], 0, ',', '.') }}</x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="6" icon="ph-hand-coins" message="Belum ada transaksi pelunasan piutang pada periode ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
        </x-pos.table.container>
    </div>
</div>
