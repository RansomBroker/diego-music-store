<!-- TAB 2: ATURAN & DENDA PELANGGARAN -->
@if ($activeTab === 'rules')
    <div class="space-y-4">
        <x-pos.table.container>
            <x-pos.table>
                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                    <tr>
                        <x-pos.table.th>Nama Aturan</x-pos.table.th>
                        <x-pos.table.th>Jenis Pelanggaran</x-pos.table.th>
                        <x-pos.table.th>Batas Menit</x-pos.table.th>
                        <x-pos.table.th>Tipe Denda / Potongan</x-pos.table.th>
                        <x-pos.table.th>Nominal Denda</x-pos.table.th>
                        <x-pos.table.th class="text-center">Status</x-pos.table.th>
                        <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                    @forelse ($rules as $r)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <x-pos.table.td>
                                <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $r->name }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td>
                                <x-pos.utility.pill :variant="$r->violation_type === 'late_in' ? 'warning' : 'primary'" size="xs">
                                    {{ $r->violation_type === 'late_in' ? 'Keterlambatan (Late In)' : ($r->violation_type === 'early_out' ? 'Pulang Cepat (Early Out)' : $r->violation_type) }}
                                </x-pos.utility.pill>
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ $r->min_minutes }}m - {{ $r->max_minutes ? $r->max_minutes . 'm' : 'Tanpa Batas' }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-slate-600 dark:text-slate-300 capitalize">
                                {{ str_replace('_', ' ', $r->deduction_type) }}
                            </x-pos.table.td>
                            <x-pos.table.td class="font-mono font-black text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($r->deduction_amount, 0, ',', '.') }}
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <button
                                    type="button"
                                    wire:click="toggleRuleStatus({{ $r->id }})"
                                    class="cursor-pointer"
                                >
                                    <x-pos.utility.pill :variant="$r->is_active ? 'success' : 'default'" size="xs">
                                        {{ $r->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </x-pos.utility.pill>
                                </button>
                            </x-pos.table.td>
                            <x-pos.table.td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <x-pos.utility.button
                                        type="button"
                                        variant="warning"
                                        size="sm"
                                        icon="ph-pencil-simple"
                                        wire:click="openRuleModal({{ $r->id }})"
                                        title="Edit Aturan"
                                    />
                                    <x-pos.utility.button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        icon="ph-trash"
                                        wire:click="deleteRule({{ $r->id }})"
                                        wire:confirm="Yakin ingin menghapus aturan denda ini?"
                                        title="Hapus Aturan"
                                    />
                                </div>
                            </x-pos.table.td>
                        </tr>
                    @empty
                        <x-pos.table.empty colspan="7" message="Belum ada aturan denda presensi terdaftar." />
                    @endforelse
                </tbody>
            </x-pos.table>
        </x-pos.table.container>

        <x-pos.table.footer :total="count($rules)" />
    </div>
@endif
