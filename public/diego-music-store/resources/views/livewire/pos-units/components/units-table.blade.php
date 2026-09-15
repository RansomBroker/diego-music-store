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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 font-mono text-xs">
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
                        <div class="flex items-center justify-end gap-3">
                            <button
                                wire:click="openEdit({{ $row->id }})"
                                class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                <i class="ph-bold ph-pencil-simple text-xs"></i>
                                <span>Ubah</span>
                            </button>
                            <span class="text-slate-300 dark:text-slate-700">|</span>
                            <button
                                wire:click="confirmDelete({{ $row->id }})"
                                class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                            >
                                <i class="ph-bold ph-trash text-xs"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="6" icon="ph-ruler" message="Tidak ada satuan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Footer (Filament v3 Style Pagination) -->
@if ($units->total() > 0)
    <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
        <!-- Left: Statistics & Per Page -->
        <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
            <div>
                Menampilkan
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $units->firstItem() }}</span>
                sampai
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $units->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $units->total() }}</span>
                hasil
            </div>
            <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>
            <div class="flex items-center gap-1.5">
                <label class="text-xs">Per halaman:</label>
                <select
                    wire:model.live="perPage"
                    class="bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-750 dark:text-slate-250 py-1 px-2 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition duration-150"
                >
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Right: Navigation Pages -->
        @if ($units->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                {{-- Previous Page Button --}}
                @if ($units->onFirstPage())
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
                        <i class="ph-bold ph-caret-left text-sm"></i>
                    </span>
                @else
                    <button
                        wire:click="previousPage"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                    >
                        <i class="ph-bold ph-caret-left text-sm"></i>
                    </button>
                @endif

                {{-- Page Numbers --}}
                @foreach ($units->getUrlRange(max(1, $units->currentPage() - 2), min($units->lastPage(), $units->currentPage() + 2)) as $page => $url)
                    @if ($page == $units->currentPage())
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <button
                            wire:click="gotoPage({{ $page }})"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                        >
                            {{ $page }}
                        </button>
                    @endif
                @endforeach

                {{-- Next Page Button --}}
                @if ($units->hasMorePages())
                    <button
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-655 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                    >
                        <i class="ph-bold ph-caret-right text-sm"></i>
                    </button>
                @else
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
                        <i class="ph-bold ph-caret-right text-sm"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>
@endif
