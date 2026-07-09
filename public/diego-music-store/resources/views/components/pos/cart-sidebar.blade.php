@props([
    'cart',
    'customerSearch',
    'customers',
    'selectedCustomerId',
    'selectedCustomerName',
    'isLoyaltyMember',
    'subtotal',
    'discountAmount',
    'taxAmount',
    'grandTotal',
    'pricingTiers' => [],
    'selectedPricingTierId' => null,
    'enableTax' => true,
    'taxPercent' => 11,
    'usePoints' => false,
    'customerPoints' => 0,
    'pointDiscountAmount' => 0
])

<aside class="w-96 xl:w-[600px] bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col h-full shadow-xl shadow-slate-200/50 dark:shadow-none flex-shrink-0 transition-colors">
    <!-- Cart Header -->
    <div class="p-6 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">Transaksi Saat Ini</h2>
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
                <div class="relative">
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
                        <div class="flex items-center gap-3 p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700 shadow-sm h-[50px]">
                            <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-user-plus text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="text" wire:model.live.debounce.250ms="customerSearch" placeholder="Cari Pelanggan..." class="w-full bg-transparent border-none outline-none text-sm font-semibold text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 p-0 focus:ring-0">
                            </div>
                            <button type="button" wire:click="openCreateCustomerModal" class="p-1.5 text-primary dark:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-lg transition-colors flex items-center justify-center cursor-pointer flex-shrink-0" title="Tambah Pelanggan Baru">
                                <i class="ph-bold ph-plus-circle text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Customer Search Results Dropdown -->
                        @if ($customerSearch !== '')
                            <div class="absolute left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg z-20 overflow-hidden max-h-60 overflow-y-auto no-scrollbar">
                                @if (!empty($customers))
                                    @foreach ($customers as $c)
                                        <button wire:click="selectCustomer({{ $c->id }}, '{{ $c->name }}', {{ $c->is_loyalty_member ? 'true' : 'false' }})" class="w-full px-4 py-3 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0 flex items-center justify-between">
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ $c->name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $c->phone }}</div>
                                            </div>
                                        </button>
                                    @endforeach
                                @endif
                                <button type="button" wire:click="openCreateCustomerModal" class="w-full px-4 py-3 text-left text-xs bg-slate-50 dark:bg-slate-900/60 text-primary dark:text-blue-400 font-bold hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
                                    <i class="ph-bold ph-plus-circle text-sm"></i>
                                    <span>Daftarkan "{{ $customerSearch }}" sebagai Pelanggan Baru</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Pricing Tier Dropdown Selector -->
                @if (!empty($pricingTiers))
                    <div class="relative">
                        <label class="text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider block mb-1">Tingkat Harga</label>
                        <div class="relative flex items-center bg-white dark:bg-slate-800 rounded-xl border border-slate-200/50 dark:border-slate-700 shadow-sm overflow-hidden h-[50px]">
                            <i class="ph-bold ph-tag text-slate-400 dark:text-slate-500 text-sm absolute left-3.5 pointer-events-none"></i>
                            <select wire:model.live="selectedPricingTierId" class="w-full pl-10 pr-10 py-0 h-full bg-transparent border-none text-sm font-bold text-slate-700 dark:text-slate-300 outline-none cursor-pointer appearance-none focus:ring-0">
                                @if ($selectedPricingTierId === 'custom')
                                    <option value="custom" class="bg-white dark:bg-slate-800 text-slate-850 dark:text-slate-100">Kustom (Campuran)</option>
                                @endif
                                @foreach ($pricingTiers as $tier)
                                    <option value="{{ $tier->id }}" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                            <i class="ph-bold ph-caret-down text-slate-400 absolute right-3.5 pointer-events-none text-[11px]"></i>
                        </div>
                    </div>
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
            @if ($discountAmount > 0)
                <div class="flex items-center justify-between text-sm text-green-600 dark:text-green-400 font-medium">
                    <span class="flex items-center gap-1"><i class="ph-fill ph-tag"></i> Diskon Member</span>
                    <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Dotted Divider -->
        <div class="w-full border-t-2 border-dashed border-slate-200 dark:border-slate-700 my-4"></div>

        <div class="flex items-center justify-between mb-6">
            <span class="text-base font-medium text-slate-800 dark:text-slate-200">Total Tagihan</span>
            <span class="text-2xl font-bold text-primary dark:text-blue-400">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>

        <button wire:click="openPayment" class="w-full bg-primary hover:bg-primaryHover text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-blue-500/30 transition-all flex items-center justify-center gap-2 group {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>
            <i class="ph-bold ph-credit-card text-xl group-hover:scale-110 transition-transform"></i>
            Proses Pembayaran
        </button>
    </div>
</aside>
