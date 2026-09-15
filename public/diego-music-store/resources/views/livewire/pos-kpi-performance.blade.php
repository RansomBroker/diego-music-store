<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Global Toast Notification Listener -->
        <x-pos.toast />

        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Performance KPI & Insentif Karyawan"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Top Header Filter Bar -->
                @include('livewire.pos-kpi-performance.components.filter-bar')

                <!-- Tabs Navigation -->
                @include('livewire.pos-kpi-performance.components.tabs-navigation')

                <!-- TAB 1: REKAP EVALUASI BULANAN & PENCATATAN BONUS -->
                @if ($activeTab === 'recap')
                    @include('livewire.pos-kpi-performance.components.recap-table')
                @endif

                <!-- TAB 2: REALTIME DASHBOARD KPI SAYA -->
                @if ($activeTab === 'dashboard')
                    @include('livewire.pos-kpi-performance.components.my-kpi-dashboard')
                @endif

                <!-- TAB 3: KELOLA TEMPLATE KPI PER JABATAN / USER -->
                @if ($activeTab === 'templates')
                    @include('livewire.pos-kpi-performance.components.templates-table')
                @endif

            </div>
        </div>
    </main>

    <!-- MODAL FORM TEMPLATE KPI -->
    @include('livewire.pos-kpi-performance.components.template-modal')
</div>
