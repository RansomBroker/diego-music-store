<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Piutang Usaha (AR Aging)"
                backLabel="Dashboard"
            />

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto no-scrollbar p-6">
                <div class="w-full space-y-6">

                    <!-- Header & Breadcrumb -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                    <li class="inline-flex items-center">
                                        <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-400">Laporan</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Laporan Piutang</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                                Laporan Piutang Usaha (AR Aging)
                            </h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Comprehensive Filter Toolbar Card -->
                    @include('livewire.pos-reports-ar-aging.components.filter-card')

                    <!-- AR Aging Summary Cards -->
                    @include('livewire.pos-reports-ar-aging.components.summary-cards')

                    <!-- AR Table Card -->
                    @include('livewire.pos-reports-ar-aging.components.aging-table')

                </div>
            </div>
        </main>
    </div>

    <!-- Modal Detail & Histori Laporan AR Aging -->
    @include('livewire.pos-reports-ar-aging.components.detail-modal')

    <!-- Formal ERP Print Template -->
    @include('livewire.pos-reports-ar-aging.components.print-template')
</div>
