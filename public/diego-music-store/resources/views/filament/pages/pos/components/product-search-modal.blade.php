@props([
    'show',
    'products',
    'activeCategory',
    'search',
    'selectedBranchId',
    'selectedPricingTierId' => null,
    'cart' => [],
    'categoryCounts' => [],
    'hasMoreProducts' => false,
    'availableCategories' => [],
    'viewMode' => 'grid',
])

@if ($show)
    <div 
        x-init="$nextTick(() => { $refs.searchInput.focus(); $refs.searchInput.select() })"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" 
        wire:click.self="closeProductSearch"
    >
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-5xl max-h-[85vh] shadow-2xl transition-all border border-slate-100 dark:border-slate-700 mx-4 relative flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-955 dark:text-white">Tambah Produk</h3>
                    <p class="text-xs text-slate-700 dark:text-slate-300 font-semibold mt-0.5">Cari dan pilih produk untuk ditambahkan ke keranjang</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex bg-slate-100 dark:bg-slate-900 rounded-lg p-1 border border-slate-200 dark:border-slate-700">
                        <button 
                            wire:click="setViewMode('grid')"
                            class="px-2 py-1 rounded-md text-sm transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-800 shadow-sm text-primary dark:text-blue-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                            title="Tampilan Grid"
                        >
                            <i class="ph-fill ph-squares-four text-lg"></i>
                        </button>
                        <button 
                            wire:click="setViewMode('list')"
                            class="px-2 py-1 rounded-md text-sm transition-all {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-800 shadow-sm text-primary dark:text-blue-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                            title="Tampilan List"
                        >
                            <i class="ph-fill ph-list text-lg"></i>
                        </button>
                    </div>
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
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari barang, SKU atau barcode..." 
                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-950 dark:text-white placeholder-slate-655 dark:placeholder-slate-400 focus:ring-2 focus:ring-primary/20 dark:focus:ring-blue-500/20 focus:border-primary dark:focus:border-blue-500 outline-none transition-all"
                    >
                </div>

                <!-- Category Tabs -->
                <x-pos-page::category-list 
                    :activeCategory="$activeCategory" 
                    :categoryCounts="$categoryCounts"
                    :availableCategories="$availableCategories"
                />
            </div>

            <!-- Products Grid (scrollable) - Infinite scroll via native scroll listener -->
            <div
                class="flex-1 overflow-y-auto px-6 pb-6 no-scrollbar"
                x-data="{ triggered: false }"
                x-init="
                    const container = $el;
                    const wire = $wire;

                    function onScroll() {
                        if (triggered) return;
                        const nearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 250;
                        if (nearBottom) {
                            triggered = true;
                            container.removeEventListener('scroll', onScroll);
                            wire.loadMoreProducts();
                        }
                    }

                    // Pasang scroll listener hanya jika ada lebih banyak produk
                    if ({{ $hasMoreProducts ? 'true' : 'false' }}) {
                        container.addEventListener('scroll', onScroll, { passive: true });
                    }
                "
            >
                @if ($products->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                        <i class="ph ph-package text-6xl mb-3 opacity-40"></i>
                        <span class="text-sm font-medium">Tidak ada produk ditemukan</span>
                    </div>
                @else
                    @if ($viewMode === 'grid')
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($products as $variant)
                                <x-pos.product-card 
                                    :variant="$variant" 
                                    :selectedBranchId="$selectedBranchId" 
                                    :selectedPricingTierId="$selectedPricingTierId" 
                                    :qtyInCart="$cart[$variant->id]['qty'] ?? 0"
                                    clickAction="addToCart"
                                />
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach ($products as $variant)
                                <x-pos.product-list-item 
                                    :variant="$variant" 
                                    :selectedBranchId="$selectedBranchId" 
                                    :selectedPricingTierId="$selectedPricingTierId" 
                                    :qtyInCart="$cart[$variant->id]['qty'] ?? 0"
                                    clickAction="addToCart"
                                />
                            @endforeach
                        </div>
                    @endif

                    @if ($hasMoreProducts)
                        <!-- Tombol fallback + indicator scroll -->
                        <div class="w-full pt-5 pb-2 flex flex-col items-center gap-3">
                            <button
                                wire:click="loadMoreProducts"
                                wire:loading.attr="disabled"
                                wire:target="loadMoreProducts"
                                class="flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors border border-slate-200 dark:border-slate-600 disabled:opacity-50 disabled:cursor-wait cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="loadMoreProducts">
                                    <i class="ph ph-arrow-down mr-1"></i>
                                    Muat lebih banyak produk
                                </span>
                                <span wire:loading wire:target="loadMoreProducts" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Memuat...
                                </span>
                            </button>
                        </div>
                    @else
                        <p class="text-center text-xs text-slate-400 font-medium py-4">
                            <i class="ph ph-check-circle mr-1 text-emerald-400"></i>
                            Semua {{ $products->count() }} produk sudah ditampilkan
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
