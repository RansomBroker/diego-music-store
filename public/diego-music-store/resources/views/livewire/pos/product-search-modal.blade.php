@php
    if (!function_exists('getCategoryEmoji')) {
        function getCategoryEmoji(string $cat): string {
            $c = strtolower($cat);
            if (str_contains($c, 'gitar') || str_contains($c, 'guitar') || str_contains($c, 'bass') || str_contains($c, 'ukulele') || str_contains($c, 'cajon') || str_contains($c, 'harmonica') || str_contains($c, 'kalimba') || str_contains($c, 'pianika') || str_contains($c, 'suling') || str_contains($c, 'tamborin')) return '🎸';
            if (str_contains($c, 'keyboard') || str_contains($c, 'piano') || str_contains($c, 'saxophone') || str_contains($c, 'footstool')) return '🎹';
            if (str_contains($c, 'drum') || str_contains($c, 'stick') || str_contains($c, 'perkusi') || str_contains($c, 'metronom')) return '🥁';
            if (str_contains($c, 'senar') || str_contains($c, 'kabel') || str_contains($c, 'jack') || str_contains($c, 'strap') || str_contains($c, 'pick') || str_contains($c, 'capo')) return '🎵';
            if (str_contains($c, 'amplifier') || str_contains($c, 'speaker') || str_contains($c, 'mixer') || str_contains($c, 'audio') || str_contains($c, 'soundcard') || str_contains($c, 'recording') || str_contains($c, 'microphone') || str_contains($c, 'wireless')) return '🔊';
            if (str_contains($c, 'effects') || str_contains($c, 'pedal') || str_contains($c, 'preamp') || str_contains($c, 'pedalboard')) return '🎛️';
            if (str_contains($c, 'biola')) return '🎻';
            if (str_contains($c, 'stand')) return '🗿';
            if (str_contains($c, 'tas') || str_contains($c, 'gigbag') || str_contains($c, 'hardcase')) return '🎒';
            if (str_contains($c, 'headphone') || str_contains($c, 'in ear')) return '🎧';
            if (str_contains($c, 'ongkos')) return '🛠️';
            if (str_contains($c, 'cleaner') || str_contains($c, 'tools')) return '🔧';
            return '🎼';
        }
    }
@endphp

<div
    x-data="{
        cartQuantities: @js($cartQuantities),
        categoryCounts: @js($categoryCounts),
        qtyTimers: {},
        qtyFor(id) {
            return parseInt(this.cartQuantities[String(id)] ?? 0, 10) || 0;
        },
        catCount(cat) {
            return parseInt(this.categoryCounts[cat] ?? 0, 10) || 0;
        },
        addProduct(variantId, maxStock, isService) {
            const key = String(variantId);
            const current = this.qtyFor(variantId);
            if (!isService && maxStock <= current) {
                Livewire.dispatch('pos-add-to-cart', { variantId: variantId });
                return;
            }
            this.cartQuantities[key] = current + 1;
            Livewire.dispatch('pos-add-to-cart', { variantId: variantId });
        },
        changeProductQty(variantId, diff, maxStock, isService) {
            const key = String(variantId);
            const current = this.qtyFor(variantId);
            let next = current + diff;
            if (next < 0) next = 0;
            if (!isService && diff > 0 && next > maxStock) {
                Livewire.dispatch('pos-set-cart-qty', { variantId: variantId, qty: next });
                return;
            }
            this.cartQuantities[key] = next;
            clearTimeout(this.qtyTimers[key]);
            this.qtyTimers[key] = setTimeout(() => {
                Livewire.dispatch('pos-set-cart-qty', { variantId: variantId, qty: next });
            }, 250);
        }
    }"
    @pos-cart-synced.window="cartQuantities = $event.detail.quantities || {}; categoryCounts = $event.detail.categoryCounts || { Semua: 0 };"
