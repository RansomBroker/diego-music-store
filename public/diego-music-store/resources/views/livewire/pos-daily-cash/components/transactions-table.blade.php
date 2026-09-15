<!-- Transactions Table Card Wrapper (Filament Style) -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
        <div>
            <h3 class="text-sm font-extrabold text-slate-850 dark:text-white">Riwayat Kas Sesi Ini</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Daftar transaksi kas masuk & keluar yang tercatat pada shift saat ini.</p>
        </div>
        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider rounded-md self-start sm:self-center">
            Sesi #{{ str_pad($activeSession->id, 5, '0', STR_PAD_LEFT) }}
        </span>
    </div>

    <!-- Table Container -->
    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>Waktu</x-pos.table.th>
                    <x-pos.table.th>No. Transaksi</x-pos.table.th>
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
                            {{ $tx->created_at->format('d M Y, H:i') }}
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap font-mono text-xs font-bold text-slate-900 dark:text-slate-100">
                            {{ $tx->transaction_no }}
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap">
                            @if($tx->type === 'in')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-450 border border-emerald-100 dark:border-emerald-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Kas Masuk
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
                            @else
                                {{ $tx->destinationAccount ? $tx->destinationAccount->name : 'N/A' }}
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td class="text-xs font-medium text-slate-555 dark:text-slate-400 max-w-xs truncate" title="{{ $tx->notes }}">
                            {{ $tx->notes ?: '-' }}
                        </x-pos.table.td>
                        <x-pos.table.td class="whitespace-nowrap text-right font-bold text-sm {{ $tx->type === 'in' ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-600 dark:text-rose-450' }}">
                            {{ $tx->type === 'in' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="6" icon="ph-receipt-x" message="Belum ada transaksi kas tercatat di sesi ini." />
                @endforelse
            </tbody>
        </x-pos.table>
    </x-pos.table.container>

    <!-- Footer (Filament v3 Style Pagination) -->
    @if ($transactions && $transactions->total() > 0)
        <div class="px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
            <!-- Left: Statistics & Per Page -->
            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-550 dark:text-slate-400">
                <div>
                    Menampilkan
                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->firstItem() }}</span>
                    sampai
                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->lastItem() }}</span>
                    dari
                    <span class="font-semibold text-slate-850 dark:text-slate-200">{{ $transactions->total() }}</span>
                    hasil
                </div>
            </div>

            <!-- Right: Navigation Pages -->
            @if ($transactions->hasPages())
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end gap-1">
                    {{-- Previous Page Button --}}
                    @if ($transactions->onFirstPage())
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-650 cursor-not-allowed">
                            <i class="ph-bold ph-caret-left text-sm"></i>
                        </span>
                    @else
                        <button
                            wire:click="previousPage"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                        >
                            <i class="ph-bold ph-caret-left text-sm"></i>
                        </button>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                        @if ($page == $transactions->currentPage())
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <button
                                wire:click="gotoPage({{ $page }})"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white text-sm font-semibold transition duration-150 cursor-pointer"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach

                    {{-- Next Page Button --}}
                    @if ($transactions->hasMorePages())
                        <button
                            wire:click="nextPage"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-white transition duration-150 cursor-pointer"
                        >
                            <i class="ph-bold ph-caret-right text-sm"></i>
                        </button>
                    @else
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-655 cursor-not-allowed">
                            <i class="ph-bold ph-caret-right text-sm"></i>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    @endif
</div>
