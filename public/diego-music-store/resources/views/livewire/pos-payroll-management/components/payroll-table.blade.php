<!-- Table Card Wrapper -->
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
                    size="sm"
                >
                    <option value="">Semua Cabang</option>
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

        @if ($currentPayroll)
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    Kode: <span class=" text-slate-900 dark:text-slate-100 font-bold">{{ $currentPayroll->payroll_code }}</span>
                </span>
                <x-pos.utility.pill
                    :variant="$currentPayroll->status === 'paid' ? 'success' : ($currentPayroll->status === 'draft' ? 'warning' : 'primary')"
                    size="sm"
                >
                    STATUS: {{ strtoupper($currentPayroll->status) }}
                </x-pos.utility.pill>
            </div>
        @endif
    </div>

    @if ($currentPayroll && $currentPayroll->items->isNotEmpty())
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Nama Karyawan</x-pos.table.th>
                        <x-pos.table.th class="text-right text-slate-700 dark:text-slate-300"><span class="text-slate-700 dark:text-slate-300">Gaji Pokok</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-emerald-600 dark:text-emerald-400"><span class="text-emerald-600 dark:text-emerald-400">Tunjangan</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-sky-600 dark:text-sky-400"><span class="text-sky-600 dark:text-sky-400">Tunj. Lembur</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-blue-600 dark:text-blue-400"><span class="text-blue-600 dark:text-blue-400">Komisi Sales</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-purple-600 dark:text-purple-400"><span class="text-purple-600 dark:text-purple-400">Bonus KPI</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-rose-600 dark:text-rose-400"><span class="text-rose-600 dark:text-rose-400">Denda Presensi</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-amber-600 dark:text-amber-400"><span class="text-amber-600 dark:text-amber-400">Potongan Lain</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-emerald-700 dark:text-emerald-300"><span class="text-emerald-700 dark:text-emerald-300">Gaji Bersih</span></x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($currentPayroll->items as $item)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $item->employee->name ?? '-' }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $item->employee->nik ?? '' }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-slate-700 dark:text-slate-300">
                                <span class="text-slate-700 dark:text-slate-300">Rp {{ number_format($item->basic_salary, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-emerald-600 dark:text-emerald-400">
                                <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->allowance_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-sky-600 dark:text-sky-400">
                                <span class="text-sky-600 dark:text-sky-400">Rp {{ number_format($item->overtime_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-blue-600 dark:text-blue-400">
                                <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($item->commission_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-purple-600 dark:text-purple-400">
                                <span class="text-purple-600 dark:text-purple-400">Rp {{ number_format($item->kpi_bonus_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-rose-600 dark:text-rose-400">
                                <span class="text-rose-600 dark:text-rose-400">Rp {{ number_format($item->violation_deduction_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold  text-amber-600 dark:text-amber-400">
                                <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($item->other_deduction_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-black  text-emerald-700 dark:text-emerald-300">
                                <span class="text-emerald-700 dark:text-emerald-300 font-black">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <!-- Preview Modal Button -->
                                    <x-pos.utility.button
                                        type="button"
                                        variant="info"
                                        size="xs"
                                        icon="ph-eye"
                                        wire:click="openPreviewModal({{ $item->id }})"
                                        title="Preview Slip & Detail Lembur"
                                    >
                                        Detail
                                    </x-pos.utility.button>

                                    <!-- Edit / Override Komponen Gaji -->
                                    <x-pos.utility.button
                                        type="button"
                                        variant="warning"
                                        size="xs"
                                        icon="ph-pencil-simple"
                                        wire:click="openEditItemModal({{ $item->id }})"
                                        title="Edit & Override Komponen Gaji"
                                    >
                                        Edit
                                    </x-pos.utility.button>

                                    <!-- Cetak Slip Gaji PDF -->
                                    <x-pos.utility.button
                                        href="{{ route('pos.payroll.payslip-pdf', $item->id) }}"
                                        target="_blank"
                                        variant="primary"
                                        size="xs"
                                        icon="ph-printer"
                                        title="Cetak Slip Gaji PDF"
                                    >
                                        Slip PDF
                                    </x-pos.utility.button>
                                </div>
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100/90 dark:bg-slate-800/90 font-extrabold text-xs border-t-2 border-slate-300 dark:border-slate-700">
                    <tr>
                        <x-pos.table.td class="font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                            TOTAL ({{ $currentPayroll->items->count() }} STAF)
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-slate-800 dark:text-slate-200">
                            <span class="text-slate-800 dark:text-slate-200">Rp {{ number_format($currentPayroll->items->sum('basic_salary'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-emerald-600 dark:text-emerald-400">
                            <span class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($currentPayroll->items->sum('allowance_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-sky-600 dark:text-sky-400">
                            <span class="text-sky-600 dark:text-sky-400">Rp {{ number_format($currentPayroll->items->sum('overtime_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-blue-600 dark:text-blue-400">
                            <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($currentPayroll->items->sum('commission_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-purple-600 dark:text-purple-400">
                            <span class="text-purple-600 dark:text-purple-400">Rp {{ number_format($currentPayroll->items->sum('kpi_bonus_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-rose-600 dark:text-rose-400">
                            <span class="text-rose-600 dark:text-rose-400">Rp {{ number_format($currentPayroll->items->sum('violation_deduction_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-amber-600 dark:text-amber-400">
                            <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($currentPayroll->items->sum('other_deduction_amount'), 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right font-black  text-emerald-700 dark:text-emerald-300">
                            <span class="text-emerald-700 dark:text-emerald-300 font-black text-sm">Rp {{ number_format($currentPayroll->total_net_salary, 0, ',', '.') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-center text-slate-400 font-normal">
                            -
                        </x-pos.table.td>
                    </tr>
                </tfoot>
            </x-pos.table>
            <x-pos.table.footer :total="count($currentPayroll->items)" />
        </x-pos.table.container>
    @else
        <div class="p-12 text-center text-slate-400">
            <i class="ph-bold ph-bank text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-bold mb-3">Belum ada rekapitulasi payroll untuk periode {{ $filterMonth }}.</p>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-arrows-clockwise"
                wire:click="generatePayroll()"
            >
                Proses Payroll Sekarang
            </x-pos.utility.button>
        </div>
    @endif
</div>
