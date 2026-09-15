<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <x-pos.table.th sortable field="code" :sortField="$sortField" :sortDirection="$sortDirection">
                    Kode Voucher
                </x-pos.table.th>
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Voucher
                </x-pos.table.th>
                <x-pos.table.th>
                    Nilai Diskon
                </x-pos.table.th>
                <x-pos.table.th>
                    Min. Belanja
                </x-pos.table.th>
                <x-pos.table.th>
                    Kadaluarsa
                </x-pos.table.th>
                <x-pos.table.th class="text-center">
                    Penggunaan
                </x-pos.table.th>
                <x-pos.table.th class="text-center">
                    Status
                </x-pos.table.th>
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm">
            @forelse ($vouchers as $row)
                <x-pos.table.tr>
                    <x-pos.table.td class="whitespace-nowrap font-bold text-primary dark:text-blue-400">
                        <span class="px-2.5 py-1 bg-primary/10 dark:bg-blue-950/40 rounded-lg text-xs tracking-wider">
                            {{ $row->code }}
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-white">
                        {{ $row->name }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400">
                        @if ($row->type === 'percent')
                            {{ $row->value }}%
                        @else
                            Rp {{ number_format($row->value, 0, ',', '.') }}
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                        @if ($row->min_spend > 0)
                            Rp {{ number_format($row->min_spend, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-xs font-semibold text-slate-600 dark:text-slate-300">
                        @if ($row->valid_until)
                            {{ $row->valid_until->format('d M Y H:i') }}
                        @else
                            <span class="text-slate-400">Selamanya</span>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-center text-xs font-bold">
                        <span class="text-slate-800 dark:text-slate-200">{{ $row->used_count }}</span>
                        <span class="text-slate-400">/ {{ $row->max_uses ?? '∞' }}</span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($row->is_active)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50">
                                Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200/50">
                                Non-aktif
                            </span>
                        @endif
                    </x-pos.table.td>
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
                <x-pos.table.empty colspan="8" icon="ph-ticket" message="Tidak ada voucher ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$vouchers" perPageModel="perPage" />
</x-pos.table.container>
