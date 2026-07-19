@props([
    'cart',
    'customerSearch',
    'customers',
    'selectedCustomerId',
    'selectedCustomerName',
    'isLoyaltyMember',
    'subtotal',
    'discountAmount',
    'discountValue' => 0,
    'discountType' => 'fixed',
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
    'editingSaleId' => null
])

<div class="flex-1 w-full bg-white dark:bg-slate-800 flex flex-col h-full transition-colors overflow-hidden">
    <!-- Cart Header -->
    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
        @if ($editingSaleId)
            <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/60 rounded-xl flex items-center justify-between gap-3 transition-colors duration-200">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-450">
                    <i class="ph-fill ph-warning-circle text-lg animate-pulse"></i>
                    <div class="text-xs">
                        <span class="font-extrabold block">Mode Edit Transaksi</span>
                        <span class="font-medium text-slate-500 dark:text-slate-400 block mt-0.5">Semua perubahan stok & jurnal akan diperbarui setelah checkout.</span>
                    </div>
                </div>
                <button 
                    type="button"
                    wire:click="cancelEdit"
                    class="px-2.5 py-1.5 text-[10px] font-black text-amber-700 hover:text-white bg-amber-100 hover:bg-amber-600 dark:bg-amber-900/40 dark:hover:bg-amber-600 rounded-lg transition-all"
                >
                    Batal Edit
                </button>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-3">
            <div class="flex flex-col">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Transaksi Saat Ini</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100">
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
            <div class="flex items-center flex-wrap gap-2">
                <!-- Tambah Produk -->
                <x-pos.utility.button 
                    wire:click="openProductSearch" 
                    variant="primary" 
                    size="sm" 
                    icon="ph-bold ph-plus" 
                    title="Tambah Produk"
                >
                    Tambah Produk
                </x-pos.utility.button>

                <!-- Daftar Transaksi (Hold / List) -->
                <x-pos.utility.button 
                    wire:click="openHeldTransactionsModal" 
                    variant="warning" 
                    size="sm" 
                    icon="ph-bold ph-folder-open" 
                    title="Daftar Transaksi"
                    class="!bg-amber-500 hover:!bg-amber-600 dark:!bg-amber-600 dark:hover:!bg-amber-700 !text-white !border-transparent"
                >
                    Daftar Transaksi
                    @if (count($heldTransactions) > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-rose-600 text-white text-[8px] font-bold rounded-full flex items-center justify-center pointer-events-none">
                            {{ count($heldTransactions) }}
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

                <!-- Print Tagihan -->
                <x-pos.utility.button 
                    wire:click="printDraft('tagihan')" 
                    variant="info" 
                    size="sm" 
                    icon="ph-bold ph-file-arrow-up" 
                    title="Cetak Tagihan / Draft Invoice" 
                    :disabled="empty($cart)"
                >
                    Tagihan
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
                    class="ms-auto !bg-red-600 hover:!bg-red-700 dark:!bg-red-700 dark:hover:!bg-red-800 !text-white !border-transparent"
                >
                    Reset
                </x-pos.utility.button>
            </div>
        </div>

        <!-- Informasi Pembelian Section -->
        <div class="mt-5 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-400 dark:border-slate-700">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-1.5">
                <i class="ph-bold ph-receipt text-sm"></i>
                Informasi Pembelian
            </h3>

            <!-- 4 Column Grid for Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Customer Selector -->
                <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">Pelanggan</label>
                    @if ($selectedCustomerId)
                        <div class="relative">
                            <i class="ph ph-user-plus text-slate-600 dark:text-slate-355 absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold"></i>
                            <div class="w-full pl-9 pr-8 py-2 bg-white dark:bg-slate-800 border border-slate-400 dark:border-slate-600 rounded-lg font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between shadow-sm">
                                <span class="truncate">{{ $selectedCustomerName }}</span>
                                <button wire:click="clearCustomer" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0 ml-2">
                                    <i class="ph-bold ph-x text-base"></i>
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
                            class="!bg-white dark:!bg-slate-800"
                        />
                        
                        <!-- Customer Search Results Dropdown -->
                        <div x-show="isOpen" x-cloak class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 overflow-hidden max-h-60 overflow-y-auto no-scrollbar">
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
                            <button type="button" wire:click="openCreateCustomerModal" @click="isOpen = false" class="w-full px-4 py-3 text-left text-xs bg-slate-50 dark:bg-slate-900/60 text-primary dark:text-blue-400 font-bold hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
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

                <!-- Sales Dropdown Selector (Searchable) -->
                <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">Sales</label>
                    @if ($selectedSalesRepId)
                        <div class="relative">
                            <i class="ph ph-identification-card text-slate-600 dark:text-slate-355 absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold"></i>
                            <div class="w-full pl-9 pr-8 py-2 bg-white dark:bg-slate-800 border border-slate-400 dark:border-slate-600 rounded-lg font-bold text-xs text-slate-900 dark:text-white flex items-center justify-between shadow-sm">
                                <span class="truncate">{{ $selectedSalesRepName }}</span>
                                <button wire:click="clearSalesRep" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0 ml-2">
                                    <i class="ph-bold ph-x text-base"></i>
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
                <thead class="sticky top-0 bg-slate-900 dark:bg-slate-950/95 backdrop-blur-sm z-[1]">
                    <tr class="border-b border-slate-800 dark:border-slate-800">
                        <th class="text-left py-3 px-3 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-8">No</th>
                        <th class="text-left py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider">Item</th>
                        <th class="text-left py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-48">Tingkat Harga</th>
                        <th class="text-left py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-44">Catatan</th>
                        <th class="text-right py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider !bg-slate-800 dark:!bg-slate-900/60">Harga</th>
                        <th class="text-center py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-28">Qty</th>
                        <th class="text-right py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-36">Diskon</th>
                        <th class="text-right py-3 px-2 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider !bg-emerald-900/40 dark:!bg-emerald-950/60">Subtotal</th>
                        <th class="text-center py-3 px-3 text-xs font-black text-slate-200 dark:text-slate-300 uppercase tracking-wider w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart as $id => $item)
                        @php 
                            $rowSubtotal = ($item['price'] * $item['qty']) - intval($item['discount_amount'] ?? 0); 
                            $itemVariant = \App\Models\ProductVariant::find($id);
                        @endphp
                        <tr class="border-b border-slate-250 dark:border-slate-700 hover:bg-slate-100/40 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-2 text-sm font-extrabold text-slate-700 dark:text-slate-300 align-middle">{{ $loop->iteration }}</td>
                            <td class="py-3.5 px-2 align-middle min-w-[180px]">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $item['emoji'] }}</span>
                                    <div class="flex-1">
                                        <div class="font-extrabold text-slate-900 dark:text-white text-sm whitespace-normal leading-snug">{{ $item['name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- Dedicated Tingkat Harga Column -->
                            <td class="py-3.5 px-2 align-middle">
                                <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-8 w-full max-w-[220px]">
                                    <i class="ph ph-tag-chevron text-slate-500 dark:text-slate-400 text-xs absolute left-2 pointer-events-none"></i>
                                    <select 
                                        onchange="@this.call('updateItemPricingTier', {{ $id }}, this.value)"
                                        class="w-full pl-7 pr-6 py-0 h-full bg-transparent border-none text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:ring-0 cursor-pointer appearance-none"
                                    >
                                        @foreach ($pricingTiers as $tier)
                                            <option value="{{ $tier->id }}" {{ ($item['pricing_tier_id'] ?? '') == $tier->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                                                {{ $tier->name }} ({{ \App\Helpers\FormatHelper::rupiah($itemVariant ? $itemVariant->priceForTier($tier->id) : 0) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="ph ph-caret-down text-slate-500 dark:text-slate-400 absolute right-2 pointer-events-none text-[10px]"></i>
                                </div>
                            </td>
                            <!-- Dedicated Catatan Column -->
                            <td class="py-3.5 px-2 align-middle">
                                <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-8 w-full max-w-[200px]">
                                    <i class="ph ph-note-pencil text-slate-500 dark:text-slate-400 text-xs absolute left-2 pointer-events-none"></i>
                                    <input 
                                        type="text" 
                                        placeholder="Catatan..." 
                                        value="{{ $item['notes'] ?? '' }}"
                                        onchange="@this.call('updateItemNote', {{ $id }}, this.value)"
                                        class="w-full pl-7 pr-2 py-0 h-full bg-transparent border-none text-xs font-semibold text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none focus:ring-0"
                                    >
                                </div>
                            </td>
                            <td class="py-3.5 px-2 text-sm font-bold text-slate-900 dark:text-slate-100 text-right align-middle whitespace-nowrap bg-slate-50/50 dark:bg-slate-800/20">{{ \App\Helpers\FormatHelper::rupiah($item['price']) }}</td>
                            <td class="py-3.5 px-2 align-middle">
                                <div class="flex items-center justify-center gap-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-lg p-0.5 mx-auto w-fit">
                                    <button wire:click="updateQty({{ $id }}, -1)" class="w-6 h-6 rounded bg-white dark:bg-slate-700 shadow border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer">
                                        <i class="ph-bold ph-minus text-xs"></i>
                                    </button>
                                    <span class="w-6 text-center text-sm font-extrabold text-slate-900 dark:text-white">{{ $item['qty'] }}</span>
                                    <button wire:click="updateQty({{ $id }}, 1)" class="w-6 h-6 rounded bg-white dark:bg-slate-700 shadow border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors cursor-pointer">
                                        <i class="ph-bold ph-plus text-xs"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-2 align-middle">
                                <div class="flex items-center justify-end gap-1">
                                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-8 w-24">
                                        <input type="number" placeholder="0" value="{{ ($item['discount_value'] ?? 0) > 0 ? $item['discount_value'] : '' }}" onchange="@this.call('updateItemDiscountValue', {{ $id }}, this.value)" class="w-full pl-2 pr-7 py-0 h-full bg-transparent border-none text-xs font-bold text-slate-900 dark:text-slate-100 outline-none focus:ring-0" min="0">
                                        <button type="button" wire:click="toggleItemDiscountType({{ $id }})" class="absolute right-0 top-0 bottom-0 px-2 bg-slate-150 dark:bg-slate-800 text-[10px] font-black border-l border-slate-400 dark:border-slate-600 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center">{{ ($item['discount_type'] ?? 'fixed') === 'percent' ? '%' : 'Rp' }}</button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-2 text-base font-black text-emerald-500 dark:text-emerald-300 text-right align-middle whitespace-nowrap bg-emerald-50/30 dark:bg-emerald-950/15">{{ \App\Helpers\FormatHelper::rupiah($rowSubtotal) }}</td>
                            <td class="py-3.5 px-2 text-center align-middle">
                                <button wire:click="updateQty({{ $id }}, -{{ $item['qty'] }})" class="w-7 h-7 rounded-lg bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white flex items-center justify-center transition-colors cursor-pointer" title="Hapus item">
                                    <i class="ph-bold ph-trash text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Cart Footer / Summary -->
    <div class="bg-slate-100 dark:bg-slate-900 p-6 border-t border-slate-200 dark:border-slate-700 mt-auto">
        <div class="space-y-3 mb-5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-700 dark:text-slate-300 font-bold">Subtotal ({{ collect($cart)->sum('qty') }} item)</span>
                <span class="font-black text-slate-950 dark:text-white text-base">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-sm gap-2">
                <div class="flex items-center gap-2">
                    <label for="enableTax" class="inline-flex items-center cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" id="enableTax" wire:model.live="enableTax" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-4 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                        </div>
                        <span class="ms-2.5 text-sm font-extrabold text-slate-700 dark:text-slate-300">Pajak PPN</span>
                    </label>
                    @if ($enableTax)
                        <div class="flex items-center bg-white dark:bg-slate-700 rounded px-2 py-0.5 border border-slate-400 dark:border-slate-600">
                            <input type="number" wire:model.live.debounce.250ms="taxPercent" class="w-8 bg-transparent text-center border-none p-0 text-xs font-black text-slate-900 dark:text-white focus:ring-0 appearance-none h-4 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="0" max="100">%
                        </div>
                    @endif
                </div>
                <span class="font-black text-slate-950 dark:text-white text-base">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
            </div>
            @if ($selectedCustomerId && $customerPoints > 0)
                <div class="flex items-center justify-between text-sm gap-2">
                    <div class="flex items-center gap-2">
                        <label for="usePoints" class="inline-flex items-center cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" id="usePoints" wire:model.live="usePoints" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-4 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                            </div>
                            <span class="ms-2.5 text-sm font-extrabold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="ph-fill ph-coins text-amber-500 text-base"></i>
                                Gunakan Poin ({{ $customerPoints }} Poin)
                            </span>
                        </label>
                    </div>
                    <span class="font-black text-green-600 dark:text-green-400 text-base">
                        @if ($usePoints)
                            - Rp {{ number_format($pointDiscountAmount, 0, ',', '.') }}
                        @else
                            Rp 0
                        @endif
                    </span>
                </div>
            @endif
            <!-- Diskon Global Input -->
            <div class="flex items-center justify-between text-sm gap-2 pt-1">
                <div class="flex flex-col">
                    <span class="text-sm font-extrabold text-slate-700 dark:text-slate-300">
                        Diskon Transaksi
                    </span>
                    @if ($discountType === 'percent' && $discountValue > 0)
                        <span class="text-sm font-black text-green-600 dark:text-green-400 mt-0.5">
                            - Rp {{ number_format($discountAmount, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                <div class="w-32 flex-shrink-0">
                    <div class="relative flex items-center bg-white dark:bg-slate-900 rounded-lg border border-slate-400 dark:border-slate-600 overflow-hidden h-8">
                        <i class="ph ph-tag text-slate-500 dark:text-slate-400 text-xs absolute left-2 pointer-events-none"></i>
                        <input 
                            type="number" 
                            placeholder="{{ $discountType === 'percent' ? '0 %' : 'Rp 0' }}" 
                            wire:model.live="discountValue"
                            class="w-full pl-7 pr-8 py-0 h-full bg-transparent border-none text-sm font-extrabold text-slate-900 dark:text-slate-100 outline-none focus:ring-0"
                            min="0"
                        >
                        <button 
                            type="button"
                            wire:click="toggleGlobalDiscountType"
                            class="absolute right-0 top-0 bottom-0 px-2 bg-slate-150 dark:bg-slate-800 text-[10px] font-black border-l border-slate-400 dark:border-slate-600 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center select-none"
                            title="Klik untuk mengubah jenis diskon (Nominal / Persentase)"
                        >
                            {{ $discountType === 'percent' ? '%' : 'Rp' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dotted Divider -->
        <div class="w-full border-t-2 border-dashed border-slate-200 dark:border-slate-700 my-4"></div>

        <div class="flex items-center justify-between mb-6">
            <span class="text-base font-bold text-slate-900 dark:text-slate-100">Total Tagihan</span>
            <span class="text-3xl font-black text-primary dark:text-blue-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>

        <x-pos.utility.button 
            wire:click="openPayment" 
            variant="primary" 
            size="lg" 
            icon="ph-bold ph-credit-card"
            :disabled="empty($cart)"
            class="shadow-blue-500/30 group"
        >
            Proses Pembayaran
        </x-pos.utility.button>
    </div>
</div>