>
    @if ($show)
        @php
            $products = $this->products;
            $availableCategories = $this->availableCategories;
            $pinnedCats = [];
            $otherCats = [];
            foreach ($availableCategories as $cat) {
                if (in_array($cat['name'], $visibleCategoryTabs, true)) {
                    $pinnedCats[] = $cat;
                } else {
                    $otherCats[] = $cat;
                }
            }
            $sortedCats = array_merge($pinnedCats, $otherCats);
        @endphp

        <div
            wire:key="pos-product-search-overlay"
            x-data="{
                closing: false,
                close() {
                    if (this.closing) return;
                    this.closing = true;
                    $wire.closeModal();
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
                    if (window.innerWidth >= 1024) cols = 4;
                    else if (window.innerWidth >= 640) cols = 3;
                    else cols = 2;

                    if (!document.querySelector('.pos-modal-grid')) cols = 1;

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
                                type="button"
                                wire:click="setViewMode('grid')"
                                class="px-2 py-1 rounded-md text-sm transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-800 shadow-sm text-primary dark:text-blue-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300' }}"
                                title="Tampilan Grid"
                            >
                                <i class="ph-fill ph-squares-four text-lg"></i>
                            </button>
                            <button
                                type="button"
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

                        {{-- Filter Categories Button --}}
                        <div x-data="{ open: false }" class="relative hidden sm:block">
                            <button
                                type="button"
                                @click="open = !open"
                                class="flex items-center justify-center px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors gap-1.5 text-xs font-semibold"
                                title="Atur Kategori yang Tampil di Depan"
                            >
                                <i class="ph ph-push-pin text-base"></i>
                                <span>Pin Kategori</span>
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-[60] overflow-hidden"
                                style="display: none;"
                            >
                                <div class="p-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">Pin Kategori (Max 6)</h4>
                                </div>
                                <div class="p-2 max-h-60 overflow-y-auto">
                                    @foreach ($availableCategories as $catConfig)
                                        <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer {{ (count($visibleCategoryTabs) >= 6 && !in_array($catConfig['name'], $visibleCategoryTabs, true)) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                            <input type="checkbox" wire:model.live="visibleCategoryTabs" value="{{ $catConfig['name'] }}"
                                                @if(count($visibleCategoryTabs) >= 6 && !in_array($catConfig['name'], $visibleCategoryTabs, true)) disabled @endif
                                                class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 disabled:opacity-50">
                                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $catConfig['name'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
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
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center gap-2 overflow-x-auto pb-2 flex-1">
                            <button
                                type="button"
                                wire:click="setCategory('Semua')"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-wait"
                                class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 flex-shrink-0 cursor-pointer
                                    {{ $activeCategory === 'Semua'
                                        ? 'bg-primary text-white shadow-md shadow-blue-500/20'
                                        : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-primary dark:hover:border-blue-500 hover:text-primary dark:hover:text-blue-400' }}"
                            >
                                <span>🌐</span>
                                <span>Semua</span>
                                <span
                                    x-show="catCount('Semua') > 0"
                                    x-text="catCount('Semua')"
                                    x-cloak
                                    class="px-1.5 py-0.5 {{ $activeCategory === 'Semua' ? 'bg-white text-primary' : 'bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-400' }} text-[10px] font-black rounded-full leading-none"
                                ></span>
                            </button>

                            @foreach ($sortedCats as $cat)
                                @php
                                    $catName  = $cat['name'];
                                    $emoji    = getCategoryEmoji($catName);
                                    $isActive = $activeCategory === $catName;
                                @endphp
                                <button
                                    type="button"
                                    wire:key="modal-cat-tab-{{ \Illuminate\Support\Str::slug($catName) }}"
                                    wire:click="setCategory('{{ $catName }}')"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-70 cursor-wait"
                                    class="px-4 py-2 rounded-xl text-sm whitespace-nowrap transition-all flex items-center gap-1.5 flex-shrink-0 cursor-pointer
                                        {{ $isActive
                                            ? 'bg-primary text-white font-semibold shadow-md shadow-blue-500/20'
                                            : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-medium hover:border-primary dark:hover:border-blue-500 hover:text-primary dark:hover:text-blue-400' }}"
                                >
                                    <span>{{ $emoji }}</span>
                                    <span>{{ $catName }}</span>
                                    <span
                                        x-show="catCount(@js($catName)) > 0"
                                        x-text="catCount(@js($catName))"
                                        x-cloak
                                        class="px-1.5 py-0.5 {{ $isActive ? 'bg-white text-primary' : 'bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-400' }} text-[10px] font-black rounded-full leading-none"
                                    ></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Product Grid / List -->
                <div class="flex-1 overflow-y-auto px-6 pb-6">
                    @if ($products->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <i class="ph ph-package text-6xl mb-3 opacity-40"></i>
                            <span class="text-sm font-medium">Tidak ada produk ditemukan</span>
                        </div>
                    @elseif ($viewMode === 'grid')
                        <div class="pos-modal-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($products as $variant)
                                @php
                                    $product = $variant->product;
                                    $stock = $branchId ? $variant->stockForBranch((int) $branchId) : $variant->totalStock();
                                    $isService = $product->isService();
                                    $isBundle = $product->isBundle();
                                    $isOutOfStock = !$isService && $stock <= 0;
                                    $emoji = $isService ? '🛠️' : ($isBundle ? '📦' : '🎸');
                                    $nameLower = strtolower($product->name . ' ' . ($variant->name ?? ''));
                                    if (str_contains($nameLower, 'drum') || str_contains($nameLower, 'stick')) {
                                        $emoji = '🥁';
                                    } elseif (str_contains($nameLower, 'keyboard') || str_contains($nameLower, 'piano')) {
                                        $emoji = '🎹';
                                    } elseif (str_contains($nameLower, 'senar') || str_contains($nameLower, 'kabel') || str_contains($nameLower, 'jack')) {
                                        $emoji = '🔌';
                                    }
                                    $price = ($pricingTierId && is_numeric($pricingTierId)) ? $variant->priceForTier((int) $pricingTierId) : $variant->price;
                                @endphp
                                <div
                                    wire:key="modal-prod-card-{{ $variant->id }}"
                                    tabindex="0"
                                    @if (!$isOutOfStock)
                                        @click="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                        @keydown.enter.prevent="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                    @endif
                                    :class="qtyFor({{ $variant->id }}) > 0 && !{{ $isOutOfStock ? 'true' : 'false' }} ? 'bg-blue-50/30 dark:bg-blue-950/20 border-primary dark:border-blue-500 shadow-blue-500/5' : ''"
                                    class="pos-product-item outline-none focus:ring-4 focus:ring-primary/50 focus:border-primary p-3 rounded-2xl shadow-sm border transition-all flex flex-col justify-between {{ $isOutOfStock ? 'opacity-40 saturate-50 bg-slate-100/60 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 pointer-events-none cursor-not-allowed select-none' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-md hover:border-primary dark:hover:border-blue-500 cursor-pointer group' }}"
                                >
                                    <div>
                                        <div class="aspect-[4/3] w-full bg-slate-100 dark:bg-slate-700 rounded-xl mb-3 flex items-center justify-center text-4xl group-hover:scale-[1.02] transition-transform relative overflow-hidden">
                                            {{ $emoji }}
                                            <span
                                                x-show="qtyFor({{ $variant->id }}) > 0"
                                                x-text="qtyFor({{ $variant->id }})"
                                                x-cloak
                                                class="absolute top-2 right-2 bg-primary text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md"
                                            ></span>
                                        </div>
                                        <div class="px-1">
                                            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1 line-clamp-2" title="{{ $product->name }} {{ $variant->name ? '(' . $variant->name . ')' : '' }}">
                                                {{ $product->name }}
                                                @if ($variant->name)
                                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">({{ $variant->name }})</span>
                                                @endif
                                            </h3>

                                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                                @if ($product->category)
                                                    <span class="text-[9px] uppercase tracking-wider font-black text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $product->category }}</span>
                                                @endif
                                                @if ($variant->sku)
                                                    <span class="text-[9px] uppercase tracking-wider font-black text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded"><i class="ph-bold ph-barcode mr-0.5"></i>{{ $variant->sku }}</span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                @if ($isService)
                                                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-md font-bold border border-indigo-200 dark:border-indigo-800">Jasa</span>
                                                @elseif ($isBundle)
                                                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold border border-purple-200 dark:border-purple-800">Paket</span>
                                                    <span class="text-[10px] {{ $stock > 0 ? 'bg-slate-150 dark:bg-slate-700 text-slate-800 dark:text-slate-200' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                                                @else
                                                    <span class="text-[10px] bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold border border-blue-200 dark:border-blue-800">Fisik</span>
                                                    <span class="text-[10px] {{ $stock > 0 ? 'bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-1 mt-4 flex items-center justify-between gap-1 flex-wrap">
                                        <span class="font-bold text-primary dark:text-blue-400 text-sm whitespace-nowrap">Rp {{ number_format($price, 0, ',', '.') }}</span>

                                        @if ($isOutOfStock)
                                            <span class="text-xs font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1.5 rounded-xl border border-rose-200/50 dark:border-rose-900/40">Habis</span>
                                        @else
                                            <div
                                                x-show="qtyFor({{ $variant->id }}) > 0"
                                                x-cloak
                                                @click.stop
                                                class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-250 dark:border-slate-700 rounded-lg p-0.5"
                                            >
                                                <button
                                                    type="button"
                                                    @click.stop="changeProductQty({{ $variant->id }}, -1, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                    class="rounded-full w-6 h-6 p-0 bg-white hover:bg-red-500 text-slate-700 hover:text-white dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-red-650 dark:hover:text-white flex items-center justify-center flex-shrink-0 transition-colors cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-minus text-[10px]"></i>
                                                </button>
                                                <span x-text="qtyFor({{ $variant->id }})" class="w-4 text-center text-xs font-black text-slate-800 dark:text-white"></span>
                                                <button
                                                    type="button"
                                                    @click.stop="changeProductQty({{ $variant->id }}, 1, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                    class="rounded-full w-6 h-6 p-0 bg-white hover:bg-primary text-slate-700 hover:text-white dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary dark:hover:text-white flex items-center justify-center flex-shrink-0 transition-colors cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-plus text-[10px]"></i>
                                                </button>
                                            </div>
                                            <button
                                                type="button"
                                                x-show="qtyFor({{ $variant->id }}) === 0"
                                                @click.stop="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                class="rounded-full w-8 h-8 p-0 bg-primary-light hover:bg-primary text-primary hover:text-white dark:bg-blue-950/60 dark:text-blue-400 dark:hover:bg-primary dark:hover:text-white flex items-center justify-center flex-shrink-0 transition-colors cursor-pointer"
                                            >
                                                <i class="ph-bold ph-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach ($products as $variant)
                                @php
                                    $product = $variant->product;
                                    $stock = $branchId ? $variant->stockForBranch((int) $branchId) : $variant->totalStock();
                                    $isService = $product->isService();
                                    $isBundle = $product->isBundle();
                                    $isOutOfStock = !$isService && $stock <= 0;
                                    $emoji = $isService ? '🛠️' : ($isBundle ? '📦' : '🎸');
                                    $nameLower = strtolower($product->name . ' ' . ($variant->name ?? ''));
                                    if (str_contains($nameLower, 'drum') || str_contains($nameLower, 'stick')) {
                                        $emoji = '🥁';
                                    } elseif (str_contains($nameLower, 'keyboard') || str_contains($nameLower, 'piano')) {
                                        $emoji = '🎹';
                                    } elseif (str_contains($nameLower, 'senar') || str_contains($nameLower, 'kabel') || str_contains($nameLower, 'jack')) {
                                        $emoji = '🔌';
                                    }
                                    $price = ($pricingTierId && is_numeric($pricingTierId)) ? $variant->priceForTier((int) $pricingTierId) : $variant->price;
                                @endphp
                                <div
                                    wire:key="modal-prod-list-{{ $variant->id }}"
                                    tabindex="0"
                                    @if (!$isOutOfStock)
                                        @click="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                        @keydown.enter.prevent="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                    @endif
                                    :class="qtyFor({{ $variant->id }}) > 0 && !{{ $isOutOfStock ? 'true' : 'false' }} ? 'bg-blue-50/30 dark:bg-blue-950/20 border-primary dark:border-blue-500 shadow-blue-500/5' : ''"
                                    class="pos-product-item outline-none focus:ring-4 focus:ring-primary/50 focus:border-primary p-3 rounded-2xl shadow-sm border transition-all flex items-center justify-between gap-4 {{ $isOutOfStock ? 'opacity-40 saturate-50 bg-slate-100/60 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 pointer-events-none cursor-not-allowed select-none' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-md hover:border-primary dark:hover:border-blue-500 cursor-pointer group' }}"
                                >
                                    <div class="flex items-center gap-4 flex-1 overflow-hidden">
                                        <div class="w-14 h-14 shrink-0 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center text-3xl group-hover:scale-105 transition-transform relative overflow-hidden">
                                            {{ $emoji }}
                                            <span
                                                x-show="qtyFor({{ $variant->id }}) > 0"
                                                x-text="qtyFor({{ $variant->id }})"
                                                x-cloak
                                                class="absolute top-1 right-1 bg-primary text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-md"
                                            ></span>
                                        </div>

                                        <div class="min-w-0">
                                            <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate mb-1" title="{{ $product->name }} {{ $variant->name ? '(' . $variant->name . ')' : '' }}">
                                                {{ $product->name }}
                                                @if ($variant->name)
                                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">({{ $variant->name }})</span>
                                                @endif
                                            </h3>

                                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                                @if ($product->category)
                                                    <span class="text-[9px] uppercase tracking-wider font-black text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $product->category }}</span>
                                                @endif
                                                @if ($variant->sku)
                                                    <span class="text-[9px] uppercase tracking-wider font-black text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded"><i class="ph-bold ph-barcode mr-0.5"></i>{{ $variant->sku }}</span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if ($isService)
                                                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-md font-bold border border-indigo-200 dark:border-indigo-800">Jasa</span>
                                                @elseif ($isBundle)
                                                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold border border-purple-200 dark:border-purple-800">Paket</span>
                                                    <span class="text-[10px] {{ $stock > 0 ? 'bg-slate-150 dark:bg-slate-700 text-slate-800 dark:text-slate-200' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                                                @else
                                                    <span class="text-[10px] bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold border border-blue-200 dark:border-blue-800">Fisik</span>
                                                    <span class="text-[10px] {{ $stock > 0 ? 'bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 shrink-0">
                                        <span class="font-bold text-primary dark:text-blue-400 text-sm whitespace-nowrap">Rp {{ number_format($price, 0, ',', '.') }}</span>

                                        @if ($isOutOfStock)
                                            <span class="text-xs font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1.5 rounded-xl border border-rose-200/50 dark:border-rose-900/40">Habis</span>
                                        @else
                                            <div
                                                x-show="qtyFor({{ $variant->id }}) > 0"
                                                x-cloak
                                                @click.stop
                                                class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-250 dark:border-slate-700 rounded-lg p-0.5 select-none"
                                            >
                                                <button
                                                    type="button"
                                                    @click.stop="changeProductQty({{ $variant->id }}, -1, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                    class="rounded-full w-7 h-7 p-0 bg-white hover:bg-red-500 text-slate-700 hover:text-white dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-red-650 dark:hover:text-white flex items-center justify-center flex-shrink-0 active:scale-90 transition-all cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-minus text-xs"></i>
                                                </button>
                                                <span x-text="qtyFor({{ $variant->id }})" class="w-5 text-center text-xs font-black text-slate-800 dark:text-white"></span>
                                                <button
                                                    type="button"
                                                    @click.stop="changeProductQty({{ $variant->id }}, 1, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                    class="rounded-full w-7 h-7 p-0 bg-white hover:bg-primary text-slate-700 hover:text-white dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-primary dark:hover:text-white flex items-center justify-center flex-shrink-0 active:scale-90 transition-all cursor-pointer"
                                                >
                                                    <i class="ph-bold ph-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <button
                                                type="button"
                                                x-show="qtyFor({{ $variant->id }}) === 0"
                                                @click.stop="addProduct({{ $variant->id }}, {{ (int) $stock }}, {{ $isService ? 'true' : 'false' }})"
                                                class="rounded-full w-9 h-9 p-0 bg-primary-light hover:bg-primary text-primary hover:text-white dark:bg-blue-950/60 dark:text-blue-400 dark:hover:bg-primary dark:hover:text-white flex items-center justify-center flex-shrink-0 active:scale-90 transition-all cursor-pointer"
                                            >
                                                <i class="ph-bold ph-plus text-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
</div>

