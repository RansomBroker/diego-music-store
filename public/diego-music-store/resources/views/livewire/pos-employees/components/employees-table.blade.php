<!-- Toolbar (Search & Actions) Card Header -->
<div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
    <!-- Search Input -->
    <div class="w-full sm:max-w-xs">
        <x-pos.form.input
            model="search"
            :live="true"
            debounce="300ms"
            placeholder="Cari NIK, Nama, HP..."
            icon="ph-magnifying-glass"
            size="sm"
        />
    </div>

    <!-- Add Action -->
    <x-pos.utility.button
        type="button"
        variant="primary"
        size="sm"
        icon="ph-plus"
        wire:click="openCreate"
    >
        Tambah Karyawan
    </x-pos.utility.button>
</div>

<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <x-pos.table.th sortable field="nik" :sortField="$sortField" :sortDirection="$sortDirection">
                    NIK
                </x-pos.table.th>
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Karyawan
                </x-pos.table.th>
                <x-pos.table.th>
                    Role / Akses User
                </x-pos.table.th>
                <x-pos.table.th>
                    Cabang
                </x-pos.table.th>
                <x-pos.table.th>
                    No Telepon / WA
                </x-pos.table.th>
                <x-pos.table.th sortable field="monthly_off_days_quota" :sortField="$sortField" :sortDirection="$sortDirection">
                    Quota Off Day
                </x-pos.table.th>
                <x-pos.table.th sortable field="basic_salary" :sortField="$sortField" :sortDirection="$sortDirection">
                    Gaji Pokok
                </x-pos.table.th>
                <x-pos.table.th sortable field="is_active" :sortField="$sortField" :sortDirection="$sortDirection">
                    Status
                </x-pos.table.th>
                <x-pos.table.th align="right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
            @forelse ($employees as $emp)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                    <!-- NIK -->
                    <x-pos.table.td class="font-mono text-xs text-slate-500 dark:text-slate-400">
                        {{ $emp->nik }}
                    </x-pos.table.td>

                    <!-- Nama Karyawan -->
                    <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                        <div>{{ $emp->name }}</div>
                        @if($emp->email)
                            <div class="text-xs text-slate-400 dark:text-slate-500 font-normal">{{ $emp->email }}</div>
                        @endif
                    </x-pos.table.td>

                    <!-- Role / User -->
                    <x-pos.table.td>
                        @if ($emp->user)
                            <div class="flex flex-wrap gap-1">
                                @forelse($emp->user->roles as $role)
                                    <x-pos.utility.pill variant="primary" size="xs">
                                        {{ ucfirst($role->name) }}
                                    </x-pos.utility.pill>
                                @empty
                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">User (Tanpa Role)</span>
                                @endforelse
                            </div>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500 italic">Tanpa Akun User</span>
                        @endif
                    </x-pos.table.td>

                    <!-- Cabang -->
                    <x-pos.table.td>
                        @if($emp->branch)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                                <i class="ph ph-storefront text-slate-400"></i>
                                {{ $emp->branch->name }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500 italic">Semua Cabang</span>
                        @endif
                    </x-pos.table.td>

                    <!-- Telepon -->
                    <x-pos.table.td class="text-xs">
                        {{ $emp->phone ?: '-' }}
                    </x-pos.table.td>

                    <!-- Quota Off Day -->
                    <x-pos.table.td class="text-xs font-semibold">
                        <x-pos.utility.pill variant="default" size="xs">
                            {{ $emp->monthly_off_days_quota }} hari/bln
                        </x-pos.utility.pill>
                    </x-pos.table.td>

                    <!-- Gaji Pokok -->
                    <x-pos.table.td class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}
                    </x-pos.table.td>

                    <!-- Status -->
                    <x-pos.table.td>
                        @if ($emp->is_active)
                            <x-pos.utility.pill variant="success" size="xs">
                                Aktif
                            </x-pos.utility.pill>
                        @else
                            <x-pos.utility.pill variant="danger" size="xs">
                                Nonaktif
                            </x-pos.utility.pill>
                        @endif
                    </x-pos.table.td>

                    <!-- Actions -->
                    <x-pos.table.td align="right">
                        <x-pos.table.actions
                            :editAction="'openEdit(' . $emp->id . ')'"
                            :deleteAction="'confirmDelete(' . $emp->id . ')'"
                        />
                    </x-pos.table.td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="ph ph-user-group text-4xl text-slate-300 dark:text-slate-600"></i>
                            <span class="text-sm font-medium">Belum ada data karyawan</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Table Footer / Pagination -->
<x-pos.table.footer :paginator="$employees" />
