<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Key -->
                <x-pos.table.th sortable field="key" :sortField="$sortField" :sortDirection="$sortDirection">
                    Key / Code
                </x-pos.table.th>
                <!-- Name -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Label / Kategori
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($labels as $row)
                <x-pos.table.tr>
                    <!-- Key -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-750 dark:text-slate-200 ">
                        {{ $row->key }}
                    </x-pos.table.td>
                    <!-- Name -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-semibold">
                        {{ $row->name }}
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
                <x-pos.table.empty colspan="3" icon="ph-tag" message="Tidak ada kategori penjualan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$labels" perPageModel="perPage" />
</x-pos.table.container>
