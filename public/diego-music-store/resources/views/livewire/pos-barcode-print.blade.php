<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Cetak Barcode Label Produk"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-400 dark:text-slate-500">Utility</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Cetak Barcode</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Cetak Barcode Produk</h1>
                    </div>

                    <!-- Action Buttons Header -->
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="addAllProducts"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-bold rounded-xl transition cursor-pointer"
                        >
                            <i class="ph-bold ph-squares-four text-base text-primary dark:text-blue-400"></i>
                            <span>Tambah Semua Produk</span>
                        </button>

                        <button
                            wire:click="openProductModal"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition cursor-pointer"
                        >
                            <i class="ph-bold ph-plus-circle text-lg"></i>
                            <span>Tambah Produk</span>
                        </button>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left Column: Queue Table & Action Bar -->
                    <div class="lg:col-span-2 space-y-6">
                        
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

                                    <button
                                        wire:click="openProductModal"
                                        class="text-xs font-bold text-primary dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer whitespace-nowrap"
                                    >
                                        <i class="ph-bold ph-plus"></i> Tambah
                                    </button>

                                    @if (!empty($printQueue))
                                        <span class="text-slate-300 dark:text-slate-700">|</span>
                                        <button
                                            wire:click="clearQueue"
                                            class="text-xs font-semibold text-rose-600 hover:underline cursor-pointer whitespace-nowrap"
                                        >
                                            Kosongkan
                                        </button>
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
                                                    <button
                                                        wire:click="removeVariant({{ $variantId }})"
                                                        class="text-rose-600 hover:underline text-xs font-semibold cursor-pointer"
                                                    >
                                                        <i class="ph-bold ph-trash"></i> Hapus
                                                    </button>
                                                </x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="4" icon="ph-barcode" message="{{ !empty($queueSearch) ? 'Tidak ada item antrean yang cocok dengan kata kunci pencarian.' : 'Belum ada produk yang dipilih. Klik tombol \'Tambah Produk\' di atas.' }}" />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>

                            @if (!empty($printQueue))
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                                    <button
                                        wire:click="triggerPrint"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                                    >
                                        <i class="ph-bold ph-printer text-base"></i>
                                        <span>Cetak {{ array_sum(array_column($printQueue, 'qty')) }} Label Barcode</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Settings & Live Preview -->
                    <div class="space-y-6">
                        
                        <!-- Layout Setting Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-5 space-y-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Format Kertas & Label</h3>
                            
                            <!-- Preset Selector -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Preset Tata Letak Stiker</label>
                                <select
                                    wire:model.live="paperLayout"
                                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                                >
                                    <option value="3col">3 Kolom per Baris (Stiker 33 x 18 mm)</option>
                                    <option value="2col">2 Kolom per Baris (Stiker 40 x 22 mm)</option>
                                    <option value="1col">1 Kolom / Label Thermal (50 x 30 mm)</option>
                                    <option value="custom">⚙️ Manual / Custom Setting</option>
                                </select>
                            </div>

                            <!-- Manual Dimensions Accordion / Grid -->
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                        <i class="ph-bold ph-sliders-horizontal text-primary"></i>
                                        Ukuran & Jarum Stiker (Manual)
                                    </span>
                                    @if ($paperLayout === 'custom')
                                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800">Mode Custom</span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Lebar Stiker (mm)</label>
                                        <input
                                            type="number"
                                            wire:model.live="labelWidth"
                                            wire:change="touchCustom"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Tinggi Stiker (mm)</label>
                                        <input
                                            type="number"
                                            wire:model.live="labelHeight"
                                            wire:change="touchCustom"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Kolom (1-5)</label>
                                        <input
                                            type="number"
                                            min="1"
                                            max="5"
                                            wire:model.live="columns"
                                            wire:change="touchCustom"
                                            class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Jarak X (mm)</label>
                                        <input
                                            type="number"
                                            wire:model.live="gapX"
                                            wire:change="touchCustom"
                                            class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Jarak Y (mm)</label>
                                        <input
                                            type="number"
                                            wire:model.live="gapY"
                                            wire:change="touchCustom"
                                            class="w-full px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white text-center"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Ukuran Font (px)</label>
                                        <input
                                            type="number"
                                            min="7"
                                            max="16"
                                            wire:model.live="fontSize"
                                            wire:change="touchCustom"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-0.5">Tinggi Barcode (px)</label>
                                        <input
                                            type="number"
                                            min="20"
                                            max="70"
                                            wire:model.live="barcodeHeight"
                                            wire:change="touchCustom"
                                            class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Content Toggles -->
                            <div class="space-y-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Informasi Ditampilkan</label>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" wire:model.live="showStoreName" class="w-4 h-4 rounded text-primary">
                                        <span>Nama Toko</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" wire:model.live="showProductName" class="w-4 h-4 rounded text-primary">
                                        <span>Nama Produk</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" wire:model.live="showPrice" class="w-4 h-4 rounded text-primary">
                                        <span>Harga Jual</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" wire:model.live="showCode" class="w-4 h-4 rounded text-primary">
                                        <span>Kode SKU</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Live Barcode Sticker Preview Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pratinjau Stiker (Dynamic)</h3>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $labelWidth }}x{{ $labelHeight }}mm | {{ $columns }} Col</span>
                            </div>
                            
                            <div
                                class="bg-white border-2 border-slate-900 rounded-lg text-slate-900 text-center mx-auto space-y-1 shadow-md overflow-hidden transition-all duration-200"
                                style="width: {{ min($labelWidth * 4, 260) }}px; padding: 6px;"
                            >
                                @if ($showStoreName)
                                    <div class="font-bold uppercase tracking-wider truncate" style="font-size: {{ max($fontSize - 2, 7) }}px;">Diego Music Store</div>
                                @endif
                                @if ($showProductName)
                                    <div class="font-bold truncate" style="font-size: {{ $fontSize }}px;">Gitar Yamaha F310</div>
                                @endif
                                
                                <div class="w-full flex items-center justify-center my-1" style="height: {{ $barcodeHeight }}px;">
                                    {!! \App\Helpers\BarcodeHelper::generateCode128Svg('SKU-10023', 180, $barcodeHeight) !!}
                                </div>

                                @if ($showCode)
                                    <div class="font-mono tracking-widest" style="font-size: {{ max($fontSize - 2, 7) }}px;">SKU-10023</div>
                                @endif
                                @if ($showPrice)
                                    <div class="font-extrabold border-t border-slate-300 pt-0.5" style="font-size: {{ $fontSize }}px;">Rp 1.750.000</div>
                                @endif
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- ===================== PRODUCT SEARCH MODAL (EXACT SAME AS POS KASIR) ===================== -->
    @if ($showProductModal)
        <div 
            x-data="{}"
            x-init="$nextTick(() => { $refs.searchInput.focus(); $refs.searchInput.select() })"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" 
            wire:click.self="closeProductSearch"
        >
            <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-5xl max-h-[85vh] shadow-2xl transition-all border border-slate-100 dark:border-slate-700 mx-4 relative flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-955 dark:text-white">Tambah Produk Barcode</h3>
                        <p class="text-xs text-slate-700 dark:text-slate-300 font-semibold mt-0.5">Cari dan pilih produk untuk ditambahkan ke antrean cetak</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="addAllProducts"
                            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white text-xs font-bold rounded-xl transition cursor-pointer"
                        >
                            <i class="ph-bold ph-plus-circle text-sm text-primary dark:text-blue-400"></i>
                            <span>Tambah Semua Produk</span>
                        </button>
                        <button wire:click="closeProductSearch" class="w-8 h-8 rounded-full bg-slate-150 hover:bg-slate-200 dark:bg-slate-700 text-slate-650 hover:text-slate-955 dark:text-slate-300 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Search & Categories -->
                <div class="px-6 pt-5 pb-3 flex-shrink-0 space-y-4">
                    <!-- Search Input -->
                    <div class="relative">
                        <i class="ph ph-magnifying-glass text-slate-600 dark:text-slate-300 absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold"></i>
                        <input 
                            type="text" 
                            x-ref="searchInput"
                            wire:model.live.debounce.300ms="modalSearch" 
                            placeholder="Cari barang, SKU atau barcode..." 
                            class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-950 dark:text-white placeholder-slate-655 dark:placeholder-slate-400 focus:ring-2 focus:ring-primary/20 dark:focus:ring-blue-500/20 focus:border-primary dark:focus:border-blue-500 outline-none transition-all"
                        >
                    </div>

                    <!-- Category Tabs -->
                    <x-pos-page::category-list :activeCategory="$activeCategory" />
                </div>

                <!-- Products Grid (scrollable) -->
                <div class="flex-1 overflow-y-auto px-6 pb-6 no-scrollbar">
                    @if ($modalProducts->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <i class="ph ph-package text-6xl mb-3 opacity-40"></i>
                            <span class="text-sm font-medium">Tidak ada produk ditemukan</span>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($modalProducts as $variant)
                                <x-pos.product-card 
                                    :variant="$variant" 
                                    :selectedBranchId="$selectedBranchId" 
                                    :qtyInCart="$printQueue[$variant->id]['qty'] ?? 0"
                                    clickAction="addVariant"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
