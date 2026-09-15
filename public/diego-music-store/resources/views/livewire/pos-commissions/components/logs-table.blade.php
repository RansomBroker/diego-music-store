<!-- TAB 3: LOG TRANSAKSI KOMISI -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <!-- Card Toolbar Header -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="ph-bold ph-list-bullets text-primary text-base"></i>
                Log Transaksi Komisi Per Penjualan
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Riwayat pencatatan komisi otomatis dari setiap transaksi POS</p>
        </div>

        <div class="w-full sm:w-64">
            <x-pos.form.dropdown
                model="selectedEmployeeId"
                :live="true"
                icon="ph-user"
                placeholder="Semua Staf Karyawan"
                size="sm"
            >
                @foreach ($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->nik }})</option>
                @endforeach
            </x-pos.form.dropdown>
        </div>
    </div>

    @if ($logs->isNotEmpty())
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Tanggal</x-pos.table.th>
                        <x-pos.table.th>Karyawan</x-pos.table.th>
                        <x-pos.table.th>Transaksi ID</x-pos.table.th>
                        <x-pos.table.th class="text-right text-blue-600 dark:text-blue-400"><span class="text-blue-600 dark:text-blue-400">Nilai Penjualan</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-emerald-600 dark:text-emerald-400"><span class="text-emerald-600 dark:text-emerald-400">Komisi Earned</span></x-pos.table.th>
                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                        <x-pos.table.th>Catatan</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($logs as $log)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-mono text-slate-600 dark:text-slate-400 font-normal">
                                {{ $log->date->format('d/m/Y') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ $log->employee?->name ?: '-' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono text-slate-600 dark:text-slate-400 font-normal">
                                #{{ $log->sale_id ?: '-' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-medium text-slate-700 dark:text-slate-300">
                                <span class="text-slate-700 dark:text-slate-300">Rp {{ number_format($log->sale_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($log->commission_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <x-pos.utility.pill
                                    :variant="$log->status === 'approved' ? 'primary' : ($log->status === 'paid' ? 'success' : 'warning')"
                                    size="sm"
                                >
                                    {{ ucfirst($log->status) }}
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-500 font-normal text-[11px] max-w-xs truncate">
                                {{ $log->notes ?: '-' }}
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :paginator="$logs" />
        </x-pos.table.container>
    @else
        <div class="p-12 text-center text-slate-400 dark:text-slate-500">
            <i class="ph ph-list-bullets text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-medium">Belum ada log transaksi komisi untuk periode filter ini.</p>
        </div>
    @endif
</div>
