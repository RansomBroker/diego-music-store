<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Name -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Kategori
                </x-pos.table.th>
                <!-- Prefix -->
                <x-pos.table.th sortable field="prefix" :sortField="$sortField" :sortDirection="$sortDirection">
                    Prefix
                </x-pos.table.th>
                <!-- Range Abjad -->
                <x-pos.table.th>
                    Range Abjad
                </x-pos.table.th>
                <!-- Panjang Angka -->
                <x-pos.table.th sortable field="digit_length" :sortField="$sortField" :sortDirection="$sortDirection">
                    Panjang Angka
                </x-pos.table.th>
                <!-- Default -->
                <x-pos.table.th sortable field="is_default" :sortField="$sortField" :sortDirection="$sortDirection" class="text-center">
                    Default
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($categories as $row)
                <x-pos.table.tr>
                    <!-- Name -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-semibold">
                        {{ $row->name }}
                    </x-pos.table.td>
                    <!-- Prefix -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-700 dark:text-slate-300 font-bold">
                        {{ $row->prefix ?? '-' }}
                    </x-pos.table.td>
                    <!-- Range Abjad -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-700 dark:text-slate-300 font-bold uppercase">
                        {{ $row->start_alphabet ?? 'A' }} - {{ $row->end_alphabet ?? 'Z' }}
                    </x-pos.table.td>
                    <!-- Panjang Angka -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                        {{ $row->digit_length ?? 4 }} Digit
                    </x-pos.table.td>
                    <!-- Default Switch -->
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if (\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.sale_categories.edit'))
                            <button
                                type="button"
                                role="switch"
                                aria-checked="{{ $row->is_default ? 'true' : 'false' }}"
                                wire:click="toggleDefault({{ $row->id }})"
                                title="{{ $row->is_default ? 'Nonaktifkan Default' : 'Jadikan Default' }}"
                                class="inline-flex items-center gap-2 cursor-pointer group"
                            >
                                <span class="relative inline-flex h-5 w-9 flex-shrink-0 items-center rounded-full transition-colors duration-200 {{ $row->is_default ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-700' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white border border-slate-300 dark:border-slate-600 transition-transform duration-200 {{ $row->is_default ? 'translate-x-[18px]' : 'translate-x-[2px]' }}"></span>
                                </span>
                                @if ($row->is_default)
                                    <span class="text-[10px] font-bold text-primary dark:text-blue-400">Default</span>
                                @endif
                            </button>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $row->is_default ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/40' : 'bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' }}">
                                {{ $row->is_default ? 'Default' : '-' }}
                            </span>
                        @endif
                    </x-pos.table.td>
                    <!-- Actions -->
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if (\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.sale_categories.edit'))
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
                            @endif
                            @if (\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.sale_categories.delete'))
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
                            @endif
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="6" icon="ph-storefront" message="Tidak ada kategori penjualan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$categories" perPageModel="perPage" />
</x-pos.table.container>
