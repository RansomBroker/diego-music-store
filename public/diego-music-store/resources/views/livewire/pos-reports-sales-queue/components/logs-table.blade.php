<!-- Table List: Riwayat Detail Sesi Giliran Melayani -->
<x-pos.table.container class="border-0 shadow-none rounded-none">
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-500 uppercase tracking-wider">
            <tr>
                <x-pos.table.th>Waktu Mulai</x-pos.table.th>
                <x-pos.table.th>Waktu Selesai</x-pos.table.th>
                <x-pos.table.th class="text-center">Durasi</x-pos.table.th>
                <x-pos.table.th>Sales Rep</x-pos.table.th>
                <x-pos.table.th class="text-center">Status</x-pos.table.th>
                <x-pos.table.th>No. Faktur (Closing)</x-pos.table.th>
                <x-pos.table.th class="text-right">Nilai Belanja</x-pos.table.th>
                <x-pos.table.th>Kasir Pencatat</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
            @forelse ($logs as $log)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                    {{-- Waktu Mulai --}}
                    <x-pos.table.td class="whitespace-nowrap font-bold text-slate-900 dark:text-white">
                        {{ $log->started_at->format('H:i:s') }}
                    </x-pos.table.td>

                    {{-- Waktu Selesai --}}
                    <x-pos.table.td class="whitespace-nowrap text-slate-500 dark:text-slate-400">
                        {{ $log->ended_at ? $log->ended_at->format('H:i:s') : '-' }}
                    </x-pos.table.td>

                    {{-- Durasi --}}
                    <x-pos.table.td class="whitespace-nowrap text-center font-bold text-slate-700 dark:text-slate-200">
                        {{ $log->duration_text }}
                    </x-pos.table.td>

                    {{-- Sales Rep --}}
                    <x-pos.table.td class="whitespace-nowrap font-black text-slate-900 dark:text-white">
                        {{ $log->user->name ?? 'Sales' }}
                    </x-pos.table.td>

                    {{-- Status --}}
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($log->status === 'completed')
                            <x-pos.utility.pill variant="success" size="xs">
                                Selesai
                            </x-pos.utility.pill>
                        @elseif ($log->status === 'serving')
                            <x-pos.utility.pill variant="primary" size="xs" class="animate-pulse">
                                Sedang Melayani
                            </x-pos.utility.pill>
                        @else
                            <x-pos.utility.pill variant="default" size="xs">
                                {{ ucfirst($log->status) }}
                            </x-pos.utility.pill>
                        @endif
                    </x-pos.table.td>

                    {{-- No. Faktur --}}
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($log->sale)
                            <a 
                                href="{{ route('pos.receipt', $log->sale->id) }}" 
                                target="_blank"
                                class="text-primary dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1"
                            >
                                <span>{{ $log->sale->invoice_number }}</span>
                                <i class="ph-bold ph-arrow-square-out text-[11px]"></i>
                            </a>
                        @else
                            <span class="text-slate-400 font-normal">-</span>
                        @endif
                    </x-pos.table.td>

                    {{-- Nilai Belanja --}}
                    <x-pos.table.td class="whitespace-nowrap text-right font-black text-slate-900 dark:text-white">
                        @if ($log->closing_amount > 0)
                            Rp {{ number_format($log->closing_amount, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400 font-normal">Rp 0</span>
                        @endif
                    </x-pos.table.td>

                    {{-- Kasir Pencatat --}}
                    <x-pos.table.td class="whitespace-nowrap text-xs text-slate-500">
                        {{ $log->creator->name ?? 'Kasir' }}
                    </x-pos.table.td>
                </tr>
            @empty
                <x-pos.table.empty 
                    colspan="8" 
                    icon="ph-notepad" 
                    message="Tidak ada riwayat giliran yang cocok dengan filter." 
                />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$logs" perPageModel="perPage" />
</x-pos.table.container>
