<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Full Name -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Lengkap
                </x-pos.table.th>
                <!-- Username -->
                <x-pos.table.th sortable field="username" :sortField="$sortField" :sortDirection="$sortDirection">
                    Username
                </x-pos.table.th>
                <!-- Email -->
                <x-pos.table.th sortable field="email" :sortField="$sortField" :sortDirection="$sortDirection">
                    Email Address
                </x-pos.table.th>
                <!-- Status -->
                <x-pos.table.th>
                    Status
                </x-pos.table.th>
                <!-- Branches -->
                <x-pos.table.th>
                    Cabang
                </x-pos.table.th>
                <!-- Roles -->
                <x-pos.table.th>
                    Role
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($users as $row)
                <x-pos.table.tr>
                    <!-- Full Name + Avatar -->
                    <x-pos.table.td class="whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($row->name, 0, 2)) }}
                            </div>
                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                {{ $row->name }}
                            </div>
                        </div>
                    </x-pos.table.td>
                    <!-- Username -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 font-medium">
                        {{ $row->username ?? '—' }}
                    </x-pos.table.td>
                    <!-- Email Address -->
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                        {{ $row->email }}
                    </x-pos.table.td>
                    <!-- Status -->
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
                    <!-- Branches (badges) -->
                    <x-pos.table.td class="max-w-[200px]">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($row->branches as $branch)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60">
                                    {{ $branch->name }}
                                </span>
                            @empty
                                <span class="text-slate-350 dark:text-slate-650 text-xs">—</span>
                            @endforelse
                        </div>
                    </x-pos.table.td>
                    <!-- Roles (badges with custom colors matching UsersTable.php config) -->
                    <x-pos.table.td class="whitespace-nowrap">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($row->roles as $role)
                                @php
                                    $badgeColor = match ($role->name) {
                                        'owner'      => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/40 dark:text-rose-455 dark:border-rose-900/40',
                                        'admin'      => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/40 dark:text-amber-455 dark:border-amber-900/40',
                                        'cashier'    => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-455 dark:border-emerald-900/40',
                                        'sales'      => 'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-950/40 dark:text-sky-400 dark:border-sky-900/40',
                                        'technician' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-850 dark:text-slate-350 dark:border-slate-750',
                                        default      => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/40',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeColor }}">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-slate-350 dark:text-slate-650 text-xs">—</span>
                            @endforelse
                        </div>
                    </x-pos.table.td>
                    <!-- Actions -->
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <x-pos.table.actions
                            :editAction="'openEdit(' . $row->id . ')'"
                            :deleteAction="Auth::id() !== $row->id ? 'confirmDelete(' . $row->id . ')' : null"
                            :showDelete="Auth::id() !== $row->id"
                        />
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="7" icon="ph-users" message="Tidak ada user ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Footer (Filament v3 Style Pagination) -->
@if ($users->total() > 0)
    <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
        <!-- Left: Statistics & Per Page -->
        <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
            <div>
                Menampilkan
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->firstItem() }}</span>
                sampai
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $users->total() }}</span>
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
        @if ($users->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                {{-- Previous Page Button --}}
                @if ($users->onFirstPage())
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
                @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                    @if ($page == $users->currentPage())
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
                @if ($users->hasMorePages())
                    <button
                        wire:click="nextPage"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
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
