<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <!-- Toast Notification Listener -->
        <x-pos.toast />

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Daftar Transaksi"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                <div>
                    <!-- Breadcrumbs -->
                    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-650 dark:text-slate-300 font-bold">Daftar Transaksi</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <!-- Page Title -->
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Daftar Transaksi</h1>
                </div>

                <!-- Table Card Wrapper (Filament Style) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

                    <!-- Toolbar (Filters & Search) -->
                    @include('livewire.pos-transactions.components.toolbar-filters')

                    <!-- Table List -->
                    @include('livewire.pos-transactions.components.transactions-table')

                </div>

            </div>
        </div>

    </main>

    <!-- Modal Detail Transaksi -->
    @include('livewire.pos-transactions.components.details-modal')

    <!-- Return Transaction Modal -->
    @include('livewire.pos-transactions.components.return-modal')

    <!-- Modal Pelunasan Piutang -->
    @include('livewire.pos-transactions.components.settlement-modal')
</div>
