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
                            @if ($row->avatar_full_url)
                                <img src="{{ $row->avatar_full_url }}" alt="{{ $row->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 flex-shrink-0 shadow-xs">
                            @else
                                <div class="w-8 h-8 rounded-full bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-primary/20 dark:border-blue-800/30">
                                    {{ strtoupper(substr($row->name, 0, 2)) }}
                                </div>
                            @endif
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
    <x-pos.table.footer :paginator="$users" perPageModel="perPage" />
</x-pos.table.container>
