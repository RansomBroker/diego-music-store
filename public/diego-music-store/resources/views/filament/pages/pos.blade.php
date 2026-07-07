<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos.sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <x-pos.header :branches="$branches" :selectedBranchId="$selectedBranchId" :selectedStoreName="$selectedStoreName" :selectedBranchName="$selectedBranchName" />

        <!-- Products Area -->
        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
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
        :discountAmount="$discountAmount"
        :taxAmount="$this->taxAmount"
        :grandTotal="$this->grandTotal"
        :pricingTiers="$pricingTiers"
        :selectedPricingTierId="$selectedPricingTierId"
        :enableTax="$enableTax"
        :taxPercent="$taxPercent"
    />

    <!-- Payment Detail Modal -->
    <x-pos.payment-modal 
        :showPaymentModal="$showPaymentModal"
        :paymentMethod="$paymentMethod"
        :grandTotal="$this->grandTotal"
        :amountPaid="$amountPaid"
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
</div>
