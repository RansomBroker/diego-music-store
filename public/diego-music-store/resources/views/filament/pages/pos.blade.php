<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos.sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <x-pos.header :branches="$branches" :selectedBranchId="$selectedBranchId" :selectedStoreName="$selectedStoreName" :selectedBranchName="$selectedBranchName" />

        <!-- Products Area -->
        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
            <!-- Search Bar -->
            <div class="mb-5 w-full">
                <x-pos.form.input 
                    model="search"
                    placeholder="Cari barang, SKU atau barcode..."
                    icon="ph-magnifying-glass"
                    live
                    class="!border-gray-300 dark:!border-gray-600 focus:!border-primary dark:focus:!border-blue-500"
                />
            </div>

            <!-- Categories -->
            <x-pos.category-list :activeCategory="$activeCategory" />

            <!-- Products Grid -->
            @if ($this->products->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <i class="ph ph-package text-6xl mb-3 opacity-40"></i>
                    <span class="text-sm font-medium">Tidak ada produk ditemukan</span>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    @foreach ($this->products as $variant)
                        <x-pos.product-card :variant="$variant" :selectedBranchId="$selectedBranchId" :selectedPricingTierId="$selectedPricingTierId" />
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <!-- Right Order Cart Sidebar -->
    <x-pos.cart-sidebar 
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
    />

    <!-- Payment Detail Modal -->
    <x-pos.payment-modal 
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
    />

    <!-- Create Customer Modal -->
    <x-pos.create-customer-modal 
        :showCreateCustomerModal="$showCreateCustomerModal"
        :newCustomerName="$newCustomerName"
        :newCustomerPhone="$newCustomerPhone"
        :newCustomerEmail="$newCustomerEmail"
        :newCustomerPricingTierId="$newCustomerPricingTierId"
        :newCustomerIsLoyaltyMember="$newCustomerIsLoyaltyMember"
        :pricingTiers="$pricingTiers"
    />

    <!-- Held Transactions List Modal -->
    <x-pos.modal 
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
    </x-pos.modal>
</div>
