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
                        <div class="flex items-center justify-end gap-3">
                            <button wire:click="openEdit({{ $row->id }})" class="text-xs font-bold text-primary hover:underline cursor-pointer">
                                Ubah
                            </button>
                            <span class="text-slate-300 dark:text-slate-700">|</span>
                            <button wire:click="confirmDelete({{ $row->id }})" class="text-xs font-bold text-rose-600 hover:underline cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="8" icon="ph-ticket" message="Tidak ada voucher ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Footer (Filament v3 Style Pagination) -->
@if ($vouchers->total() > 0)
    <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
        <!-- Left: Statistics & Per Page -->
        <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
            <div>
                Menampilkan
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $vouchers->firstItem() }}</span>
                sampai
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $vouchers->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $vouchers->total() }}</span>
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
        @if ($vouchers->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                {{-- Previous Page Button --}}
                @if ($vouchers->onFirstPage())
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
                @foreach ($vouchers->getUrlRange(max(1, $vouchers->currentPage() - 2), min($vouchers->lastPage(), $vouchers->currentPage() + 2)) as $page => $url)
                    @if ($page == $vouchers->currentPage())
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
                @if ($vouchers->hasMorePages())
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
