<!-- Print Queue Table Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden">
    <!-- Header & Table Search Toolbar -->
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="ph-bold ph-barcode text-primary"></i>
            Daftar Produk Untuk Dicetak ({{ count($printQueue) }})
        </h3>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <!-- Search Table Input -->
            <div class="relative flex-1 sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="ph ph-magnifying-glass text-slate-400 text-xs"></i>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="queueSearch"
                    placeholder="Cari di tabel..."
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                >
            </div>

            <x-pos.utility.button
                type="button"
                wire:click="openProductModal"
                variant="primary"
                size="xs"
                icon="ph-plus"
            >
                Tambah
            </x-pos.utility.button>

            @if (!empty($printQueue))
                <x-pos.utility.button
                    type="button"
                    wire:click="clearQueue"
                    variant="danger"
                    size="xs"
                    icon="ph-trash"
                >
                    Kosongkan
                </x-pos.utility.button>
            @endif
        </div>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <x-pos.table.th>Produk / Varian</x-pos.table.th>
                    <x-pos.table.th>SKU & Barcode</x-pos.table.th>
                    <x-pos.table.th class="text-center">Jumlah Label</x-pos.table.th>
                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($filteredQueue as $variantId => $item)
                    <x-pos.table.tr>
                        <x-pos.table.td class="font-bold text-sm text-slate-900 dark:text-white">
                            {{ $item['name'] }}
                            <div class="text-xs font-normal text-slate-400">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        </x-pos.table.td>
                        <x-pos.table.td class="font-mono text-xs text-slate-600 dark:text-slate-300">
                            <div class="space-y-0.5">
                                @if (!empty($item['sku']))
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 dark:bg-slate-800 px-1 py-0.2 rounded">SKU</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item['sku'] }}</span>
                                    </div>
                                @endif

                                @if (!empty($item['barcode']))
                                    <div class="flex items-center gap-1">
                                        <span class="text-[10px] font-bold text-blue-500 uppercase bg-blue-50 dark:bg-blue-950/40 px-1 py-0.2 rounded">BARCODE</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $item['barcode'] }}</span>
                                    </div>
                                @endif

                                @if (empty($item['sku']) && empty($item['barcode']))
                                    <span class="text-slate-400 italic">SKU-{{ $variantId }}</span>
                                @endif
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-center">
                            <div class="inline-flex items-center gap-1">
                                <button
                                    wire:click="updateQty({{ $variantId }}, {{ $item['qty'] - 1 }})"
                                    class="w-7 h-7 rounded border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 font-bold flex items-center justify-center text-xs cursor-pointer"
                                >-</button>
                                <input
                                    type="number"
                                    value="{{ $item['qty'] }}"
                                    wire:change="updateQty({{ $variantId }}, $event.target.value)"
                                    class="w-12 text-center py-1 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold"
                                >
                                <button
                                    wire:click="updateQty({{ $variantId }}, {{ $item['qty'] + 1 }})"
                                    class="w-7 h-7 rounded border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 font-bold flex items-center justify-center text-xs cursor-pointer"
                                >+</button>
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right">
                            <x-pos.utility.button
                                type="button"
                                variant="danger"
                                size="xs"
                                icon="ph-trash"
                                wire:click="removeVariant({{ $variantId }})"
                                title="Hapus"
                            >
                                Hapus
                            </x-pos.utility.button>
                        </x-pos.table.td>
                    </x-pos.table.tr>
                @empty
                    <x-pos.table.empty colspan="4" icon="ph-barcode" message="{{ !empty($queueSearch) ? 'Tidak ada item antrean yang cocok dengan kata kunci pencarian.' : 'Belum ada produk yang dipilih. Klik tombol \'Tambah Produk\' di atas.' }}" />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :total="count($filteredQueue)" />
    </x-pos.table.container>

    @if (!empty($printQueue))
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end">
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="base"
                icon="ph-printer"
                wire:click="triggerPrint"
            >
                Cetak {{ array_sum(array_column($printQueue, 'qty')) }} Label Barcode
            </x-pos.utility.button>
        </div>
    @endif
</div>
