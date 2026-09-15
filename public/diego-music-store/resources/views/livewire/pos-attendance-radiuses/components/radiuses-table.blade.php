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
                            variant="warning"
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
                <x-pos.table.empty colspan="6" icon="ph-storefront" message="Belum ada data cabang ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
</x-pos.table.container>

<!-- Table Footer / Pagination -->
<x-pos.table.footer :paginator="$branches" />
