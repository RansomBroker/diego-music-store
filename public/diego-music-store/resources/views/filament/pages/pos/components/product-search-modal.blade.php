@props([
    'show',
    'products',
    'activeCategory',
    'search',
    'selectedBranchId',
    'selectedPricingTierId' => null,
    'cart' => [],
    'categoryCounts' => [],
])

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" wire:click.self="closeProductSearch">
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full max-w-5xl max-h-[85vh] shadow-2xl transition-all border border-slate-100 dark:border-slate-700 mx-4 relative flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-955 dark:text-white">Tambah Produk</h3>
                    <p class="text-xs text-slate-700 dark:text-slate-300 font-semibold mt-0.5">Cari dan pilih produk untuk ditambahkan ke keranjang</p>
                </div>
                <button wire:click="closeProductSearch" class="w-8 h-8 rounded-full bg-slate-150 hover:bg-slate-200 dark:bg-slate-700 text-slate-650 hover:text-slate-955 dark:text-slate-300 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Search & Categories -->
            <div class="px-6 pt-5 pb-3 flex-shrink-0 space-y-4">
                <!-- Search Input -->
                <div class="relative">
                    <i class="ph ph-magnifying-glass text-slate-600 dark:text-slate-300 absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold"></i>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari barang, SKU atau barcode..." 
                        class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-950 dark:text-white placeholder-slate-655 dark:placeholder-slate-400 focus:ring-2 focus:ring-primary/20 dark:focus:ring-blue-500/20 focus:border-primary dark:focus:border-blue-500 outline-none transition-all"
                        autofocus
                    >
                </div>

                <!-- Category Tabs -->
                <x-pos-page::category-list :activeCategory="$activeCategory" :categoryCounts="$categoryCounts" />
            </div>

            <!-- Products Grid (scrollable) -->
            <div class="flex-1 overflow-y-auto px-6 pb-6 no-scrollbar">
                @if ($products->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                        <i class="ph ph-package text-6xl mb-3 opacity-40"></i>
                        <span class="text-sm font-medium">Tidak ada produk ditemukan</span>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach ($products as $variant)
                            <x-pos-page::product-card 
                                :variant="$variant" 
                                :selectedBranchId="$selectedBranchId" 
                                :selectedPricingTierId="$selectedPricingTierId" 
                                :qtyInCart="$cart[$variant->id]['qty'] ?? 0"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
