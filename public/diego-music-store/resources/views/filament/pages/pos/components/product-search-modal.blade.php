@props([
    'show',
    'products',
    'activeCategory',
    'search',
    'selectedBranchId',
    'selectedPricingTierId' => null,
    'cart' => [],
    'categoryCounts' => [],
    'availableCategories' => [],
    'viewMode' => 'grid',
])

@if ($show)
    <div 
        x-data="{ 
            closing: false,
            close() {
                if (this.closing) return;
                this.closing = true;
                $wire.closeProductSearch();
            },
            focusFirstProduct() {
                let first = document.querySelector('.pos-product-item');
                if (first) {
                    first.focus();
                }
            },
            navigateProducts(e) {
                let current = document.activeElement;
                if (!current || !current.classList.contains('pos-product-item')) return;
                
                let items = Array.from(document.querySelectorAll('.pos-product-item'));
                let index = items.indexOf(current);
                if (index === -1) return;

                let cols = 1;
                // Calculate grid columns approximately based on viewport
                if (window.innerWidth >= 1024) cols = 4; // lg:grid-cols-4
                else if (window.innerWidth >= 640) cols = 3; // sm:grid-cols-3
                else cols = 2; // grid-cols-2

                // Adjust for list mode
                if (!document.querySelector('.grid-cols-2')) cols = 1;

                if (e.key === 'ArrowRight') {
                    if (index + 1 < items.length) { items[index + 1].focus(); e.preventDefault(); }
                } else if (e.key === 'ArrowLeft') {
                    if (index - 1 >= 0) { items[index - 1].focus(); e.preventDefault(); }
                } else if (e.key === 'ArrowDown') {
                    if (index + cols < items.length) { items[index + cols].focus(); e.preventDefault(); }
                    else if (index + 1 < items.length) { items[items.length - 1].focus(); e.preventDefault(); }
                } else if (e.key === 'ArrowUp') {
                    if (index - cols >= 0) { items[index - cols].focus(); e.preventDefault(); }
                    else if (index >= 0) { this.$refs.searchInput.focus(); e.preventDefault(); }
                }
            }
        }"
        @keydown="navigateProducts($event)"
        x-show="!closing"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="close()"
        x-init="$nextTick(() => { $refs.searchInput?.focus(); $refs.searchInput?.select() })"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80" 
        @click.self="close()"
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
                    <div class="relative hidden sm:block">
                        <select wire:model.live="sortBy" class="appearance-none pl-3 pr-8 py-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-pointer">
                            <option value="name_asc">Nama (A-Z)</option>
                            <option value="name_desc">Nama (Z-A)</option>
                            <option value="price_asc">Harga Terendah</option>
                            <option value="price_desc">Harga Tertinggi</option>
                        </select>
                        <i class="ph-bold ph-caret-down absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>
                    <button type="button" @click="close()" class="w-8 h-8 rounded-full bg-slate-150 hover:bg-slate-200 dark:bg-slate-700 text-slate-650 hover:text-slate-955 dark:text-slate-300 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer" title="Tutup (Esc)">
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
                        @keydown.enter.prevent="focusFirstProduct()"
                        @keydown.arrow-down.prevent="focusFirstProduct()"
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

            <div
                class="flex-1 overflow-y-auto px-6 pb-6 no-scrollbar"
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
                                <div wire:key="pos-prod-card-{{ $variant->id }}">
                                    <x-pos.product-card 
                                        :variant="$variant" 
                                        :selectedBranchId="$selectedBranchId" 
                                        :selectedPricingTierId="$selectedPricingTierId" 
                                        :qtyInCart="$cart[$variant->id]['qty'] ?? 0"
                                        clickAction="addToCart"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach ($products as $variant)
                                <div wire:key="pos-prod-list-{{ $variant->id }}">
                                    <x-pos.product-list-item 
                                        :variant="$variant" 
                                        :selectedBranchId="$selectedBranchId" 
                                        :selectedPricingTierId="$selectedPricingTierId" 
                                        :qtyInCart="$cart[$variant->id]['qty'] ?? 0"
                                        clickAction="addToCart"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            <!-- Sticky Pagination -->
            @if ($products->isNotEmpty())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-800 shrink-0">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endif
