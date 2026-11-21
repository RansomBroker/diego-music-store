<!-- Roles Data Table -->
@php
    $foTotal = count(\App\Helpers\PermissionHelper::getSectionPermissionNames('frontoffice', false));
    $boTotal = count(\App\Helpers\PermissionHelper::getSectionPermissionNames('backoffice', false));
@endphp

<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <x-pos.table.th>Nama Role</x-pos.table.th>
                <x-pos.table.th>Akses Panel</x-pos.table.th>
                <x-pos.table.th class="text-center">Fitur FO</x-pos.table.th>
                <x-pos.table.th class="text-center">Fitur BO</x-pos.table.th>
                <x-pos.table.th class="text-center">Total User</x-pos.table.th>
                <x-pos.table.th align="right">Aksi</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
            @forelse ($roles as $row)
                @php
                    $isProtected = \App\Helpers\PermissionHelper::isProtectedRoleName($row->name);
                    $summary = \App\Helpers\PermissionHelper::resolveRoleAccessSummary($row);
                    $panelBadgeClass = match ($summary['access_color']) {
                        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900',
                        'info' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900',
                        'warning' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900',
                        default => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                    };
                @endphp
                <x-pos.table.tr>
                    <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                        <div class="flex items-center gap-2">
                            <span>{{ $row->name }}</span>
                            @if ($isProtected)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900">
                                    System Owner
                                </span>
                            @endif
                        </div>
                    </x-pos.table.td>
                    <x-pos.table.td>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $panelBadgeClass }}">
                            {{ $summary['access_label'] }}
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['has_fo'] ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $summary['fo_count'] }} / {{ $foTotal }}
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['has_bo'] ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $summary['bo_count'] }} / {{ $boTotal }}
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                            {{ $row->users_count }} User
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if ($canManagePrivileges && (! $isProtected || \App\Helpers\PermissionHelper::isOwner(Auth::user())))
                                <x-pos.utility.button
                                    type="button"
                                    variant="secondary"
                                    size="xs"
                                    icon="ph-copy"
                                    wire:click="cloneRole({{ $row->id }})"
                                    title="Duplikat Role"
                                >
                                    Duplikat
                                </x-pos.utility.button>
                            @endif

                            @if ($canManagePrivileges && ! $isProtected)
                                <x-pos.utility.button
                                    type="button"
                                    variant="warning"
                                    size="xs"
                                    icon="ph-key"
                                    wire:click="openEdit({{ $row->id }})"
                                    title="Atur Hak Akses"
                                >
                                    Atur Akses
                                </x-pos.utility.button>

                                @if ((int) $row->users_count === 0)
                                    <x-pos.utility.button
                                        type="button"
                                        variant="danger"
                                        size="xs"
                                        icon="ph-trash"
                                        wire:click="confirmDelete({{ $row->id }})"
                                        title="Hapus Role"
                                    >
                                        Hapus
                                    </x-pos.utility.button>
                                @endif
                            @elseif ($isProtected)
                                <span class="text-xs italic text-slate-400 dark:text-slate-500 px-2">Akses Penuh Tetap</span>
                            @endif
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="6" icon="ph-shield-check" message="Belum ada role ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$roles" :perPageModel="null" />
</x-pos.table.container>
