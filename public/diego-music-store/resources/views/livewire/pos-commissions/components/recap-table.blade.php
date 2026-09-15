<!-- TAB 1: REKAP KOMISI SALES PER KARYAWAN -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <!-- Toolbar Filters (Branch & Month) -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Filter Cabang -->
            <div class="w-full sm:w-48">
                <x-pos.form.dropdown
                    model="filterBranchId"
                    :live="true"
                    icon="ph-buildings"
                    placeholder="Semua Cabang"
                    size="sm"
                >
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </x-pos.form.dropdown>
            </div>

            <!-- Filter Bulan -->
            <div class="w-full sm:w-40">
                <x-pos.form.input
                    type="month"
                    model="filterMonth"
                    :live="true"
                    icon="ph-calendar"
                    size="sm"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                Periode: <span class="font-mono text-slate-900 dark:text-slate-100 font-semibold">{{ \Carbon\Carbon::parse($filterMonth . '-01')->translatedFormat('F Y') }}</span>
            </span>
            <x-pos.utility.pill variant="primary" size="sm">
                TOTAL: {{ count($recapData) }} STAF
            </x-pos.utility.pill>
        </div>
    </div>

    @if (!empty($recapData))
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Karyawan / Sales</x-pos.table.th>
                        <x-pos.table.th>Cabang</x-pos.table.th>
                        <x-pos.table.th class="text-right text-blue-600 dark:text-blue-400"><span class="text-blue-600 dark:text-blue-400">Total Penjualan</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-emerald-600 dark:text-emerald-400"><span class="text-emerald-600 dark:text-emerald-400">Total Komisi</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-purple-600 dark:text-purple-400"><span class="text-purple-600 dark:text-purple-400">Disetujui (Approved)</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-amber-600 dark:text-amber-400"><span class="text-amber-600 dark:text-amber-400">Menunggu Approval</span></x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi Approval</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($recapData as $row)
                        <x-pos.table.tr>
                            <x-pos.table.td>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $row['employee']->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400 font-normal">NIK: {{ $row['employee']->nik }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-600 dark:text-slate-400 font-normal">
                                {{ $row['employee']->branch?->name ?: 'Cabang Utama' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-medium text-slate-700 dark:text-slate-300">
                                <span class="text-slate-700 dark:text-slate-300">Rp {{ number_format($row['sales_total'], 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($row['commission_total'], 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-medium text-purple-600 dark:text-purple-400">
                                <span class="text-purple-600 dark:text-purple-400">Rp {{ number_format($row['approved_total'], 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-mono font-medium text-amber-600 dark:text-amber-400">
                                <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($row['pending_total'], 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                @if ($row['pending_count'] > 0)
                                    <x-pos.utility.button
                                        type="button"
                                        variant="success"
                                        size="sm"
                                        icon="ph-check"
                                        wire:click="approveEmployeeRecap({{ $row['employee']->id }})"
                                        title="Setujui {{ $row['pending_count'] }} komisi pending"
                                    >
                                        Approve ({{ $row['pending_count'] }})
                                    </x-pos.utility.button>
                                @else
                                    <x-pos.utility.pill variant="success" size="sm">
                                        <i class="ph-bold ph-check-circle mr-1"></i>
                                        Semua Approved
                                    </x-pos.utility.pill>
                                @endif
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :total="count($recapData)" />
        </x-pos.table.container>
    @else
        <div class="p-12 text-center text-slate-400 dark:text-slate-500">
            <i class="ph ph-users text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-medium">Belum ada data staf karyawan pada cabang / periode ini.</p>
        </div>
    @endif
</div>
