<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Antrian Sales"
                backLabel="Dashboard"
            />

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto no-scrollbar p-6">
                <div class="w-full space-y-6">

                    <!-- Header & Breadcrumb -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <nav class="text-sm font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
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
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Antrian Sales</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                                Laporan Antrian & Giliran Sales {{ $currentBranch?->name ? ' - ' . $currentBranch->name : '' }}
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Rekapitulasi performa giliran sales melayani pelanggan toko fisik (Round-Robin Duty Cycle).
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button 
                                variant="success" 
                                size="sm" 
                                icon="ph-file-xls" 
                                wire:click="exportExcel"
                                wire:loading.attr="disabled"
                                wire:target="exportExcel"
                                title="Download Laporan Format Excel (.xlsx)"
                            >
                                <span wire:loading.remove wire:target="exportExcel">Export ke Excel</span>
                                <span wire:loading wire:target="exportExcel">Mengunduh...</span>
                            </x-pos.utility.button>

                            <x-pos.utility.button variant="primary" size="sm" icon="ph-storefront" href="{{ route('pos') }}">
                                Buka POS Kasir
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Sales KPI Summary Cards -->
                    @include('livewire.pos-reports-sales-queue.components.summary-cards')

                    <!-- Table Card Wrapper (Filament Style: Filters & Table Unified in 1 Card) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

                        <!-- Toolbar (Filters & Search) -->
                        @include('livewire.pos-reports-sales-queue.components.toolbar-filters')

                        <!-- Navigation Tabs (Riwayat Detail Sesi & Rekap Performa Sales) -->
                        <div class="flex border-b border-slate-200 dark:border-slate-800 overflow-x-auto no-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
                            <button
                                type="button"
                                wire:click="$set('activeTab', 'logs')"
                                class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'logs' ? 'border-primary text-primary dark:text-blue-400 bg-white dark:bg-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                            >
                                <i class="ph-bold ph-list-numbers text-base"></i>
                                <span>Riwayat Detail Sesi Giliran</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'logs' ? 'bg-primary/10 text-primary dark:text-blue-400' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $logs->total() }}
                                </span>
                            </button>

                            <button
                                type="button"
                                wire:click="$set('activeTab', 'leaderboard')"
                                class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'leaderboard' ? 'border-primary text-primary dark:text-blue-400 bg-white dark:bg-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                            >
                                <i class="ph-bold ph-trophy text-base"></i>
                                <span>Rekapitulasi Performa Sales (Leaderboard)</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'leaderboard' ? 'bg-amber-500/10 text-amber-500' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $leaderboard->count() }}
                                </span>
                            </button>
                        </div>

                        <!-- Active Tab Content -->
                        @if ($activeTab === 'logs')
                            @include('livewire.pos-reports-sales-queue.components.logs-table')
                        @else
                            @include('livewire.pos-reports-sales-queue.components.leaderboard-table')
                        @endif

                    </div>

                </div>
            </div>
        </main>
    </div>
</div>
