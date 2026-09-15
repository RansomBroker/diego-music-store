<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Nama Pelanggan -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Pelanggan
                </x-pos.table.th>
                <!-- Telepon -->
                <x-pos.table.th>
                    Telepon
                </x-pos.table.th>
                <!-- Email -->
                <x-pos.table.th>
                    Email
                </x-pos.table.th>
                <!-- Label -->
                <x-pos.table.th>
                    Label
                </x-pos.table.th>
                <!-- Poin -->
                <x-pos.table.th sortable field="loyalty_points" :sortField="$sortField" :sortDirection="$sortDirection">
                    Poin
                </x-pos.table.th>
                <!-- Member -->
                <x-pos.table.th>
                    Member
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($customers as $customer)
                <x-pos.table.tr>
                    <!-- Nama & Alamat -->
                    <x-pos.table.td class="whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $customer->name }}</div>
                                @if ($customer->address)
                                    <div class="text-xs text-slate-400 dark:text-slate-555 mt-0.5 max-w-[200px] truncate">{{ $customer->address }}</div>
                                @endif
                            </div>
                        </div>
                    </x-pos.table.td>
                    <!-- Telepon -->
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $customer->phone ?? '—' }}
                    </x-pos.table.td>
                    <!-- Email -->
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $customer->email ?? '—' }}
                    </x-pos.table.td>
                    <!-- Label badge -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($customer->label)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-750 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                {{ $customer->label->name }}
                            </span>
                        @else
                            <span class="text-slate-350 dark:text-slate-600 text-xs">—</span>
                        @endif
                    </x-pos.table.td>
                    <!-- Poin -->
                    <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-200">
                        {{ number_format($customer->loyalty_points) }}
                    </x-pos.table.td>
                    <!-- Member Badge -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($customer->is_loyalty_member)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-755 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                <i class="ph-fill ph-star text-[10px]"></i> Member
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60">
                                Umum
                            </span>
                        @endif
                    </x-pos.table.td>
                    <!-- Aksi Buttons -->
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button
                                wire:click="openEdit({{ $customer->id }})"
                                class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                <i class="ph-bold ph-pencil-simple text-xs"></i>
                                <span>Ubah</span>
                            </button>
                            <span class="text-slate-300 dark:text-slate-700">|</span>
                            <button
                                wire:click="confirmDelete({{ $customer->id }})"
                                class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                            >
                                <i class="ph-bold ph-trash text-xs"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="7" icon="ph-users" message="Tidak ada data pelanggan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Footer (Pagination) -->
@if ($customers->total() > 0)
    <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
        <!-- Left: Statistics & Per Page -->
        <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
            <div>
                Menampilkan
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->firstItem() }}</span>
                sampai
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $customers->total() }}</span>
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
        @if ($customers->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                {{-- Previous Page Button --}}
                @if ($customers->onFirstPage())
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
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
                @foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) as $page => $url)
                    @if ($page == $customers->currentPage())
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
                @if ($customers->hasMorePages())
                    <button
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                    >
                        <i class="ph-bold ph-caret-right text-sm"></i>
                    </button>
                @else
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                        <i class="ph-bold ph-caret-right text-sm"></i>
                    </span>
                @endif
            </nav>
        @endif
    </div>
@endif
