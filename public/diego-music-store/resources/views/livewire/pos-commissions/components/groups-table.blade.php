{{-- ===================== TAB 4: KOMISI GRUP & OVERRIDE ===================== --}}
<div class="space-y-4">
    <!-- Toolbar Grup -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-sm">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-users-four text-primary"></i>
                Komisi Grup (Team Override Commission)
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Leader mendapatkan komisi dari akumulasi penjualan anggota grup jika <strong>seluruh anggota (100%)</strong> mencapai target penjualan bulanan masing-masing.
            </p>
        </div>

        <button
            type="button"
            wire:click="openGroupModal"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider text-white bg-primary hover:bg-primaryHover active:scale-[0.98] transition shadow-sm cursor-pointer whitespace-nowrap"
        >
            <i class="ph-bold ph-plus text-sm"></i>
            Tambah Grup Komisi
        </button>
    </div>

    <!-- Table Container -->
    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>Nama Grup</x-pos.table.th>
                    <x-pos.table.th>Leader Penerima</x-pos.table.th>
                    <x-pos.table.th>Anggota</x-pos.table.th>
                    <x-pos.table.th>Rate Komisi</x-pos.table.th>
                    <x-pos.table.th>Total Omzet Grup</x-pos.table.th>
                    <x-pos.table.th>Status Pembuka</x-pos.table.th>
                    <x-pos.table.th>Komisi Leader</x-pos.table.th>
                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($evaluatedGroups as $g)
                    <x-pos.table.tr>
                        <!-- Nama Grup -->
                        <x-pos.table.td class="whitespace-nowrap font-bold text-slate-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    <i class="ph-bold ph-users-four text-base"></i>
                                </div>
                                <div>
                                    <div class="text-sm">{{ $g['group_name'] }}</div>
                                    <div class="text-[11px] text-slate-400 font-normal">Periode: {{ $g['year'] }}-{{ str_pad($g['month'], 2, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </x-pos.table.td>

                        <!-- Leader -->
                        <x-pos.table.td class="whitespace-nowrap">
                            <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                {{ $g['leader_name'] }}
                            </div>
                        </x-pos.table.td>

                        <!-- Anggota Count -->
                        <x-pos.table.td class="whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                <i class="ph ph-user text-slate-400"></i>
                                {{ $g['total_members'] }} Anggota
                            </span>
                        </x-pos.table.td>

                        <!-- Rate Komisi -->
                        <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-800 dark:text-slate-200">
                            {{ number_format($g['rate'], 2) }}%
                        </x-pos.table.td>

                        <!-- Total Omzet Grup -->
                        <x-pos.table.td class="whitespace-nowrap font-bold text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($g['total_group_sales'], 0, ',', '.') }}
                        </x-pos.table.td>

                        <!-- Status Unlock -->
                        <x-pos.table.td class="whitespace-nowrap">
                            @if ($g['is_unlocked'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm">
                                    <i class="ph-bold ph-lock-key-open text-xs"></i>
                                    TERBUKA (100% Achieve)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800/40">
                                    <i class="ph-bold ph-lock-key text-xs"></i>
                                    TERKUNCI ({{ $g['achieved_members_count'] }}/{{ $g['total_members'] }} Tercapai)
                                </span>
                            @endif
                        </x-pos.table.td>

                        <!-- Komisi Leader -->
                        <x-pos.table.td class="whitespace-nowrap">
                            @if ($g['is_unlocked'])
                                <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($g['commission_amount'], 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-xs font-semibold text-slate-400">
                                    Rp 0 (Terkunci)
                                </span>
                            @endif
                        </x-pos.table.td>

                        <!-- Actions -->
                        <x-pos.table.td class="whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Tombol Lihat Detail Anggota -->
                                <button
                                    type="button"
                                    wire:click="openGroupDetailModal({{ $g['group_id'] }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition"
                                    title="Lihat Progres Target Anggota"
                                >
                                    <i class="ph-bold ph-chart-bar text-xs"></i>
                                    Progress
                                </button>

                                <!-- Tombol Klaim / Setujui Komisi -->
                                @if ($g['is_unlocked'] && $g['commission_amount'] > 0)
                                    <button
                                        type="button"
                                        wire:click="claimGroupCommission({{ $g['group_id'] }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                                        title="Klaim & Alokasikan Komisi Grup ke Slip Gaji"
                                    >
                                        <i class="ph-bold ph-check text-xs"></i>
                                        Klaim Komisi
                                    </button>
                                @endif

                                <x-pos.utility.button
                                    type="button"
                                    variant="warning"
                                    size="xs"
                                    icon="ph-pencil-simple"
                                    wire:click="openGroupModal({{ $g['group_id'] }})"
                                    title="Ubah Grup"
                                >
                                    Ubah
                                </x-pos.utility.button>

                                <x-pos.utility.button
                                    type="button"
                                    variant="danger"
                                    size="xs"
                                    icon="ph-trash"
                                    wire:click="deleteGroup({{ $g['group_id'] }})"
                                    title="Hapus Grup"
                                >
                                    Hapus
                                </x-pos.utility.button>
                            </div>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="8" icon="ph-users-four" message="Belum ada grup komisi yang dibuat di cabang ini." />
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>
</div>
