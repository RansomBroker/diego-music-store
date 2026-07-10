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
    'lastSaleId' => null
])

<aside class="w-[450px] xl:w-[650px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col h-full shadow-xl shadow-slate-200/50 dark:shadow-none flex-shrink-0 transition-colors">
    <!-- Cart Header -->
    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 mb-3">
            <div class="flex flex-col">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Transaksi Saat Ini</h2>
                <span class="text-[11px] font-mono font-bold text-slate-400 dark:text-slate-500 mt-0.5">
                    {{ $this->previewInvoiceNumber }} (Draft)
                </span>
            </div>
            
            <!-- Toolbar Utility Buttons with Labels -->
            <div class="flex items-center flex-wrap gap-1">
                <!-- Reset Transaksi -->
                <button type="button" wire:click="clearCart" class="flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-400 bg-slate-50 hover:bg-red-50/50 dark:bg-slate-900/60 dark:hover:bg-red-950/20 border border-slate-200/40 dark:border-slate-700/50 rounded-lg transition-all cursor-pointer uppercase tracking-wider" title="Reset Transaksi">
                    <i class="ph-bold ph-trash text-xs"></i>
                    <span>Reset</span>
                </button>
                <!-- Transaksi Ditunda (Hold / List) -->
                <button type="button" wire:click="openHeldTransactionsModal" class="relative flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 bg-slate-50 hover:bg-amber-50/50 dark:bg-slate-900/60 dark:hover:bg-amber-950/20 border border-slate-200/40 dark:border-slate-700/50 rounded-lg transition-all cursor-pointer uppercase tracking-wider" title="Daftar Transaksi Ditunda">
                    <i class="ph-bold ph-folder-open text-xs"></i>
                    <span>Daftar</span>
                    @if (count($heldTransactions) > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-amber-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center">
                            {{ count($heldTransactions) }}
                        </span>
                    @endif
                </button>
                <!-- Tunda Transaksi Saat Ini -->
                <button type="button" wire:click="holdTransaction" class="flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 bg-slate-50 hover:bg-amber-50/50 dark:bg-slate-900/60 dark:hover:bg-amber-950/20 border border-slate-200/40 dark:border-slate-700/50 rounded-lg transition-all cursor-pointer uppercase tracking-wider" title="Tunda Transaksi Sekarang" @if(empty($cart)) disabled style="opacity: 0.4; cursor: not-allowed;" @endif>
                    <i class="ph-bold ph-folder-simple-plus text-xs"></i>
                    <span>Tunda</span>
                </button>
                <!-- Print Bill Sementara (Opsi 1) -->
                <button type="button" wire:click="printBill" class="flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 bg-slate-50 hover:bg-blue-50/50 dark:bg-slate-900/60 dark:hover:bg-blue-950/20 border border-slate-200/40 dark:border-slate-700/50 rounded-lg transition-all cursor-pointer uppercase tracking-wider" title="Cetak Preview Tagihan" @if(empty($cart)) disabled style="opacity: 0.4; cursor: not-allowed;" @endif>
                    <i class="ph-bold ph-file-text text-xs"></i>
                    <span>Preview</span>
                </button>
                <!-- Print Struk Terakhir (Opsi 2) -->
                <button type="button" wire:click="reprintLastReceipt" class="flex items-center gap-1.5 px-2 py-1 text-[10px] font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 bg-slate-50 hover:bg-emerald-50/50 dark:bg-slate-900/60 dark:hover:bg-emerald-950/20 border border-slate-200/40 dark:border-slate-700/50 rounded-lg transition-all cursor-pointer uppercase tracking-wider" title="Cetak Struk Terakhir" @if(!$lastSaleId) disabled style="opacity: 0.4; cursor: not-allowed;" @endif>
                    <i class="ph-bold ph-printer text-xs"></i>
                    <span>Struk</span>
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
            <span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-slate-700 dark:text-slate-300">Faktur Baru</span>
            <span>{{ now()->format('d M Y') }}</span>
        </div>

        <!-- Informasi Pembelian Section -->
        <div class="mt-5 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200/50 dark:border-slate-700/60">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3 flex items-center gap-1.5">
                <i class="ph-bold ph-receipt text-sm"></i>
                Informasi Pembelian
            </h3>

            <!-- 2 Column Grid for Inputs -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <!-- Customer Selector -->
                <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider block mb-1">Pelanggan</label>
                    @if ($selectedCustomerId)
                        <div class="flex items-center justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700 shadow-sm h-[50px]">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-primary/10 text-primary dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                    <i class="ph-fill ph-user text-base"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $selectedCustomerName }}</span>
                            </div>
                            <button wire:click="clearCustomer" class="text-slate-400 hover:text-red-500 transition-colors flex-shrink-0">
                                <i class="ph-bold ph-x text-lg"></i>
                            </button>
                        </div>
                    @else
                        <x-pos.form.input 
                            model="customerSearch"
                            placeholder="Cari Pelanggan..."
                            icon="ph-user-plus"
                            live
                            @focus="isOpen = true"
                            @click="isOpen = true"
                            class="!bg-white dark:!bg-slate-800"
                        />
                        
                        <!-- Customer Search Results Dropdown -->
                        <div x-show="isOpen" x-cloak class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 overflow-hidden max-h-60 overflow-y-auto no-scrollbar">
                            @if (!empty($customers))
                                @foreach ($customers as $c)
                                    <button wire:click="selectCustomer({{ $c->id }}, '{{ $c->name }}', {{ $c->is_loyalty_member ? 'true' : 'false' }})" @click="isOpen = false" class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-slate-100">{{ $c->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $c->phone }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            @else
                                <div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">Tidak ada pelanggan ditemukan</div>
                            @endif
                            <button type="button" wire:click="openCreateCustomerModal" @click="isOpen = false" class="w-full px-4 py-3 text-left text-xs bg-slate-50 dark:bg-slate-900/60 text-primary dark:text-blue-400 font-bold hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
                                <i class="ph-bold ph-plus-circle text-sm"></i>
                                <span>{{ $customerSearch ? 'Daftarkan "' . $customerSearch . '" sebagai Pelanggan Baru' : 'Daftarkan Pelanggan Baru' }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Pricing Tier Dropdown Selector -->
                @if (!empty($pricingTiers))
                    <x-pos.form.select 
                        label="Tingkat Harga"
                        model="selectedPricingTierId"
                        icon="ph-tag"
                        live
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
            </div>
        </div>
    </div>

    <!-- Cart Items List (Scrollable) -->
    <div class="flex-1 overflow-y-auto no-scrollbar p-6 flex flex-col gap-6">
        @if (empty($cart))
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 py-12">
                <i class="ph ph-shopping-cart text-5xl mb-3 opacity-50"></i>
                <span class="text-sm font-medium">Keranjang belanja kosong</span>
            </div>
        @else
            @foreach ($cart as $id => $item)
                <x-pos.utility.cart-item :id="$id" :item="$item" :pricingTiers="$pricingTiers" />
            @endforeach
        @endif
    </div>

    <!-- Cart Footer / Summary -->
    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 border-t border-slate-200 dark:border-slate-700 mt-auto">
        <div class="space-y-3 mb-5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 dark:text-slate-400">Subtotal ({{ collect($cart)->sum('qty') }} item)</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-sm gap-2">
                <div class="flex items-center gap-2">
                    <label for="enableTax" class="inline-flex items-center cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" id="enableTax" wire:model.live="enableTax" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-4 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                        </div>
                        <span class="ms-2.5 text-sm font-bold text-slate-500 dark:text-slate-400">Pajak PPN</span>
                    </label>
                    @if ($enableTax)
                        <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded px-1.5 py-0.5 border border-slate-200/60 dark:border-slate-600">
                            <input type="number" wire:model.live.debounce.250ms="taxPercent" class="w-8 bg-transparent text-center border-none p-0 text-xs font-bold text-slate-700 dark:text-slate-200 focus:ring-0 appearance-none h-4 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="0" max="100">%
                        </div>
                    @endif
                </div>
                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
            </div>
            @if ($selectedCustomerId && $customerPoints > 0)
                <div class="flex items-center justify-between text-sm gap-2">
                    <div class="flex items-center gap-2">
                        <label for="usePoints" class="inline-flex items-center cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" id="usePoints" wire:model.live="usePoints" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-4 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary transition-colors"></div>
                            </div>
                            <span class="ms-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="ph-fill ph-coins text-amber-500 text-base"></i>
                                Gunakan Poin ({{ $customerPoints }} Poin)
                            </span>
                        </label>
                    </div>
                    <span class="font-semibold text-green-600 dark:text-green-400">
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
                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400">
                        Diskon Transaksi
                    </span>
                    @if ($discountType === 'percent' && $discountValue > 0)
                        <span class="text-xs font-bold text-green-600 dark:text-green-400 mt-0.5">
                            - Rp {{ number_format($discountAmount, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                <div class="w-32 flex-shrink-0">
                    <div class="relative flex items-center bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/50 dark:border-slate-700/60 overflow-hidden h-8">
                        <i class="ph ph-tag text-slate-400 dark:text-slate-500 text-xs absolute left-2 pointer-events-none"></i>
                        <input 
                            type="number" 
                            placeholder="{{ $discountType === 'percent' ? '0 %' : 'Rp 0' }}" 
                            wire:model.live="discountValue"
                            class="w-full pl-7 pr-8 py-0 h-full bg-transparent border-none text-[11px] font-bold text-slate-750 dark:text-slate-300 outline-none focus:ring-0"
                            min="0"
                        >
                        <button 
                            type="button"
                            wire:click="toggleGlobalDiscountType"
                            class="absolute right-0 top-0 bottom-0 px-2 bg-slate-100 dark:bg-slate-800 text-[10px] font-black border-l border-slate-200/50 dark:border-slate-700 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center select-none"
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
            <span class="text-base font-medium text-slate-800 dark:text-slate-200">Total Tagihan</span>
            <span class="text-2xl font-bold text-primary dark:text-blue-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>

        <button wire:click="openPayment" class="w-full bg-primary hover:bg-primaryHover text-white py-3 rounded-xl font-bold text-base shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center gap-2 group {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>
            <i class="ph-bold ph-credit-card text-lg group-hover:scale-110 transition-transform"></i>
            Proses Pembayaran
        </button>
    </div>
</aside>
