<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Pelunasan Piutang"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
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
                                        <i class="ph ph-caret-right text-[10px] text-slate-355 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Pelunasan Piutang</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <!-- Page Title -->
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pelunasan Piutang Pelanggan</h1>
                    </div>

                    <!-- Add Action -->
                    <button
                        wire:click="openCreate"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer active:scale-[0.98]"
                    >
                        <i class="ph-bold ph-plus text-sm"></i>
                        <span>Pelunasan Piutang</span>
                    </button>
                </div>

                <!-- Filters & Table Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    @include('livewire.pos-customer-payments.components.filter-bar')
                    @include('livewire.pos-customer-payments.components.payments-table')
                </div>

            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-customer-payments.components.create-modal')
    @include('livewire.pos-customer-payments.components.detail-modal')
</div>
