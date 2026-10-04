@props([
    'cart',
    'customerSearch',
    'customerLimit' => 20,
    'customers',
    'selectedCustomerId',
    'selectedCustomerName',
    'isLoyaltyMember',
    'subtotal',
    'discountAmount',
    'discountValue' => 0,
    'discountType' => 'percent',
    'taxAmount',
    'grandTotal',
    'pricingTiers' => [],
    'selectedPricingTierId' => null,
    'enableTax' => true,
    'taxPercent' => 11,
    'usePoints' => false,
    'customerPoints' => 0,
    'pointDiscountAmount' => 0,
    'heldTransactions' => [],
    'lastSaleId' => null,
    'salesSearch' => '',
    'selectedSalesRepId' => null,
    'selectedSalesRepName' => '',
    'saleCategory' => 'Store',
    'salesReps' => [],
    'saleCategories' => [],
    'editingSaleId' => null,
    'cartVariants' => []
])

<div class="flex-1 w-full bg-white dark:bg-slate-800 flex flex-col h-full transition-colors overflow-hidden">
    <!-- Cart Header -->
    <div class="p-3 sm:p-4 border-b border-slate-100 dark:border-slate-700">
        @if ($editingSaleId)
            <div class="mb-3 p-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/60 rounded-xl flex items-center justify-between gap-2 transition-colors duration-200">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-450">
                    <i class="ph-fill ph-warning-circle text-base animate-pulse"></i>
                    <div class="text-[10px] leading-tight">
                        <span class="font-extrabold block">Mode Edit Transaksi</span>
                        <span class="font-medium text-slate-500 dark:text-slate-400 block">Perubahan akan diperbarui saat checkout.</span>
                    </div>
                </div>
                <button 
                    type="button"
                    wire:click="cancelEdit"
                    class="px-2 py-1 text-[9px] font-black text-amber-700 hover:text-white bg-amber-100 hover:bg-amber-600 dark:bg-amber-900/40 dark:hover:bg-amber-600 rounded-lg transition-all"
                >
                    Batal Edit
                </button>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-2 mb-2">
            <div class="flex flex-col">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Transaksi Saat Ini</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs  font-bold text-slate-900 dark:text-slate-100">
                        {{ $this->previewInvoiceNumber }}
                    </span>
                    @if ($editingSaleId)
                        <x-pos.utility.pill variant="warning" size="xs">
                            Edit Mode
                        </x-pos.utility.pill>
                    @else
                        <x-pos.utility.pill variant="warning" size="xs">
                            Draft
                        </x-pos.utility.pill>
                    @endif
                </div>
            </div>
            
            <!-- Toolbar Utility Buttons with Labels -->
            <div class="flex items-stretch gap-2">
                <!-- Tombol Utama Tambah Produk (Expand 2 Baris) -->
                <button 
                    wire:click="openProductSearch"
                    type="button" 
                    title="Tambah Produk"
                    class="flex flex-col items-center justify-center bg-gradient-to-br from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold rounded-xl px-3 transition-all active:scale-[0.98] w-24 sm:w-28 flex-shrink-0 border border-emerald-400/20 group"
                >
                    <i class="ph-bold ph-plus text-xl sm:text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] sm:text-xs leading-tight text-center">Tambah<br>Produk</span>
                </button>

                <!-- Kumpulan Tombol Utility -->
                <div class="flex flex-col gap-2 justify-center flex-1">
                    <!-- Baris 1: Aksi Utama & Navigasi -->
                    <div class="flex items-center flex-wrap gap-2">
                        <!-- Daftar Transaksi (Hari Ini) -->
                        <x-pos.utility.button 
                            href="/pos/transactions?fromDate={{ now()->format('Y-m-d') }}&toDate={{ now()->format('Y-m-d') }}" 
                            variant="info" 
                            size="sm" 
                            icon="ph-bold ph-list-dashes" 
                            title="Daftar Transaksi"
                            class="!bg-indigo-500 hover:!bg-indigo-600 dark:!bg-indigo-600 dark:hover:!bg-indigo-700 !text-white !border-transparent"
                        >
                            Daftar Transaksi
                        </x-pos.utility.button>

                    <!-- Daftar Transaksi Tunda (Hold / List) -->
                    <x-pos.utility.button 
                        wire:click="openHeldTransactionsModal" 
                        variant="warning" 
                        size="sm" 
                        icon="ph-bold ph-folder-open" 
                        title="Daftar Transaksi Tunda"
                        class="!bg-amber-500 hover:!bg-amber-600 dark:!bg-amber-600 dark:hover:!bg-amber-700 !text-white !border-transparent"
                    >
                        Daftar Transaksi Tunda
                        @if (count($heldTransactions) > 0)
                            <span class="absolute -top-2 -right-2 flex h-5 w-5 pointer-events-none">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-5 w-5 bg-rose-600 items-center justify-center text-[10px] font-bold text-white shadow-sm border border-rose-700">
                                    {{ count($heldTransactions) }}
                                </span>
                            </span>
                        @endif
                    </x-pos.utility.button>

                    <!-- Simpan Transaksi Saat Ini -->
                    <x-pos.utility.button 
                        wire:click="holdTransaction" 
                        variant="warning" 
                        size="sm" 
                        icon="ph-bold ph-folder-simple-plus" 
                        title="Simpan Transaksi Sekarang" 
                        :disabled="empty($cart)"
                        class="!bg-amber-500 hover:!bg-amber-600 dark:!bg-amber-600 dark:hover:!bg-amber-700 !text-white !border-transparent"
                    >
                        Simpan Transaksi
                    </x-pos.utility.button>
                </div>

                <!-- Baris 2: Cetak Dokumen & Reset -->
                <div class="flex items-center flex-wrap gap-2">
                    <!-- Print Bill -->
                    <x-pos.utility.button 
                        wire:click="printDraft('bill')" 
                        variant="info" 
                        size="sm" 
                        icon="ph-bold ph-receipt" 
                        title="Cetak Bill Sementara" 
                        :disabled="empty($cart)"
                    >
                        Bill
                    </x-pos.utility.button>

                    <!-- Print Large Bill -->
                    <x-pos.utility.button 
                        wire:click="printDraft('large')" 
                        variant="info" 
                        size="sm" 
                        icon="ph-bold ph-file-text" 
                        title="Cetak Large Bill" 
                        :disabled="empty($cart)"
                    >
                        Large Bill
                    </x-pos.utility.button>

                    <!-- Print Penawaran -->
                    <x-pos.utility.button 
                        wire:click="printDraft('penawaran')" 
                        variant="info" 
                        size="sm" 
                        icon="ph-bold ph-handshake" 
                        title="Cetak Penawaran Harga" 
                        :disabled="empty($cart)"
                    >
                        Penawaran
                    </x-pos.utility.button>

                    <!-- Print Invoice -->
                    <x-pos.utility.button 
                        wire:click="printDraft('invoice')" 
                        variant="success" 
                        size="sm" 
                        icon="ph-bold ph-file-arrow-up" 
                        title="Cetak Invoice" 
                        :disabled="empty($cart)"
                    >
                        Invoice
                    </x-pos.utility.button>

                    <!-- Print Struk Terakhir -->
                    <x-pos.utility.button 
                        wire:click="reprintLastReceipt" 
                        variant="success" 
                        size="sm" 
                        icon="ph-bold ph-printer" 
                        title="Cetak Struk Terakhir" 
                        :disabled="!$lastSaleId"
                    >
                        Struk
                    </x-pos.utility.button>

                    <!-- Reset Transaksi -->
                    <x-pos.utility.button 
                        @click="$dispatch('confirm-open', { 
                            title: 'Reset Transaksi?', 
                            message: 'Ini akan mengosongkan seluruh keranjang belanja dan mengatur ulang data pelanggan.', 
                            onConfirm: 'livewire:clearCart', 
                            confirmLabel: 'Ya, Reset', 
                            isDanger: true 
                        })"
                        variant="danger" 
                        size="sm" 
                        icon="ph-bold ph-trash" 
                        title="Reset Transaksi"
                        class="!bg-red-600 hover:!bg-red-700 dark:!bg-red-700 dark:hover:!bg-red-800 !text-white !border-transparent"
                    >
                        Reset
                    </x-pos.utility.button>
                </div>
            </div>
        </div>
        </div>

        <!-- Informasi Pembelian Section -->
        <div class="mt-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-400 dark:border-slate-700">
            <h3 class="text-[10px] sm:text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-1.5">
                <i class="ph-bold ph-receipt text-xs sm:text-sm"></i>
                Informasi Pembelian
            </h3>

            <!-- 4 Column Grid for Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                <!-- Customer Selector -->
                <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Pelanggan</label>
                        <div class="flex items-center gap-1.5 cursor-pointer" 
                             @click="if ('{{ $selectedCustomerId }}') { $wire.clearCustomer(); } else { $refs.searchInput.focus(); isOpen = true; }">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Walk-in</span>
                            <button 
                                type="button"
                                class="relative inline-flex h-4 w-7 items-center rounded-full transition-colors {{ !$selectedCustomerId ? 'bg-primary dark:bg-blue-500' : 'bg-slate-300 dark:bg-slate-600' }}"
                            >
                                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ !$selectedCustomerId ? 'translate-x-3.5' : 'translate-x-0.5' }}"></span>
                            </button>
                        </div>
                    </div>
                    @if ($selectedCustomerId)
                        <div class="relative">
                            <i class="ph ph-user-plus text-slate-600 dark:text-slate-355 absolute left-2.5 top-1/2 -translate-y-1/2 text-sm font-bold"></i>
                            <div class="w-full pl-8 pr-7 py-1.5 bg-white dark:bg-slate-800 border border-slate-400 dark:border-slate-600 rounded-lg font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between shadow-sm">
                                <span class="truncate">{{ $selectedCustomerName }}</span>
                                <button wire:click="clearCustomer" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0 ml-1">
                                    <i class="ph-bold ph-x text-sm sm:text-base"></i>
                                </button>
                            </div>
                        </div>
                    @else
                        <x-pos.form.input 
                            id="customer-search-input"
                            model="customerSearch"
                            placeholder="Cari Pelanggan..."
                            icon="ph-user-plus"
                            live
                            size="sm"
                            @focus="isOpen = true"
                            @click="isOpen = true"
                            x-ref="searchInput"
                            class="!bg-white dark:!bg-slate-800"
                        />
                        
                        <!-- Customer Search Results Dropdown -->
                        <div x-show="isOpen" x-cloak class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 overflow-hidden flex flex-col">
                            <div class="max-h-60 overflow-y-auto no-scrollbar">
                            @if (count($customers) > 0)
                                @foreach ($customers as $c)
                                    <button wire:click="selectCustomer({{ $c->id }}, '{{ $c->name }}', {{ $c->is_loyalty_member ? 'true' : 'false' }})" @click="isOpen = false" class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-slate-100">{{ $c->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $c->phone }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            @else
                                <div class="px-4 py-3 text-sm text-slate-550 dark:text-slate-400">Tidak ada pelanggan ditemukan</div>
                            @endif
                            @if (count($customers) >= $customerLimit)
                                <div x-intersect="$wire.loadMoreCustomers()" class="py-3 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-800 animate-pulse">
                                    Memuat lebih banyak...
                                </div>
                            @endif
                            </div>
                            <button type="button" wire:click="openCreateCustomerModal" @click="isOpen = false" class="w-full px-4 py-3 text-left text-xs bg-slate-50 dark:bg-slate-900/60 text-primary dark:text-blue-400 font-bold hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors flex items-center gap-2 border-t border-slate-200 dark:border-slate-700 flex-shrink-0">
                                <i class="ph-bold ph-plus-circle text-sm"></i>
                                <span>{{ $customerSearch ? 'Daftarkan "' . $customerSearch . '" sebagai Pelanggan Baru' : 'Daftarkan Pelanggan Baru' }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Pricing Tier Dropdown Selector -->
                @if (count($pricingTiers) > 0)
                    <x-pos.form.select 
                        label="Tingkat Harga"
                        model="selectedPricingTierId"
                        icon="ph-tag"
                        live
                        size="sm"
                        class="!bg-white dark:!bg-slate-800"
                    >
                        @if ($selectedPricingTierId === 'custom')
                            <option value="custom" class="bg-white dark:bg-slate-800 text-slate-850 dark:text-slate-100">Kustom (Campuran)</option>
                        @endif
                        @foreach ($pricingTiers as $tier)
                            <option value="{{ $tier->id }}" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">{{ $tier->name }}</option>
                        @endforeach
                    </x-pos.form.select>
                @endif

                <!-- Kategori Penjualan Dropdown Selector -->
                <div>
                    <x-pos.form.select 
                        label="Kategori Penjualan"
                        model="saleCategory"
                        icon="ph-storefront"
                        live
                        size="sm"
                        class="!bg-white dark:!bg-slate-800"
                    >
                        @forelse ($saleCategories as $cat)
                            <option value="{{ $cat->name }}" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">{{ $cat->name }}</option>
                        @empty
                            <option value="Store" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">Store</option>
                            <option value="Online" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">Online</option>
                        @endforelse
                    </x-pos.form.select>
                    <div class="mt-1 flex items-center justify-between px-1">
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Preview Invoice:</span>
                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded">{{ $this->previewInvoiceNumber }}</span>
                    </div>
                </div>

                <!-- Sales Dropdown Selector (Searchable) -->
                <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">Sales</label>
                    @if ($selectedSalesRepId)
                        <div class="relative">
                            <i class="ph ph-identification-card text-slate-600 dark:text-slate-355 absolute left-2.5 top-1/2 -translate-y-1/2 text-sm font-bold"></i>
                            <div class="w-full pl-8 pr-7 py-1.5 bg-white dark:bg-slate-800 border border-slate-400 dark:border-slate-600 rounded-lg font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between shadow-sm">
                                <span class="truncate">{{ $selectedSalesRepName }}</span>
                                <button wire:click="clearSalesRep" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0 ml-1">
                                    <i class="ph-bold ph-x text-sm sm:text-base"></i>
                                </button>
                            </div>
                        </div>
                    @else
                        <x-pos.form.input 
                            id="sales-search-input"
                            model="salesSearch"
                            placeholder="Cari Sales..."
                            icon="ph-identification-card"
                            live
                            size="sm"
                            @focus="isOpen = true"
                            @click="isOpen = true"
                            class="!bg-white dark:!bg-slate-800"
                        />
                        
                        <!-- Sales Search Results Dropdown -->
                        <div x-show="isOpen" x-cloak class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 overflow-hidden max-h-60 overflow-y-auto no-scrollbar">
                            @if (count($salesReps) > 0)
                                @foreach ($salesReps as $salesRep)
                                    <button wire:click="selectSalesRep({{ $salesRep->id }}, '{{ $salesRep->name }}')" @click="isOpen = false" class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-slate-100">{{ $salesRep->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $salesRep->email }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            @else
                                <div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">Tidak ada data Sales</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Items Table (Scrollable) -->
    <div class="flex-1 overflow-y-auto no-scrollbar px-6">
        @if (empty($cart))
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 py-16">
                <i class="ph ph-shopping-cart text-5xl mb-3 opacity-50"></i>
                <span class="text-sm font-medium">Keranjang belanja kosong</span>
                <button type="button" wire:click="openProductSearch" class="mt-4 px-4 py-2 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl transition-colors cursor-pointer flex items-center gap-2">
                    <i class="ph-bold ph-plus"></i> Tambah Produk
                </button>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-900/95 dark:bg-slate-950/95 z-[1]">
                    <tr class="border-b border-slate-800 dark:border-slate-800">
                        <th class="text-left py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-8">No</th>
                        <th class="text-left py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider">Item</th>
                        <th class="text-left py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-40">Tingkat Harga</th>
                        <th class="text-left py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-36">Catatan</th>
                        <th class="text-right py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider !bg-slate-800 dark:!bg-slate-900/60 w-24">Harga</th>
                        <th class="text-center py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-24">Qty</th>
                        <th class="text-right py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-28">Diskon</th>
                        <th class="text-right py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-28">PPN</th>
                        <th class="text-right py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider !bg-emerald-900/40 dark:!bg-emerald-950/60 w-28">Subtotal</th>
                        <th class="text-center py-2 px-2 text-[10px] font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart as $id => $item)
                        @php 
                            $rowSubtotal = ($item['price'] * $item['qty']) - intval($item['discount_amount'] ?? 0); 
                            $itemVariant = $cartVariants[$id] ?? null;
                        @endphp
                        <tr wire:key="cart-item-row-{{ $id }}" class="border-b border-slate-250 dark:border-slate-700 hover:bg-slate-100/40 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-2 px-2 text-xs font-extrabold text-slate-700 dark:text-slate-300 align-middle">{{ $loop->iteration }}</td>
                            <td class="py-2 px-2 align-middle min-w-[150px]">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-base">{{ $item['emoji'] ?? '🎵' }}</span>
                                    <div class="flex-1">
                                        <div class="font-extrabold text-slate-900 dark:text-white text-[11px] sm:text-xs whitespace-normal leading-snug">{{ $item['name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- Dedicated Tingkat Harga Column -->
                            <td class="py-2 px-2 align-middle">
                                <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-7 w-full max-w-[200px]">
                                    <i class="ph ph-tag-chevron text-slate-500 dark:text-slate-400 text-[10px] absolute left-1.5 pointer-events-none"></i>
                                    <select 
                                        onchange="@this.call('updateItemPricingTier', {{ $id }}, this.value)"
                                        class="w-full pl-6 pr-5 py-0 h-full bg-transparent border-none text-[10px] sm:text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:ring-0 cursor-pointer appearance-none"
                                    >
                                        @foreach ($pricingTiers as $tier)
                                            <option value="{{ $tier->id }}" {{ ($item['pricing_tier_id'] ?? '') == $tier->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                                                {{ $tier->name }} ({{ \App\Helpers\FormatHelper::rupiah($itemVariant ? $itemVariant->priceForTier($tier->id) : 0) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="ph ph-caret-down text-slate-500 dark:text-slate-400 absolute right-1.5 pointer-events-none text-[8px]"></i>
                                </div>
                            </td>
                            <!-- Dedicated Catatan Column -->
                            <td class="py-2 px-2 align-middle">
                                <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-7 w-full max-w-[160px]">
                                    <i class="ph ph-note-pencil text-slate-500 dark:text-slate-400 text-[10px] absolute left-1.5 pointer-events-none"></i>
                                    <input 
                                        type="text" 
                                        placeholder="Catatan..." 
                                        value="{{ $item['notes'] ?? '' }}"
                                        onchange="@this.call('updateItemNote', {{ $id }}, this.value)"
                                        class="w-full pl-6 pr-2 py-0 h-full bg-transparent border-none text-[10px] sm:text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none focus:ring-0"
                                    >
                                </div>
                            </td>
                            <td class="py-2 px-2 text-[11px] sm:text-xs font-bold text-slate-900 dark:text-slate-100 text-right align-middle whitespace-nowrap bg-slate-50/50 dark:bg-slate-800/20">{{ \App\Helpers\FormatHelper::rupiah($item['price']) }}</td>
                            <td class="py-2 px-2 align-middle">
                                <div class="flex items-center justify-center gap-1 bg-slate-100 dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-md p-0.5 mx-auto w-fit">
                                    <button wire:click="updateQty({{ $id }}, -1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait" class="w-5 h-5 rounded bg-white dark:bg-slate-700 shadow border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer">
                                        <i class="ph-bold ph-minus text-[10px]"></i>
                                    </button>
                                    <span class="w-5 text-center text-xs font-extrabold text-slate-900 dark:text-white">{{ $item['qty'] }}</span>
                                    <button wire:click="updateQty({{ $id }}, 1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait" class="w-5 h-5 rounded bg-white dark:bg-slate-700 shadow border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer">
                                        <i class="ph-bold ph-plus text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-2 px-2 align-middle">
                                <div class="flex items-center justify-end gap-1">
                                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-7 w-20">
                                        <input type="number" placeholder="0" value="{{ ($item['discount_value'] ?? 0) > 0 ? $item['discount_value'] : '' }}" onchange="@this.call('updateItemDiscountValue', {{ $id }}, this.value)" class="w-full pl-1.5 pr-6 py-0 h-full bg-transparent border-none text-[10px] sm:text-xs font-bold text-slate-900 dark:text-slate-100 outline-none focus:ring-0" min="0">
                                        <button type="button" wire:click="toggleItemDiscountType({{ $id }})" class="absolute right-0 top-0 bottom-0 px-1.5 bg-slate-150 dark:bg-slate-800 text-[9px] font-black border-l border-slate-400 dark:border-slate-600 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center">{{ ($item['discount_type'] ?? 'percent') === 'percent' ? '%' : 'Rp' }}</button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-2 align-middle">
                                <div class="flex items-center justify-end gap-1">
                                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-7 w-20">
                                        <input type="number" placeholder="0" value="{{ ($item['tax_value'] ?? 0) > 0 ? $item['tax_value'] : '' }}" onchange="@this.call('updateItemTaxValue', {{ $id }}, this.value)" class="w-full pl-1.5 pr-6 py-0 h-full bg-transparent border-none text-[10px] sm:text-xs font-bold text-slate-900 dark:text-slate-100 outline-none focus:ring-0" min="0">
                                        <button type="button" wire:click="toggleItemTaxType({{ $id }})" class="absolute right-0 top-0 bottom-0 px-1.5 bg-slate-150 dark:bg-slate-800 text-[9px] font-black border-l border-slate-400 dark:border-slate-600 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center">{{ ($item['tax_type'] ?? 'percent') === 'percent' ? '%' : 'Rp' }}</button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-2 text-xs sm:text-sm font-black text-emerald-500 dark:text-emerald-300 text-right align-middle whitespace-nowrap bg-emerald-50/30 dark:bg-emerald-950/15">{{ \App\Helpers\FormatHelper::rupiah($rowSubtotal) }}</td>
                            <td class="py-2 px-2 text-center align-middle">
                                <button wire:click="updateQty({{ $id }}, -{{ $item['qty'] }})" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait" class="w-6 h-6 rounded-md bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white flex items-center justify-center transition-colors cursor-pointer" title="Hapus item">
                                    <i class="ph-bold ph-trash text-[10px]"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Cart Footer / Summary (Flex: Details Fill Remaining Space, Slim Fixed-Width Button) -->
    <div class="bg-slate-100 dark:bg-slate-900 p-3 border-t border-slate-200 dark:border-slate-700 mt-auto">
        <div class="flex gap-3 items-stretch">
            <!-- Left Column: Summary Details & Total Tagihan (Flex-1) -->
            <div class="flex-1 flex flex-col justify-between space-y-2 min-w-0">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-sm sm:text-base">
                        <span class="text-slate-700 dark:text-slate-200 font-extrabold truncate">Subtotal ({{ collect($cart)->sum('qty') }} item)</span>
                        <span class="font-black text-slate-950 dark:text-white text-sm sm:text-base whitespace-nowrap ml-1">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-sm sm:text-base gap-1">
                        <div class="flex items-center gap-2">
                            <label for="enableTax" class="inline-flex items-center cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" id="enableTax" wire:model.live="enableTax" class="sr-only peer">
                                    <div class="w-8 h-4.5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-3.5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                                </div>
                                <span class="ms-2 text-sm sm:text-base font-black text-slate-700 dark:text-slate-200">PPN</span>
                            </label>
                            @if ($enableTax)
                                <div class="flex items-center bg-white dark:bg-slate-700 rounded-lg px-2 py-0 border border-slate-400 dark:border-slate-600 h-6">
                                    <input type="number" wire:model.live.debounce.500ms="taxPercent" class="w-8 bg-transparent text-center border-none p-0 text-[10px] sm:text-xs font-black text-slate-900 dark:text-white focus:ring-0 appearance-none h-full [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="0" max="100">%
                                </div>
                            @endif
                        </div>
                        <span class="font-black text-slate-950 dark:text-white text-sm sm:text-base whitespace-nowrap">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>

                    @if ($selectedCustomerId && $customerPoints > 0)
                        <div class="flex items-center justify-between text-sm sm:text-base gap-2">
                            <div class="flex items-center gap-2">
                                <label for="usePoints" class="inline-flex items-center cursor-pointer select-none">
                                    <div class="relative">
                                        <input type="checkbox" id="usePoints" wire:model.live="usePoints" class="sr-only peer">
                                        <div class="w-8 h-4.5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-3.5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                                    </div>
                                    <span class="ms-2 text-sm sm:text-base font-black text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                        <i class="ph-fill ph-coins text-amber-500 text-base"></i>
                                        Poin ({{ $customerPoints }})
                                    </span>
                                </label>
                            </div>
                            <span class="font-black text-green-600 dark:text-green-400 text-sm sm:text-base whitespace-nowrap">
                                @if ($usePoints)
                                    - Rp {{ number_format($pointDiscountAmount, 0, ',', '.') }}
                                @else
                                    Rp 0
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-sm sm:text-base gap-2 pt-0.5">
                        <span class="text-sm sm:text-base font-black text-slate-700 dark:text-slate-200">
                            Diskon Transaksi
                        </span>
                        <div class="w-36 flex-shrink-0">
                            <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-7">
                                <i class="ph ph-tag text-slate-500 dark:text-slate-400 text-[10px] absolute left-2 pointer-events-none z-10"></i>
                                @if ($discountType === 'fixed')
                                    <x-money-input 
                                        wire:model.live.debounce.500ms="discountValue"
                                        class="w-full pl-6 pr-8 py-0 h-full bg-transparent border-none text-xs font-black text-slate-900 dark:text-slate-100 outline-none focus:ring-0"
                                        placeholder="0"
                                    />
                                @else
                                    <input 
                                        type="number" 
                                        placeholder="0 %" 
                                        wire:model.live.debounce.500ms="discountValue"
                                        class="w-full pl-6 pr-8 py-0 h-full bg-transparent border-none text-xs font-black text-slate-900 dark:text-slate-100 outline-none focus:ring-0"
                                        min="0"
                                    >
                                @endif
                                <button 
                                    type="button"
                                    wire:click="toggleGlobalDiscountType"
                                    class="absolute right-0 top-0 bottom-0 px-2 bg-slate-150 dark:bg-slate-800 text-[10px] font-black border-l border-slate-400 dark:border-slate-600 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center select-none z-10"
                                    title="Klik untuk mengubah jenis diskon (Nominal / Persentase)"
                                >
                                    {{ $discountType === 'percent' ? '%' : 'Rp' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Tagihan -->
                <div class="pt-2 border-t border-slate-300 dark:border-slate-700 flex flex-col items-end mt-1">
                    <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider leading-none">Total Tagihan</span>
                    <span class="text-2xl sm:text-3xl font-black text-primary dark:text-blue-400 leading-tight mt-0.5 text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Right Column: Payment Box Button -->
            <div class="w-32 sm:w-36 flex-shrink-0 flex">
                <button 
                    type="button"
                    wire:click="openPayment"
                    @if(empty($cart)) disabled @endif
                    class="w-full h-full min-h-[90px] bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-slate-250 dark:disabled:bg-slate-800 text-white disabled:text-slate-400 dark:disabled:text-slate-600 rounded-xl p-2 sm:p-3 flex flex-col items-center justify-center text-center transition-all duration-200 shadow-lg shadow-emerald-600/25 disabled:shadow-none cursor-pointer disabled:cursor-not-allowed group"
                >
                    <div class="w-10 h-10 rounded-lg bg-white/20 dark:bg-white/10 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="ph-bold ph-credit-card text-xl text-white"></i>
                    </div>
                    <span class="font-black text-sm leading-tight">Bayar</span>
                    <span class="text-[10px] text-white/95 mt-1 font-black bg-white/20 px-2 py-0.5 rounded-md">F9</span>
                </button>
            </div>
        </div>
    </div>
</div>
