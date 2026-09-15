<div class="space-y-6">
    @if (!$isOwner)
        <!-- Hidden for non-owner roles -->
    @else
        <!-- Include Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- OWNER EXECUTIVE DASHBOARD CONTAINER -->
        <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6 transition-colors duration-200">

            <!-- DASHBOARD HEADER & FILTER TOOLBAR -->
            @include('livewire.owner-dashboard-widgets.components.header-filters')

            <!-- 1. RINGKASAN KEUANGAN CARDS -->
            @include('livewire.owner-dashboard-widgets.components.financial-summary')

            <!-- GRID ROW 1: Penjualan vs Pembelian & Turn Over Stok -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7">
                    @include('livewire.owner-dashboard-widgets.components.sales-vs-purchases')
                </div>
                <div class="lg:col-span-5">
                    @include('livewire.owner-dashboard-widgets.components.stock-turnover')
                </div>
            </div>

            <!-- GRID ROW 2: Pareto 80/20 & Monthly Trend + Forecast -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-6">
                    @include('livewire.owner-dashboard-widgets.components.pareto-analysis')
                </div>
                <div class="lg:col-span-6">
                    @include('livewire.owner-dashboard-widgets.components.monthly-trend')
                </div>
            </div>

            <!-- GRID ROW 3: Kategori & Per-Cabang -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-6">
                    @include('livewire.owner-dashboard-widgets.components.category-distribution')
                </div>
                <div class="lg:col-span-6">
                    @include('livewire.owner-dashboard-widgets.components.branch-distribution')
                </div>
            </div>

            <!-- GRID ROW 4: Daily Traffic & Hourly Traffic Jam -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7">
                    @include('livewire.owner-dashboard-widgets.components.daily-traffic')
                </div>
                <div class="lg:col-span-5">
                    @include('livewire.owner-dashboard-widgets.components.hourly-traffic')
                </div>
            </div>

            <!-- GRID ROW 5: Grafik Performa Sales -->
            @include('livewire.owner-dashboard-widgets.components.sales-performance')

        </div>

        <!-- Chart.js Scripts -->
        @include('livewire.owner-dashboard-widgets.components.chart-scripts')
    @endif
</div>
