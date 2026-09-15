<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Pusat Laporan & Analisis Keuangan ERP"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Header & Navigation Bar -->
                @include('livewire.pos-reports.components.header')

                <!-- 5 Tab Executive Navigation Bar -->
                @include('livewire.pos-reports.components.tabs-bar')

                <!-- Common Filter Toolbar Card -->
                @include('livewire.pos-reports.components.filter-bar')

                <!-- Tab Contents -->
                @if ($activeTab === 'sales')
                    @include('livewire.pos-reports.components.tab-sales')
                @elseif ($activeTab === 'ar-aging')
                    @include('livewire.pos-reports.components.tab-ar-aging')
                @elseif ($activeTab === 'ar-settlement')
                    @include('livewire.pos-reports.components.tab-ar-settlement')
                @elseif ($activeTab === 'daily-cash')
                    @include('livewire.pos-reports.components.tab-daily-cash')
                @elseif ($activeTab === 'stock-prices')
                    @include('livewire.pos-reports.components.tab-stock-prices')
                @endif

            </div>
        </div>
    </main>
</div>
