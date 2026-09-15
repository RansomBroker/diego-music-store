<!-- TAB 3: KELOLA TEMPLATE KPI PER JABATAN / USER -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <!-- Card Toolbar Header -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div>
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">
                Master Template KPI & Penetapan Target Insentif
            </h3>
            <p class="text-xs text-slate-400">Aturan bobot performa dan batas maksimum bonus per posisi/karyawan</p>
        </div>
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-plus-circle"
            wire:click="openTemplateModal()"
        >
            Buat Template KPI Baru
        </x-pos.utility.button>
    </div>

    @if ($templates->isNotEmpty())
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-pos.table.th>Nama Template</x-pos.table.th>
                        <x-pos.table.th>Target Jabatan / User</x-pos.table.th>
                        <x-pos.table.th class="text-right text-blue-600 dark:text-blue-400"><span class="text-blue-600 dark:text-blue-400">Target Sales</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-sky-600 dark:text-sky-400"><span class="text-sky-600 dark:text-sky-400">Target ATV</span></x-pos.table.th>
                        <x-pos.table.th class="text-center text-emerald-600 dark:text-emerald-400"><span class="text-emerald-600 dark:text-emerald-400">Target Absensi</span></x-pos.table.th>
                        <x-pos.table.th class="text-center text-amber-600 dark:text-amber-400"><span class="text-amber-600 dark:text-amber-400">Target Tepat Waktu</span></x-pos.table.th>
                        <x-pos.table.th class="text-right text-purple-600 dark:text-purple-400"><span class="text-purple-600 dark:text-purple-400">Max Bonus</span></x-pos.table.th>
                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach ($templates as $tpl)
                        <x-pos.table.tr>
                            <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $tpl->name }}
                            </x-pos.table.td>
                            <x-pos.table.td class="font-medium text-slate-700 dark:text-slate-300">
                                @if ($tpl->employee)
                                    <span class="bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold text-[11px]">
                                        Khusus: {{ $tpl->employee->name }}
                                    </span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold text-[11px]">
                                        Jabatan: {{ $tpl->position ?: 'Semua Jabatan' }}
                                    </span>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold font-mono text-blue-600 dark:text-blue-400">
                                <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($tpl->target_sales_amount, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_sales }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-semibold font-mono text-sky-600 dark:text-sky-400">
                                <span class="text-sky-600 dark:text-sky-400">Rp {{ number_format($tpl->target_atv_amount, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_atv }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="text-emerald-600 dark:text-emerald-400">{{ $tpl->target_attendance_pct }}%</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_attendance }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center font-bold text-amber-600 dark:text-amber-400">
                                <span class="text-amber-600 dark:text-amber-400">{{ $tpl->target_punctuality_pct }}%</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_punctuality }}%</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-right font-black font-mono text-purple-600 dark:text-purple-400">
                                <span class="text-purple-600 dark:text-purple-400 font-black">Rp {{ number_format($tpl->max_bonus_amount, 0, ',', '.') }}</span>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <x-pos.utility.pill
                                    :variant="$tpl->is_active ? 'success' : 'default'"
                                    size="sm"
                                >
                                    {{ $tpl->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <button
                                    type="button"
                                    wire:click="openTemplateModal({{ $tpl->id }})"
                                    class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-bold text-[11px] rounded-lg transition inline-flex items-center gap-1 cursor-pointer"
                                    title="Edit Template KPI"
                                >
                                    <i class="ph-bold ph-pencil-simple"></i> Edit
                                </button>
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @endforeach
                </tbody>
            </x-pos.table>
        </x-pos.table.container>
    @else
        <div class="p-12 text-center text-slate-400">
            <i class="ph-bold ph-sliders-horizontal text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-bold mb-3">Belum ada master template KPI.</p>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-plus-circle"
                wire:click="openTemplateModal()"
            >
                Buat Template KPI Pertama
            </x-pos.utility.button>
        </div>
    @endif
</div>
