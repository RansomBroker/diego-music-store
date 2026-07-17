<div class="flex flex-col h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
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
</div>
