<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- POS Global Toast Notification Listener -->
    <x-pos.toast />

    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Manajemen Komisi Sales"
            backLabel="Dashboard"
            backUrl="/pos/dashboard"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Header Actions & Period Filter Bar -->
                @include('livewire.pos-commissions.components.filter-bar')

                <!-- KPI Summary Cards -->
                @include('livewire.pos-commissions.components.summary-cards')

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto no-scrollbar">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'recap')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'recap' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <i class="ph-bold ph-users-three text-base"></i>
                        <span>1. Rekap Komisi Sales</span>
                    </button>

                    <button
                        type="button"
                        wire:click="$set('activeTab', 'schemes')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'schemes' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <i class="ph-bold ph-sliders text-base"></i>
                        <span>2. Skema & Aturan Komisi</span>
                    </button>

                    <button
                        type="button"
                        wire:click="$set('activeTab', 'logs')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'logs' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <i class="ph-bold ph-list-bullets text-base"></i>
                        <span>3. Log Transaksi Komisi</span>
                    </button>

                    <button
                        type="button"
                        wire:click="$set('activeTab', 'groups')"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'groups' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}"
                    >
                        <i class="ph-bold ph-users-four text-base"></i>
                        <span>4. Komisi Grup</span>
                    </button>
                </div>

                <!-- TAB 1: REKAP KOMISI SALES PER KARYAWAN -->
                @if ($activeTab === 'recap')
                    @include('livewire.pos-commissions.components.recap-table')
                @endif

                <!-- TAB 2: SKEMA & ATURAN KOMISI -->
                @if ($activeTab === 'schemes')
                    @include('livewire.pos-commissions.components.schemes-table')
                @endif

                <!-- TAB 3: LOG TRANSAKSI KOMISI -->
                @if ($activeTab === 'logs')
                    @include('livewire.pos-commissions.components.logs-table')
                @endif

                <!-- TAB 4: KOMISI GRUP & OVERRIDE -->
                @if ($activeTab === 'groups')
                    @include('livewire.pos-commissions.components.groups-table')
                @endif

            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-commissions.components.scheme-modal')
    @include('livewire.pos-commissions.components.group-modal')
    @include('livewire.pos-commissions.components.group-detail-modal')
</div>
