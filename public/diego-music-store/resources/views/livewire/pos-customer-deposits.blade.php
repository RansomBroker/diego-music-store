<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    {{-- POS Sidebar --}}
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Deposit Pelanggan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header, Breadcrumbs & Toolbar Filters -->
                @include('livewire.pos-customer-deposits.components.toolbar')

                <!-- Unified Table & Filters Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-2xl overflow-hidden transition-colors duration-200">
                    @include('livewire.pos-customer-deposits.components.deposits-table')
                </div>

            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-customer-deposits.components.deposit-modal')
    @include('livewire.pos-customer-deposits.components.settle-modal')

</div>
