<!-- TAB 1: REKAP POTONGAN PRESENSI PER KARYAWAN -->
@if ($activeTab === 'recap')
    <div class="space-y-4">
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                    <tr>
                        <x-pos.table.th>Staf Karyawan</x-pos.table.th>
                        <x-pos.table.th>Terlambat (Menit)</x-pos.table.th>
                        <x-pos.table.th>Pulang Cepat (Menit)</x-pos.table.th>
                        <x-pos.table.th>Total Denda (Rp)</x-pos.table.th>
                        <x-pos.table.th>Disetujui Payroll (Rp)</x-pos.table.th>
                        <x-pos.table.th class="text-center">Status Waiver / Payroll</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                    @forelse ($recapData as $row)
                        <x-pos.table.tr>
                            <x-pos.table.td>
                                <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $row['employee']->name }}</div>
                                <div class="text-[10px]  text-slate-400">NIK: {{ $row['employee']->nik }} &bull; {{ $row['employee']->branch?->name ?: '-' }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td class=" font-bold text-amber-600 dark:text-amber-400">
                                {{ number_format($row['late_minutes']) }}m
                            </x-pos.table.td>
                            <x-pos.table.td class=" font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format($row['early_minutes']) }}m
                            </x-pos.table.td>
                            <x-pos.table.td class=" font-black text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($row['total_deduction'], 0, ',', '.') }}
                            </x-pos.table.td>
                            <x-pos.table.td class=" font-black text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($row['approved_deduction'], 0, ',', '.') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                @if ($row['approved_deduction'] > 0)
                                    <x-pos.utility.pill variant="success" size="xs">
                                        Siap ke Slip Gaji
                                    </x-pos.utility.pill>
                                @elseif ($row['pending_count'] > 0)
                                    <x-pos.utility.pill variant="warning" size="xs">
                                        {{ $row['pending_count'] }} Menunggu Approval
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="default" size="xs">
                                        Tidak Ada Potongan
                                    </x-pos.utility.pill>
                                @endif
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="6" icon="ph-user-list" message="Belum ada data presensi & denda pada periode bulan ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :total="count($recapData ?? [])" />
        </x-pos.table.container>
    </div>
@endif
