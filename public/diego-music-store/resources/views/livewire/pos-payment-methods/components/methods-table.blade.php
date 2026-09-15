<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Name -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Metode Pembayaran
                </x-pos.table.th>
                <!-- Code -->
                <x-pos.table.th>
                    Kode
                </x-pos.table.th>
                <!-- Account relation -->
                <x-pos.table.th>
                    Akun Perkiraan (COA)
                </x-pos.table.th>
                <!-- Status -->
                <x-pos.table.th class="text-center">
                    Status
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($methods as $row)
                <x-pos.table.tr>
                    <!-- Name -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-semibold">
                        <div class="flex items-center gap-1.5">
                            @if ($row->parent)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 rounded text-[10px] font-bold border border-purple-200/50 dark:border-purple-800/40">
                                    <i class="ph-bold ph-git-branch text-[10px]"></i>
                                    {{ $row->parent->name }}
                                </span>
                            @elseif ($row->children->count() > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 rounded text-[10px] font-bold border border-blue-200/50 dark:border-blue-800/40">
                                    <i class="ph-bold ph-folders text-[10px]"></i>
                                    Parent Group
                                </span>
                            @endif
                            <span>{{ $row->name }}</span>
                        </div>
                    </x-pos.table.td>
                    <!-- Code -->
                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                        {{ $row->code }}
                    </x-pos.table.td>
                    <!-- Account -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-770 dark:text-slate-300 font-medium">
                        @if ($row->account)
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                                <i class="ph ph-file-text"></i>
                                {{ $row->account->code }} - {{ $row->account->name }}
                            </span>
                        @else
                            <span class="text-slate-405 dark:text-slate-600">-</span>
                        @endif
                    </x-pos.table.td>
                    <!-- Status -->
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($row->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-455 border border-rose-200/50 dark:border-rose-800/40">
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
                <x-pos.table.empty colspan="5" icon="ph-credit-card" message="Tidak ada metode pembayaran ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$methods" perPageModel="perPage" />
</x-pos.table.container>
