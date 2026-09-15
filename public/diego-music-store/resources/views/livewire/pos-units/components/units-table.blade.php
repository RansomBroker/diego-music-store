<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Unit Name -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Satuan
                </x-pos.table.th>
                <!-- Unit Code -->
                <x-pos.table.th sortable field="code" :sortField="$sortField" :sortDirection="$sortDirection">
                    Kode Satuan
                </x-pos.table.th>
                <!-- Base Unit -->
                <x-pos.table.th>
                    Satuan Dasar
                </x-pos.table.th>
                <!-- Conversion Factor -->
                <x-pos.table.th class="text-center">
                    Faktor Konversi
                </x-pos.table.th>
                <!-- Status -->
                <x-pos.table.th>
                    Status
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($units as $row)
                <x-pos.table.tr>
                    <!-- Unit Name -->
                    <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100 text-sm">
                        {{ $row->name }}
                    </x-pos.table.td>
                    <!-- Unit Code -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 font-semibold uppercase">
                        {{ $row->code }}
                    </x-pos.table.td>
                    <!-- Base Unit -->
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $row->baseUnit->name ?? '-' }}
                    </x-pos.table.td>
                    <!-- Conversion Factor -->
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($row->base_unit_id)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60  text-xs">
                                1 {{ $row->code }} = {{ $row->conversion_factor }} {{ $row->baseUnit->code ?? '' }}
                            </span>
                        @else
                            <span class="text-slate-350 dark:text-slate-650 font-normal">-</span>
                        @endif
                    </x-pos.table.td>
                    <!-- Status Badge -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($row->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-950/45 dark:text-emerald-450 dark:border-emerald-900/40">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700/60">
                                Non-aktif
                            </span>
                        @endif
                    </x-pos.table.td>
                    <!-- Actions -->
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <x-pos.utility.button
                                type="button"
                                variant="warning"
                                size="xs"
                                icon="ph-pencil-simple"
                                wire:click="openEdit({{ $row->id }})"
                                title="Ubah"
                            >
                                Ubah
                            </x-pos.utility.button>
                            <x-pos.utility.button
                                type="button"
                                variant="danger"
                                size="xs"
                                icon="ph-trash"
                                wire:click="confirmDelete({{ $row->id }})"
                                title="Hapus"
                            >
                                Hapus
                            </x-pos.utility.button>
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="6" icon="ph-ruler" message="Tidak ada satuan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$units" perPageModel="perPage" />
</x-pos.table.container>
