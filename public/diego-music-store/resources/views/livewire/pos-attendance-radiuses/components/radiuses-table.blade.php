<!-- Table -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
            <tr>
                <x-pos.table.th>Nama Cabang / Toko</x-pos.table.th>
                <x-pos.table.th>Alamat Fisik</x-pos.table.th>
                <x-pos.table.th>Koordinat GPS (Lat, Lng)</x-pos.table.th>
                <x-pos.table.th>Radius Toleransi (Meter)</x-pos.table.th>
                <x-pos.table.th>Status Operasional</x-pos.table.th>
                <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs">
            @forelse ($branches as $b)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                    <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                        {{ $b->name }}
                        <div class="text-[10px] text-slate-400 font-normal">{{ $b->store_name ?: $b->name }}</div>
                    </x-pos.table.td>
                    <x-pos.table.td class="max-w-xs truncate text-slate-500">
                        {{ $b->address ?: 'Alamat belum diatur' }}
                    </x-pos.table.td>
                    <x-pos.table.td>
                        @if ($b->latitude !== null && $b->longitude !== null)
                            <div class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                <i class="ph-bold ph-map-pin text-primary text-sm"></i>
                                {{ number_format($b->latitude, 6) }}, {{ number_format($b->longitude, 6) }}
                            </div>
                        @else
                            <span class="text-amber-500 font-semibold text-[11px] flex items-center gap-1">
                                <i class="ph-bold ph-warning"></i> Belum Set Koordinat
                            </span>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td>
                        <x-pos.utility.pill variant="primary" size="sm" class="gap-1 font-black">
                            <i class="ph-bold ph-ruler"></i>
                            {{ $b->attendance_radius_meters ?: 100 }} Meter
                        </x-pos.utility.pill>
                    </x-pos.table.td>
                    <x-pos.table.td>
                        @if ($b->is_active)
                            <x-pos.utility.pill variant="success" size="xs">
                                Aktif
                            </x-pos.utility.pill>
                        @else
                            <x-pos.utility.pill variant="default" size="xs">
                                Nonaktif
                            </x-pos.utility.pill>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="text-right">
                        <x-pos.utility.button
                            variant="secondary"
                            size="sm"
                            icon="ph-map-pin-line"
                            wire:click="openEditModal({{ $b->id }})"
                            title="Edit Radius Presensi Peta Leaflet"
                        >
                            Edit Radius
                        </x-pos.utility.button>
                    </x-pos.table.td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i class="ph ph-storefront text-4xl text-slate-300 dark:text-slate-600"></i>
                            <span class="text-sm font-medium">Belum ada data cabang ditemukan</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Pagination -->
@if ($branches->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        {{ $branches->links() }}
    </div>
@endif
