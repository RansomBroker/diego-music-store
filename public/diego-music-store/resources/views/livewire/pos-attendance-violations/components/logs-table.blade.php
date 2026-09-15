<!-- TAB 3: LOG DETAIL PELANGGARAN -->
@if ($activeTab === 'logs')
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700/60">
            <div class="w-full sm:w-64">
                <x-pos.form.dropdown
                    model="selectedEmployeeId"
                    :live="true"
                    icon="ph-user"
                    size="sm"
                    placeholder="Semua Staf Karyawan"
                >
                    @foreach ($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} (NIK: {{ $e->nik }})</option>
                    @endforeach
                </x-pos.form.dropdown>
            </div>

            @if (count($selectedLogIds) > 0)
                <x-pos.utility.button
                    type="button"
                    variant="success"
                    size="sm"
                    icon="ph-check-square"
                    wire:click="bulkApproveLogs()"
                >
                    Setujui {{ count($selectedLogIds) }} Log Terpilih ke Payroll
                </x-pos.utility.button>
            @endif
        </div>

        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                    <tr>
                        <th class="w-10 text-center px-4 py-3">
                            <input type="checkbox" disabled class="rounded border-slate-300" />
                        </th>
                        <x-pos.table.th>Tanggal & Karyawan</x-pos.table.th>
                        <x-pos.table.th>Jenis Pelanggaran</x-pos.table.th>
                        <x-pos.table.th>Durasi Menit</x-pos.table.th>
                        <x-pos.table.th>Nominal Denda (Rp)</x-pos.table.th>
                        <x-pos.table.th>Catatan</x-pos.table.th>
                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi Approval</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                    @forelse ($logs as $l)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="text-center px-4 py-3">
                                <input
                                    type="checkbox"
                                    value="{{ $l->id }}"
                                    wire:model.live="selectedLogIds"
                                    class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4"
                                />
                            </td>
                            <x-pos.table.td>
                                <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $l->employee?->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $l->date->format('d M Y') }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td>
                                <x-pos.utility.pill :variant="$l->violation_type === 'late_in' ? 'warning' : 'primary'" size="xs">
                                    {{ $l->violation_type === 'late_in' ? 'Terlambat Masuk' : 'Pulang Cepat' }}
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $l->late_early_minutes }} Menit
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono font-black text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($l->deduction_amount, 0, ',', '.') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-500 max-w-xs truncate">
                                {{ $l->notes ?: '-' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                @if ($l->status === 'approved')
                                    <x-pos.utility.pill variant="success" size="xs">
                                        Disetujui
                                    </x-pos.utility.pill>
                                @elseif ($l->status === 'waived')
                                    <x-pos.utility.pill variant="primary" size="xs">
                                        Waived (Dihapus)
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="warning" size="xs">
                                        Pending
                                    </x-pos.utility.pill>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                @if ($l->status === 'pending')
                                    <div class="inline-flex items-center justify-center gap-1">
                                        <x-pos.utility.button
                                            type="button"
                                            variant="success"
                                            size="sm"
                                            wire:click="approveLog({{ $l->id }})"
                                        >
                                            Approve
                                        </x-pos.utility.button>
                                        <x-pos.utility.button
                                            type="button"
                                            variant="warning"
                                            size="sm"
                                            wire:click="waiveLog({{ $l->id }})"
                                        >
                                            Waive
                                        </x-pos.utility.button>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-400 font-mono">Selesai</span>
                                @endif
                            </x-pos.table.td>
                        </tr>
                    @empty
                        <x-pos.table.empty colspan="8" message="Belum ada log pelanggaran presensi pada periode bulan ini." />
                    @endforelse
                </tbody>
            </x-pos.table>
        </x-pos.table.container>

        <x-pos.table.footer :paginator="$logs" />
    </div>
@endif
