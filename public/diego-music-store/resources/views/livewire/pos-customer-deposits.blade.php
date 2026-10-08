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

                <!-- Page Header (Title & Breadcrumbs & CTA) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <!-- Breadcrumbs -->
                        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-400 dark:text-slate-600 mx-1"></i>
                                        <span class="text-slate-600 dark:text-slate-300 font-bold">Deposit Pelanggan</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <!-- Page Title -->
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight flex items-center gap-2.5">
                            <span>Deposit & Titipan Dana Pelanggan</span>
                        </h1>
                    </div>

                    <!-- Add Action Button -->
                    <x-pos.utility.button
                        variant="primary"
                        icon="ph-plus"
                        wire:click="openCreateModal"
                    >
                        Tambah Deposit Baru
                    </x-pos.utility.button>
                </div>

                <!-- Unified Table & Filters Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-2xl overflow-hidden transition-colors duration-200">
                    @include('livewire.pos-customer-deposits.components.toolbar')
                    @include('livewire.pos-customer-deposits.components.deposits-table')
                </div>

            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-customer-deposits.components.deposit-modal')
    @include('livewire.pos-customer-deposits.components.settle-modal')

</div>
