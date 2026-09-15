<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <x-pos.table.th>Nama Role</x-pos.table.th>
                <x-pos.table.th>Jumlah User</x-pos.table.th>
                <x-pos.table.th>Jumlah Permissions</x-pos.table.th>
                <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($roles as $row)
                <x-pos.table.tr>
                    <x-pos.table.td class="whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <i class="ph-bold ph-shield-check text-base"></i>
                            </div>
                            <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">
                                {{ $row->name }}
                            </div>
                        </div>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ $row->users_count }} User
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                            {{ $row->permissions_count }} Akses Dipilih
                        </span>
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button
                                wire:click="openEdit({{ $row->id }})"
                                class="inline-flex items-center gap-1 text-sm font-semibold text-primary dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                <i class="ph-bold ph-pencil-simple text-xs"></i>
                                <span>Atur Hak Akses</span>
                            </button>
                            @if ($row->name !== 'Super Admin' && $row->name !== 'admin')
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <button
                                    wire:click="confirmDelete({{ $row->id }})"
                                    class="inline-flex items-center gap-1 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                                >
                                    <i class="ph-bold ph-trash text-xs"></i>
                                    <span>Hapus</span>
                                </button>
                            @endif
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="4" icon="ph-shield-warning" message="Tidak ada Role ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

@if ($roles->total() > 0)
    <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        {{ $roles->links() }}
    </div>
@endif
