<div 
    x-data="{ showShortcuts: false }"
    @keydown.window.f1.prevent="$wire.openProductSearch()"
    @keydown.window.alt.p.prevent="$wire.openProductSearch()"
    @keydown.window.f2.prevent="document.getElementById('customer-search-input')?.focus(); document.getElementById('customer-search-input')?.select()"
    @keydown.window.alt.c.prevent="document.getElementById('customer-search-input')?.focus(); document.getElementById('customer-search-input')?.select()"
    @keydown.window.f3.prevent="document.getElementById('sales-search-input')?.focus(); document.getElementById('sales-search-input')?.select()"
    @keydown.window.alt.s.prevent="document.getElementById('sales-search-input')?.focus(); document.getElementById('sales-search-input')?.select()"
    @keydown.window.f4.prevent="$wire.set('showHeldModal', true)"
    @keydown.window.alt.l.prevent="$wire.set('showHeldModal', true)"
    @keydown.window.f7.prevent="$wire.holdTransaction()"
    @keydown.window.alt.h.prevent="$wire.holdTransaction()"
    @keydown.window.f8.prevent="$wire.reprintLastReceipt()"
    @keydown.window.alt.t.prevent="$wire.reprintLastReceipt()"
    @keydown.window.f9.prevent="$wire.openPayment()"
    @keydown.window.alt.b.prevent="$wire.openPayment()"
    @keydown.window.alt.r.prevent="$dispatch('confirm-open', { 
        title: 'Reset Transaksi?', 
        message: 'Ini akan mengosongkan seluruh keranjang belanja dan mengatur ulang data pelanggan.', 
        onConfirm: 'livewire:clearCart', 
        confirmLabel: 'Ya, Reset', 
        isDanger: true 
    })"
    @keydown.window.f12.prevent="showShortcuts = !showShortcuts"
    @keydown.window.escape="showShortcuts = false"
    class="flex flex-col h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200 relative"
