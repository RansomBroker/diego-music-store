<!-- Table List: Rekapitulasi Performa Giliran Sales (Leaderboard) -->
<x-pos.table.container class="border-0 shadow-none rounded-none">
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-500 uppercase tracking-wider">
            <tr>
                <x-pos.table.th class="text-center w-14">Rank</x-pos.table.th>
                <x-pos.table.th>Nama Sales</x-pos.table.th>
                <x-pos.table.th class="text-center">Total Giliran</x-pos.table.th>
                <x-pos.table.th class="text-center">Closing Nota</x-pos.table.th>
                <x-pos.table.th class="text-center">Rasio Closing</x-pos.table.th>
                <x-pos.table.th class="text-right">Total Omset Closing</x-pos.table.th>
                <x-pos.table.th class="text-center">Rata-rata Durasi</x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-300">
            @forelse ($leaderboard as $idx => $row)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                    {{-- Rank --}}
                    <x-pos.table.td class="text-center">
                        @if ($idx === 0)
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-black inline-flex items-center justify-center text-xs shadow-xs">1</span>
                        @elseif ($idx === 1)
                            <span class="w-6 h-6 rounded-full bg-slate-300 dark:bg-slate-600 text-slate-900 dark:text-white font-black inline-flex items-center justify-center text-xs">2</span>
                        @elseif ($idx === 2)
                            <span class="w-6 h-6 rounded-full bg-amber-700/70 text-white font-black inline-flex items-center justify-center text-xs">3</span>
                        @else
                            <span class="text-slate-400 font-bold text-xs">{{ $idx + 1 }}</span>
                        @endif
                    </x-pos.table.td>

                    {{-- Nama Sales --}}
                    <x-pos.table.td>
                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ $row['name'] }}</div>
                        <div class="text-[11px] text-slate-400">NIK: {{ $row['employee_nik'] }}</div>
                    </x-pos.table.td>

                    {{-- Total Giliran --}}
                    <x-pos.table.td class="text-center font-extrabold text-slate-700 dark:text-slate-200">
                        {{ $row['total_servings'] }}x
                    </x-pos.table.td>

                    {{-- Closing Nota --}}
                    <x-pos.table.td class="text-center font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $row['total_closings'] }}
                    </x-pos.table.td>

                    {{-- Rasio Closing --}}
                    <x-pos.table.td class="text-center">
                        <x-pos.utility.pill 
                            variant="{{ $row['closing_rate'] >= 50 ? 'success' : 'default' }}" 
                            size="xs"
                        >
                            {{ $row['closing_rate'] }}%
                        </x-pos.utility.pill>
                    </x-pos.table.td>

                    {{-- Total Omset Closing --}}
                    <x-pos.table.td class="text-right font-black text-slate-900 dark:text-white">
                        Rp {{ number_format($row['total_revenue'], 0, ',', '.') }}
                    </x-pos.table.td>

                    {{-- Rata-rata Durasi --}}
                    <x-pos.table.td class="text-center text-slate-500 dark:text-slate-400 font-semibold">
                        {{ $row['avg_duration_minutes'] }} mnt
                    </x-pos.table.td>
                </tr>
            @empty
                <x-pos.table.empty 
                    colspan="7" 
                    icon="ph-chart-bar" 
                    message="Belum ada aktivitas giliran sales pada tanggal ini." 
                />
            @endforelse
        </tbody>
    </x-pos.table>

    <!-- Table Footer Info -->
    <div class="px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
        <span>Menampilkan <strong class="text-slate-800 dark:text-slate-200">{{ $leaderboard->count() }}</strong> sales aktif yang melayani hari ini</span>
        <span class="text-[11px] text-slate-400">Diurutkan berdasarkan perolehan total omset closing tertinggi</span>
    </div>
</x-pos.table.container>
