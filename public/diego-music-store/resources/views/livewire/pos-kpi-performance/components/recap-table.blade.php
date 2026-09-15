<!-- TAB 1: REKAP EVALUASI BULANAN & PENCATATAN BONUS -->
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

        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                Periode: <span class="font-mono text-slate-900 dark:text-slate-100 font-bold">{{ $filterMonth }}</span>
            </span>
            <x-pos.utility.pill variant="primary" size="sm">
                TOTAL: {{ $evaluations->total() }} KARYAWAN
            </x-pos.utility.pill>
        </div>
    </div>

    @if ($evaluations->isNotEmpty())
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Nama Karyawan</x-pos.table.th>
                        <x-pos.table.th>Cabang</x-pos.table.th>
                        <x-pos.table.th>Template KPI</x-pos.table.th>
                        <x-pos.table.th class="text-right text-blue-600 dark:text-blue-400"><span class="text-blue-600 dark:text-blue-400">Omset Sales</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-sky-600 dark:text-sky-400"><span class="text-sky-600 dark:text-sky-400">ATV (Rata²)</span></x-pos.table.th>
                        <x-pos.table.th class="text-center text-emerald-600 dark:text-emerald-400"><span class="text-emerald-600 dark:text-emerald-400">Absensi %</span></x-pos.table.th>
                        <x-pos.table.th class="text-center text-amber-600 dark:text-amber-400"><span class="text-amber-600 dark:text-amber-400">Tepat Waktu %</span></x-pos.table.th>
                        <x-pos.table.th class="text-center">Skor KPI</x-pos.table.th>
                        <x-pos.table.th class="text-right text-purple-600 dark:text-purple-400"><span class="text-purple-600 dark:text-purple-400">Bonus Cair</span></x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($evaluations as $eval)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $eval->employee->name ?? '-' }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $eval->employee->nik ?? '' }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-700 dark:text-slate-300 font-medium">
                                {{ $eval->branch->name ?? '-' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="font-medium text-slate-600 dark:text-slate-400">
                                {{ $eval->template->name ?? 'Default' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold font-mono text-blue-600 dark:text-blue-400">
                                <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($eval->actual_sales_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold font-mono text-sky-600 dark:text-sky-400">
                                <span class="text-sky-600 dark:text-sky-400">Rp {{ number_format($eval->actual_atv_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="text-emerald-600 dark:text-emerald-400">{{ $eval->actual_attendance_pct }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-amber-600 dark:text-amber-400">
                                <span class="text-amber-600 dark:text-amber-400">{{ $eval->actual_punctuality_pct }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <x-pos.utility.pill
                                    :variant="$eval->final_kpi_score >= 90 ? 'success' : ($eval->final_kpi_score >= 75 ? 'primary' : 'danger')"
                                    size="sm"
                                >
                                    {{ $eval->final_kpi_score }}%
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-black font-mono text-purple-600 dark:text-purple-400">
                                <span class="text-purple-600 dark:text-purple-400 font-black">Rp {{ number_format($eval->earned_bonus_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
            </x-pos.table>
        </x-pos.table.container>

        <x-pos.table.footer :paginator="$evaluations" />
    @else
        <div class="p-12 text-center text-slate-400">
            <i class="ph-bold ph-chart-line-up text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-bold mb-3">Belum ada data evaluasi KPI untuk periode {{ $filterMonth }}.</p>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-arrows-clockwise"
                wire:click="recalculateAllKpi()"
            >
                Kalkulasi Realtime Sekarang
            </x-pos.utility.button>
        </div>
    @endif
</div>
