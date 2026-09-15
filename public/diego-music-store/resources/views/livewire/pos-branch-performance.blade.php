<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Toast Notification Listener -->
        <x-pos.toast />

        <!-- Header -->
        <x-pos.navbar
            pageTitle="Manajemen & Performa Cabang"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 no-scrollbar">

            <!-- Filter Header & Branch Selector Bar -->
            @include('livewire.pos-branch-performance.components.filter-bar')

            <!-- Executive KPI Cards Grid (Laba Rugi, Stok, Pelanggan, Omset) -->
            @include('livewire.pos-branch-performance.components.kpi-summary')

            <!-- Breakdown Laba Rugi Multi-Step Cabang -->
            @include('livewire.pos-branch-performance.components.profit-loss-breakdown')

            <!-- Tabel Perbandingan Performa Seluruh Cabang (Entitas Bisnis Konsolidasi) -->
            @include('livewire.pos-branch-performance.components.branch-comparison-table')

        </div>
    </main>
</div>
