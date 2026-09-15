<!-- Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors duration-200">
    <!-- Header Title Bar -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white dark:bg-slate-900">
        <div class="flex items-center gap-2">
            <span class="font-bold text-sm text-slate-900 dark:text-white">DAFTAR TIKET SERVICE & REPARASI BARANG</span>
        </div>
        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
            Cabang: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $currentBranch?->name ?: 'Semua Cabang' }}</span>
        </span>
    </div>

    <!-- Integrated Filter Toolbar -->
    @include('livewire.pos-service-management.components.filter-bar')

    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>No. Tiket & Tgl</x-pos.table.th>
                    <x-pos.table.th>Unit / Instrument</x-pos.table.th>
                    <x-pos.table.th>Pelanggan</x-pos.table.th>
                    <x-pos.table.th>Teknisi</x-pos.table.th>
                    <x-pos.table.th class="text-center">Status Progress</x-pos.table.th>
                    <x-pos.table.th class="text-right">Total Biaya</x-pos.table.th>
                    <x-pos.table.th class="text-center">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($orders as $so)
                    <x-pos.table.tr>
                        <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                            <div class="font-semibold text-primary dark:text-blue-400">{{ $so->ticket_code }}</div>
                            <div class="text-[10px] text-slate-400">{{ $so->created_at->format('d/m/Y H:i') }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs font-semibold text-slate-900 dark:text-white">
                            <div>{{ $so->device_name }}</div>
                            @if ($so->serial_number)
                                <div class="text-[10px] font-mono text-slate-400">S/N: {{ $so->serial_number }}</div>
                            @endif
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                            <div>{{ $so->customer_name }}</div>
                            <div class="text-[10px] font-mono text-slate-400">{{ $so->customer_phone ?: '-' }}</div>
                        </x-pos.table.td>

                        <x-pos.table.td class="text-xs text-slate-700 dark:text-slate-300">
                            {{ $so->technician->name ?? 'Belum ditentukan' }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            @if ($so->status === 'received')
                                <x-pos.utility.pill variant="primary" size="xs">
                                    DITERIMA
                                </x-pos.utility.pill>
                            @elseif ($so->status === 'diagnosing')
                                <x-pos.utility.pill variant="warning" size="xs">
                                    DIAGNOSA
                                </x-pos.utility.pill>
                            @elseif ($so->status === 'in_progress')
                                <x-pos.utility.pill variant="primary" size="xs">
                                    DIKERJAKAN
                                </x-pos.utility.pill>
                            @elseif ($so->status === 'waiting_parts')
                                <x-pos.utility.pill variant="warning" size="xs">
                                    SPAREPART
                                </x-pos.utility.pill>
                            @elseif ($so->status === 'completed')
                                <x-pos.utility.pill variant="success" size="xs">
                                    SELESAI
                                </x-pos.utility.pill>
                            @elseif ($so->status === 'picked_up')
                                <x-pos.utility.pill variant="success" size="xs">
                                    DIAMBIL
                                </x-pos.utility.pill>
                            @else
                                <x-pos.utility.pill variant="danger" size="xs">
                                    DIBATALKAN
                                </x-pos.utility.pill>
                            @endif
                        </x-pos.table.td>

                        <x-pos.table.td class="text-right font-mono font-semibold text-xs text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($so->total_cost ?: $so->estimated_cost, 0, ',', '.') }}
                        </x-pos.table.td>

                        <x-pos.table.td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <x-pos.utility.button variant="primary" size="xs" icon="ph-pencil-simple" wire:click="openEditModal({{ $so->id }})" title="Update Status & Catatan">
                                    Update
                                </x-pos.utility.button>

                                <a href="{{ $so->tracking_url }}" target="_blank" class="p-1.5 text-slate-500 hover:text-primary dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Buka Link Tracking Publik">
                                    <i class="ph ph-arrow-square-out text-base"></i>
                                </a>
                            </div>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="7" icon="ph-wrench" message="Belum ada tiket service & reparasi barang yang sesuai dengan filter." />
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>

    <!-- Table Footer / Pagination -->
    <x-pos.table.footer :paginator="$orders" />
</div>
