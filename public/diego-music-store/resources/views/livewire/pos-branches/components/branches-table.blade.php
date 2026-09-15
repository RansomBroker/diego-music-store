<!-- Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
        <span>DAFTAR OUTLET & LOKASI CABANG</span>
        <span class="text-xs text-slate-400 font-normal">Total {{ $branches->total() }} Cabang</span>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>Nama Cabang & Toko</x-pos.table.th>
                    <x-pos.table.th>Kota / Wilayah</x-pos.table.th>
                    <x-pos.table.th>Alamat & Kontak</x-pos.table.th>
                    <x-pos.table.th>Manager Cabang</x-pos.table.th>
                    <x-pos.table.th class="text-center">Status</x-pos.table.th>
                    <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($branches as $b)
                    <x-pos.table.tr>
                        <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                @if ($b->logo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($b->logo_path) }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center text-xs shrink-0">
                                        <i class="ph ph-storefront"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $b->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $b->store_name }}</div>
                                </div>
                            </div>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <div>{{ $b->city ?: '-' }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">{{ $b->province }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-400">
                            <div class="truncate max-w-xs" title="{{ $b->address }}">{{ $b->address ?: '-' }}</div>
                            <div class="text-[10px] font-mono text-slate-400">Telp: {{ $b->phone ?: '-' }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs text-slate-800 dark:text-slate-200">
                            {{ $b->manager->name ?? 'Belum ditentukan' }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            @if ($b->is_active)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    AKTIF
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    NON-AKTIF
                                </span>
                            @endif
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-pos.utility.button variant="warning" size="xs" icon="ph-pencil-simple" wire:click="openEdit({{ $b->id }})">
                                    Edit Profil
                                </x-pos.utility.button>
                            </div>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="6" icon="ph-storefront" message="Belum ada data cabang toko." />
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>

    <x-pos.table.footer :paginator="$branches" />
</div>
