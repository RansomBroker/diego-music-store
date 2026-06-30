<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Controls Card using Native Filament Form Component -->
        <x-filament::section>
            <x-slot name="heading">
                Parameter Kartu Stok
            </x-slot>

            @if(($data['productVariantId'] ?? null) && ($data['branchId'] ?? null))
                <x-slot name="headerEnd">
                    <button 
                        wire:click="resetFilters" 
                        type="button" 
                        class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white font-semibold flex items-center gap-1 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Tampilkan Semua Log
                    </button>
                </x-slot>
            @endif
            
            {{ $this->form }}
        </x-filament::section>

        @php
            $cardData = $this->getStockCardData();
        @endphp

        <!-- DEFAULT STATE: Show standard Filament table when filters are not fully set -->
        @if(empty($cardData))
            <div class="space-y-4">
                {{ $this->table }}
            </div>

        <!-- ACTIVE FILTER STATE: Show detailed Stock Card with Running Balance -->
        @else
            <!-- Summary stats cards (Matching native Filament Stats Widgets styling) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Opening Stock -->
                <div class="p-5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold block">Stok Awal (Saldo Awal)</span>
                        <span class="text-lg font-black text-gray-900 dark:text-white font-mono mt-0.5">
                            {{ number_format($cardData['opening_stock'], 0, ',', '.') }}
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-0.5">{{ $cardData['variant']->product->unit->name ?? 'Unit' }}</span>
                        </span>
                    </div>
                </div>

                <!-- Total In -->
                <div class="p-5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold block">Total Masuk (IN)</span>
                        <span class="text-lg font-black text-green-600 dark:text-green-400 font-mono mt-0.5">
                            +{{ number_format($cardData['total_in'], 0, ',', '.') }}
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-0.5">{{ $cardData['variant']->product->unit->name ?? 'Unit' }}</span>
                        </span>
                    </div>
                </div>

                <!-- Total Out -->
                <div class="p-5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold block">Total Keluar (OUT)</span>
                        <span class="text-lg font-black text-rose-600 dark:text-rose-400 font-mono mt-0.5">
                            -{{ number_format($cardData['total_out'], 0, ',', '.') }}
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-0.5">{{ $cardData['variant']->product->unit->name ?? 'Unit' }}</span>
                        </span>
                    </div>
                </div>

                <!-- Closing Stock -->
                <div class="p-5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold block">Stok Akhir (Saldo Akhir)</span>
                        <span class="text-lg font-black text-blue-600 dark:text-blue-400 font-mono mt-0.5">
                            {{ number_format($cardData['closing_stock'], 0, ',', '.') }}
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-0.5">{{ $cardData['variant']->product->unit->name ?? 'Unit' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detailed Stock Ledger Card using Native Filament Section Component -->
            <x-filament::section>
                <x-slot name="heading">
                    Rincian Buku Mutasi Persediaan
                </x-slot>
                
                <x-slot name="description">
                    Riwayat pergerakan keluar masuk barang secara kronologis (Terbaru di atas)
                </x-slot>

                <x-slot name="headerEnd">
                    <span class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800/80 px-3 py-1 rounded border border-gray-200 dark:border-gray-700">
                        {{ $cardData['branch']->name }}
                    </span>
                </x-slot>

                <div class="overflow-x-auto -mx-6 -my-4">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 uppercase tracking-wider text-[10px] border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 font-bold">Tanggal & Waktu</th>
                                <th class="px-6 py-4 font-bold">Referensi Dokumen / Keterangan</th>
                                <th class="px-6 py-4 font-bold text-center">Tipe</th>
                                <th class="px-6 py-4 font-bold text-right">Mutasi Qty</th>
                                <th class="px-6 py-4 font-bold text-right">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <!-- Show Movements -->
                            @forelse($cardData['movements'] as $mv)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-850 transition-colors">
                                    <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-400 text-xs">
                                        {{ $mv['created_at']->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                                        {{ $mv['reference_label'] }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($mv['type'] === 'in')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                Masuk
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                                Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-sm {{ $mv['type'] === 'in' ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $mv['type'] === 'in' ? '+' : '-' }}{{ number_format($mv['quantity'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono font-extrabold text-sm text-gray-900 dark:text-white bg-gray-50/30 dark:bg-gray-800/10">
                                        {{ number_format($mv['running_balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                        Tidak ada riwayat pergerakan stok dalam rentang tanggal yang dipilih.
                                    </td>
                                </tr>
                            @endforelse

                            <!-- Opening Balance Row at the very bottom of table as the starting point -->
                            <tr class="bg-gray-50/30 dark:bg-gray-800/10 border-t border-gray-200 dark:border-gray-700 font-semibold text-gray-700 dark:text-gray-300">
                                <td class="px-6 py-4 text-xs font-mono">
                                    {{ \Carbon\Carbon::parse($data['startDate'] ?? now()->startOfMonth()->format('Y-m-d'))->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4" colspan="2">
                                    <em>SALDO AWAL (Opening Stock)</em>
                                </td>
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4 text-right font-mono font-extrabold text-sm text-gray-800 dark:text-gray-200">
                                    {{ number_format($cardData['opening_stock'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
