<!-- Tab: Riwayat Presensi Karyawan -->
<div>
    <!-- Toolbar (Filters) -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <!-- Search Input -->
        <div class="w-full sm:max-w-xs">
            <x-pos.form.input
                model="search"
                :live="true"
                debounce="300ms"
                placeholder="Cari nama karyawan..."
                icon="ph-magnifying-glass"
                size="sm"
            />
        </div>

        <!-- Filters: Month & Branch -->
        <div class="flex items-center gap-3">
            <div class="w-40">
                <x-pos.form.input
                    type="month"
                    model="filterMonth"
                    :live="true"
                    icon="ph-calendar"
                    size="sm"
                />
            </div>
            <div class="w-48">
                <x-pos.form.dropdown
                    model="filterBranchId"
                    :live="true"
                    icon="ph-buildings"
                    placeholder="Semua Cabang"
                    size="sm"
                >
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </x-pos.form.dropdown>
            </div>
        </div>
    </div>

    <!-- Table -->
    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                <tr>
                    <x-pos.table.th>Tanggal</x-pos.table.th>
                    <x-pos.table.th>Karyawan</x-pos.table.th>
                    <x-pos.table.th>Cabang</x-pos.table.th>
                    <x-pos.table.th>Clock In / Out</x-pos.table.th>
                    <x-pos.table.th>Status & Off Day</x-pos.table.th>
                    <x-pos.table.th>Radius GPS</x-pos.table.th>
                    <x-pos.table.th>Bukti Foto Selfie</x-pos.table.th>
                    <x-pos.table.th>Catatan</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs">
                @forelse ($attendances as $row)
                    @php
                        $emp = $row->employee;
                        $usedOff = $emp ? $emp->used_off_days_this_month : 0;
                        $quotaOff = $emp ? $emp->monthly_off_days_quota : 4;
                        $isOver = $emp ? $emp->is_off_days_over_quota : false;
                        $overCount = $emp ? $emp->off_days_over_count : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                        <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ $row->date->format('d M Y') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                            {{ $emp->name ?? '-' }}
                            <div class="text-[10px] text-slate-400 font-mono">{{ $emp->nik ?? '' }}</div>
                        </x-pos.table.td>
                        <x-pos.table.td>{{ $row->branch->name ?? 'Cabang Utama' }}</x-pos.table.td>
                        <x-pos.table.td>
                            <div class="space-y-0.5">
                                <div class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                    In: {{ $row->clock_in ? $row->clock_in->format('H:i:s') : '-' }}
                                </div>
                                <div class="font-mono text-amber-600 dark:text-amber-400 font-bold">
                                    Out: {{ $row->clock_out ? $row->clock_out->format('H:i:s') : '-' }}
                                </div>
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td>
                            <div class="space-y-1">
                                @if ($row->status === 'hadir')
                                    <x-pos.utility.pill variant="success" size="xs">
                                        Hadir
                                    </x-pos.utility.pill>
                                @elseif ($row->status === 'off_day')
                                    <x-pos.utility.pill variant="primary" size="xs">
                                        Off Day
                                    </x-pos.utility.pill>
                                @elseif ($row->status === 'izin')
                                    <x-pos.utility.pill variant="warning" size="xs">
                                        Izin
                                    </x-pos.utility.pill>
                                @elseif ($row->status === 'sakit')
                                    <x-pos.utility.pill variant="default" size="xs">
                                        Sakit
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="danger" size="xs">
                                        Alpha
                                    </x-pos.utility.pill>
                                @endif

                                <div class="text-[10px] text-slate-400">
                                    Off: <span class="{{ $isOver ? 'text-rose-500 font-bold' : '' }}">{{ $usedOff }}/{{ $quotaOff }} Hari</span>
                                </div>
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td>
                            @if ($row->latitude && $row->longitude)
                                @if ($row->is_out_of_radius)
                                    <x-pos.utility.pill variant="danger" size="xs" title="{{ $row->distance_meters }} meter dari cabang">
                                        <i class="ph-bold ph-warning mr-1"></i>
                                        Luar Radius ({{ $row->distance_meters }}m)
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="success" size="xs" title="{{ $row->distance_meters }} meter dari cabang">
                                        <i class="ph-bold ph-check-circle mr-1"></i>
                                        Dalam Radius ({{ $row->distance_meters }}m)
                                    </x-pos.utility.pill>
                                @endif
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td>
                            <div class="flex items-center gap-1">
                                @if ($row->clock_in_photo_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($row->clock_in_photo_path) }}" target="_blank" title="Foto Clock In">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($row->clock_in_photo_path) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hover:scale-110 transition-transform">
                                    </a>
                                @endif
                                @if ($row->clock_out_photo_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($row->clock_out_photo_path) }}" target="_blank" title="Foto Clock Out">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($row->clock_out_photo_path) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hover:scale-110 transition-transform">
                                    </a>
                                @endif
                                @if (!$row->clock_in_photo_path && !$row->clock_out_photo_path)
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td class="max-w-xs truncate text-slate-500">
                            {{ $row->notes ?: '-' }}
                        </x-pos.table.td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ph ph-calendar text-4xl text-slate-300 dark:text-slate-600"></i>
                                <span class="text-sm font-medium">Belum ada riwayat presensi untuk periode ini</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>

    <!-- Pagination -->
    @if ($attendances->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
