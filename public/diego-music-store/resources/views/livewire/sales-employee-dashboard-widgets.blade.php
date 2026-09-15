<div class="space-y-6">
    <!-- Include Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CONTAINER SALES & EMPLOYEE DASHBOARD WIDGETS -->
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6 transition-colors duration-200">

        <!-- HEADER SECTION -->
        @include('livewire.sales-employee-dashboard-widgets.components.header-indicators')

        <!-- GRID ROW 1: Target Bulanan (1), Target Harian (2), Komisi (3), Unlock Tier (4) -->
        @include('livewire.sales-employee-dashboard-widgets.components.kpi-cards')

        <!-- GRID ROW 2: Leaderboard (5) & Produk Fokus Bulan Ini (6) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-6">
                @include('livewire.sales-employee-dashboard-widgets.components.leaderboard')
            </div>
            <div class="lg:col-span-6">
                @include('livewire.sales-employee-dashboard-widgets.components.focus-products')
            </div>
        </div>

        <!-- GRID ROW 3: Grafik Performa Sales 1 Tahun (7) & Info/Bar Absensi (8) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                @include('livewire.sales-employee-dashboard-widgets.components.yearly-performance-chart')
            </div>
            <div class="lg:col-span-4">
                @include('livewire.sales-employee-dashboard-widgets.components.attendance-summary')
            </div>
        </div>

    </div>

    <!-- Chart.js JS Script for Yearly Sales Performance -->
    @include('livewire.sales-employee-dashboard-widgets.components.chart-scripts')
</div>
