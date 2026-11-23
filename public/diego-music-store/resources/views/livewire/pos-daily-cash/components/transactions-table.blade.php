<!-- Transactions Table Card Wrapper (Filament Style) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div>
            <h3 class="text-sm font-extrabold text-slate-850 dark:text-white">Riwayat Kas Harian</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Daftar transaksi kas masuk &amp; keluar sesuai filter yang dipilih.</p>
        </div>
        @if($selectedSessionId === 'active' && $activeSession)
            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/50 text-[10px] font-black uppercase tracking-wider rounded-md self-start sm:self-center">
                Sesi Aktif #{{ str_pad($activeSession->id, 5, '0', STR_PAD_LEFT) }}
            </span>
        @elseif($selectedSessionId !== 'all' && is_numeric($selectedSessionId))
            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider rounded-md self-start sm:self-center">
                Sesi #{{ str_pad((int) $selectedSessionId, 5, '0', STR_PAD_LEFT) }}
            </span>
        @else
            <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/50 text-[10px] font-black uppercase tracking-wider rounded-md self-start sm:self-center">
                Semua Sesi ({{ $fromDate ?: '...' }} s/d {{ $toDate ?: '...' }})
            </span>
        @endif
    </div>

    <!-- Filter Toolbar -->
    @include('livewire.pos-daily-cash.components.toolbar-filters')

    <!-- Filtered Summary Strip -->
    <div class="px-6 py-3 bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="font-bold text-slate-600 dark:text-slate-300">
            Menampilkan <span class="text-slate-900 dark:text-white">{{ number_format($filteredSummary['count'], 0, ',', '.') }}</span> transaksi
        </div>
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 font-bold">
            <span class="text-emerald-600 dark:text-emerald-400">
                Masuk: +Rp {{ number_format($filteredSummary['in'], 0, ',', '.') }}
            </span>
            <span class="text-rose-600 dark:text-rose-400">
                Keluar: -Rp {{ number_format($filteredSummary['out'], 0, ',', '.') }}
            </span>
            <span class="text-blue-600 dark:text-blue-400">
                Setor: -Rp {{ number_format($filteredSummary['transfer'] ?? 0, 0, ',', '.') }}
            </span>
            <span class="{{ $filteredSummary['net'] >= 0 ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                Netto: {{ $filteredSummary['net'] < 0 ? '-' : '' }}Rp {{ number_format(abs($filteredSummary['net']), 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Table Container -->
    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>Waktu</x-pos.table.th>
                    <x-pos.table.th>No. Transaksi</x-pos.table.th>
                    <x-pos.table.th>Sesi</x-pos.table.th>
                    <x-pos.table.th>Tipe</x-pos.table.th>
                    <x-pos.table.th>Kategori / Akun Kontra</x-pos.table.th>
                    <x-pos.table.th>Catatan / Keterangan</x-pos.table.th>
                    <x-pos.table.th class="text-right">Nominal</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($transactions as $tx)
                    <x-pos.table.tr>
                        <x-pos.table.td class="whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ ($tx->transaction_date ?? $tx->created_at)->format('d M Y') }}
                            <span class="block text-[10px] text-slate-400">{{ $tx->created_at->format('H:i') }}</span>
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-xs font-bold text-slate-900 dark:text-slate-100">
                            {{ $tx->transaction_no }}
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-xs font-semibold text-slate-500 dark:text-slate-400">
                            @if($tx->cash_session_id)
                                #{{ str_pad($tx->cash_session_id, 5, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap">
                            @if($tx->type === 'in')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450 border border-emerald-100 dark:border-emerald-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Kas Masuk
                                </span>
                            @elseif($tx->type === 'transfer')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Setor Kas
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-455 border border-rose-100 dark:border-rose-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Kas Keluar
                                </span>
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="font-semibold text-slate-800 dark:text-slate-200">
                            @if($tx->type === 'in')
                                {{ $tx->sourceAccount ? $tx->sourceAccount->name : 'N/A' }}
                            @elseif($tx->type === 'transfer')
                                {{ $tx->sourceAccount?->name ?? 'Kas' }} → {{ $tx->destinationAccount?->name ?? 'Tujuan' }}
                            @else
                                {{ $tx->destinationAccount ? $tx->destinationAccount->name : 'N/A' }}
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="text-xs font-medium text-slate-555 dark:text-slate-400 max-w-xs truncate" title="{{ $tx->notes }}">
                            {{ $tx->notes ?: '-' }}
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-right font-bold text-sm {{ $tx->type === 'in' ? 'text-emerald-600 dark:text-emerald-450' : ($tx->type === 'transfer' ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-450') }}">
                            {{ $tx->type === 'in' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="7" icon="ph-receipt-x" message="Tidak ada transaksi kas harian untuk filter ini." />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :paginator="$transactions" perPageModel="perPage" />
    </x-pos.table.container>
</div>