>
    <!-- Header -->
    <x-pos-page::header :branches="$branches" :selectedBranchId="$selectedBranchId" :selectedStoreName="$selectedStoreName" :selectedBranchName="$selectedBranchName" :activeSessionInfo="$activeSessionInfo" :todaySalesTotal="$this->todaySalesTotal" />

    <!-- Full-Screen Transaction Area -->
    <x-pos-page::cart-sidebar 
        :cart="$cart"
        :customerSearch="$customerSearch"
        :customers="$this->customers"
        :selectedCustomerId="$selectedCustomerId"
        :selectedCustomerName="$selectedCustomerName"
        :isLoyaltyMember="$isLoyaltyMember"
        :subtotal="$this->subtotal"
        :discountAmount="$this->discountAmount"
        :discountValue="$discountValue"
        :discountType="$discountType"
        :taxAmount="$this->taxAmount"
        :grandTotal="$this->grandTotal"
        :pricingTiers="$pricingTiers"
        :selectedPricingTierId="$selectedPricingTierId"
        :enableTax="$enableTax"
        :taxPercent="$taxPercent"
        :usePoints="$usePoints"
        :customerPoints="$customerPoints"
        :pointDiscountAmount="$this->pointDiscountAmount"
        :heldTransactions="$this->heldTransactions"
        :lastSaleId="$lastSaleId"
        :salesSearch="$salesSearch"
        :selectedSalesRepId="$selectedSalesRepId"
        :selectedSalesRepName="$selectedSalesRepName"
        :saleCategory="$saleCategory"
        :salesReps="$this->salesReps"
        :saleCategories="$this->saleCategories"
        :editingSaleId="$editingSaleId"
    />

    <!-- Product Search Modal -->
    <x-pos-page::product-search-modal 
        :show="$showProductSearchModal"
        :products="$this->products"
        :activeCategory="$activeCategory"
        :search="$search"
        :selectedBranchId="$selectedBranchId"
        :selectedPricingTierId="$selectedPricingTierId"
        :cart="$cart"
        :categoryCounts="$this->categoryCounts"
    />

    <!-- Payment Detail Modal -->
    <x-pos-page::payment-modal 
        :showPaymentModal="$showPaymentModal"
        :paymentMethod="$paymentMethod"
        :grandTotal="$this->grandTotal"
        :amountPaid="$amountPaid"
        :subtotal="$this->subtotal"
        :discountAmount="$this->discountAmount"
        :discountType="$discountType"
        :discountValue="$discountValue"
        :taxAmount="$this->taxAmount"
        :pointDiscountAmount="$this->pointDiscountAmount"
        :usePoints="$usePoints"
        :selectedPaymentMethods="$selectedPaymentMethods"
        :amountCash="$amountCash"
        :amountDebit="$amountDebit"
        :amountCredit="$amountCredit"
        :debitRef="$debitRef"
        :paymentAmounts="$paymentAmounts"
        :paymentRefs="$paymentRefs"
        :paymentMethods="$this->paymentMethods"
    />

    <!-- Create Customer Modal -->
    <x-pos-page::create-customer-modal 
        :showCreateCustomerModal="$showCreateCustomerModal"
        :newCustomerName="$newCustomerName"
        :newCustomerPhone="$newCustomerPhone"
        :newCustomerEmail="$newCustomerEmail"
        :newCustomerAddress="$newCustomerAddress"
        :newCustomerPricingTierId="$newCustomerPricingTierId"
        :newCustomerIsLoyaltyMember="$newCustomerIsLoyaltyMember"
        :pricingTiers="$pricingTiers"
    />

    <!-- Held Transactions List Modal -->
    <x-pos-page::modal 
        :show="$showHeldModal" 
        title="Daftar Transaksi Ditunda" 
        closeAction="$set('showHeldModal', false)"
        maxWidth="md"
    >
        <div class="space-y-3">
            @if ($this->heldTransactions->isEmpty())
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-6">Tidak ada transaksi yang ditunda.</p>
            @else
                @foreach ($this->heldTransactions as $held)
                    <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/50 dark:border-slate-700/60">
                        <div>
                            <div class="font-bold text-sm text-slate-800 dark:text-slate-100">
                                {{ $held->customer_name }}
                            </div>
                            <div class="text-[10px] font-mono font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mt-0.5">
                                HLD-{{ strtoupper(substr($held->id, 0, 8)) }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ count($held->cart_data) }} item • Jam {{ $held->created_at->format('H:i') }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="restoreHeldTransaction('{{ $held->id }}')" class="px-3 py-1.5 bg-primary hover:bg-primary-light text-white font-semibold text-xs rounded-lg transition-colors cursor-pointer">
                                Muat
                            </button>
                            <button type="button" wire:click="deleteHeldTransaction('{{ $held->id }}')" class="p-1.5 text-slate-400 hover:text-red-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" title="Hapus">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </x-pos-page::modal>

    <!-- Keyboard Shortcuts FAB and Popover -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Cheat Sheet Card -->
        <div 
            x-show="showShortcuts"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="mb-3 w-80 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden"
            x-cloak
            @click.away="showShortcuts = false"
        >
            <!-- Card Header -->
            <div class="p-3.5 border-b border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-keyboard text-base text-primary dark:text-blue-400"></i>
                    <span class="font-black text-xs text-slate-850 dark:text-slate-200 uppercase tracking-wider">Keyboard Shortcuts</span>
                </div>
                <button @click="showShortcuts = false" class="text-slate-400 hover:text-red-500 transition-colors">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
            
            <!-- Card Body -->
            <div class="p-3.5 space-y-2.5 max-h-[350px] overflow-y-auto no-scrollbar">
                <!-- Tambah Produk -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Cari/Tambah Produk</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F1</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">P</kbd>
                    </div>
                </div>
                <!-- Cari Pelanggan -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Cari Pelanggan</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F2</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">C</kbd>
                    </div>
                </div>
                <!-- Cari Sales -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Cari Sales</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F3</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">S</kbd>
                    </div>
                </div>
                <!-- Daftar Hold -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Daftar Hold (Recall)</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F4</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">L</kbd>
                    </div>
                </div>
                <!-- Tunda Transaksi -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Tunda Transaksi (Hold)</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F7</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">H</kbd>
                    </div>
                </div>
                <!-- Cetak Ulang Struk -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Cetak Struk Terakhir</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F8</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">T</kbd>
                    </div>
                </div>
                <!-- Bayar / Checkout -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Proses Bayar (Checkout)</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F9</kbd>
                        <span class="text-slate-400 text-[10px]">atau</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">B</kbd>
                    </div>
                </div>
                <!-- Reset Transaksi -->
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800/40 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Reset Transaksi</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">Alt</kbd>
                        <span class="text-slate-400 text-[10px]">+</span>
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">R</kbd>
                    </div>
                </div>
                <!-- F12 Bantuan -->
                <div class="flex items-center justify-between py-1.5 last:border-0 last:pb-0">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Panduan Bantuan</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-[10px] font-black text-slate-800 dark:text-slate-200">F12</kbd>
                    </div>
                </div>
            </div>
            
            <!-- Card Footer -->
            <div class="px-3.5 py-2.5 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200/50 dark:border-slate-800/50 text-[10px] text-center text-slate-500 dark:text-slate-400 font-medium">
                Diego Music Store ERP
            </div>
        </div>

        <!-- Floating Action Button (FAB) -->
        <button 
            @click="showShortcuts = !showShortcuts" 
            class="w-12 h-12 rounded-full bg-gradient-to-tr from-primary to-blue-500 hover:from-primaryHover hover:to-blue-600 text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer relative group"
            title="Keyboard Shortcuts (F12)"
        >
            <!-- Keyboard Icon -->
            <i class="ph-bold ph-keyboard text-xl" x-show="!showShortcuts"></i>
            <!-- Close Icon -->
            <i class="ph-bold ph-x text-xl" x-show="showShortcuts" x-cloak></i>
            
            <!-- Hover Tooltip -->
            <div class="absolute bottom-full right-0 mb-3 whitespace-nowrap bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-black px-2.5 py-1 rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity duration-200 shadow-md">
                Panduan Shortcut (F12)
            </div>
        </button>
    </div>

    <!-- Reusable Global Confirmation Modal -->
    <x-pos.utility.confirm-modal />
</div>
