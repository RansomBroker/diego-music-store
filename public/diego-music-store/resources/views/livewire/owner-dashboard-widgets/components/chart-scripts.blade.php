<!-- Chart.js JS Script Renderer with Re-rendering on Dark/Light Theme Switch -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initOwnerCharts();
        setupThemeObserver();
    });

    document.addEventListener('livewire:navigated', function () {
        initOwnerCharts();
    });

    if (window.Livewire) {
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                setTimeout(() => { initOwnerCharts(); }, 100);
            });
        });
    }

    function getChartThemeColors() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            textColor: isDark ? '#cbd5e1' : '#475569',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)'
        };
    }

    function setupThemeObserver() {
        if (window.themeObserverSetup) return;
        window.themeObserverSetup = true;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    initOwnerCharts();
                }
            });
        });

        observer.observe(document.documentElement, { attributes: true });
    }

    function initOwnerCharts() {
        const theme = getChartThemeColors();

        // 1. Sales vs Purchases Chart
        const ctxSvP = document.getElementById('salesVsPurchasesChartCanvas')?.getContext('2d');
        if (ctxSvP) {
            if (window.salesVsPurchasesChartInstance) window.salesVsPurchasesChartInstance.destroy();
            window.salesVsPurchasesChartInstance = new Chart(ctxSvP, {
                type: 'line',
                data: {
                    labels: @json($salesVsPurchasesChart['labels'] ?? []),
                    datasets: [
                        {
                            label: 'Penjualan (Omzet)',
                            data: @json($salesVsPurchasesChart['sales'] ?? []),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Pembelian (Pasokan)',
                            data: @json($salesVsPurchasesChart['purchases'] ?? []),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.15)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 2. Stock Turnover Chart
        const ctxTurn = document.getElementById('stockTurnoverChartCanvas')?.getContext('2d');
        if (ctxTurn) {
            if (window.stockTurnoverChartInstance) window.stockTurnoverChartInstance.destroy();
            window.stockTurnoverChartInstance = new Chart(ctxTurn, {
                type: 'bar',
                data: {
                    labels: @json($stockTurnoverChart['labels'] ?? []),
                    datasets: [{
                        label: 'Rasio Perputaran Stok',
                        data: @json($stockTurnoverChart['turnover_ratios'] ?? []),
                        backgroundColor: '#818cf8',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 3. Monthly Trend & Forecast Chart
        const ctxTrend = document.getElementById('monthlyTrendChartCanvas')?.getContext('2d');
        if (ctxTrend) {
            if (window.monthlyTrendChartInstance) window.monthlyTrendChartInstance.destroy();
            window.monthlyTrendChartInstance = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: @json($monthlyTrendChart['forecast_labels'] ?? []),
                    datasets: [{
                        label: 'Tren Omzet + Prediksi',
                        data: @json($monthlyTrendChart['forecast_data'] ?? []),
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.15)',
                        borderDash: [5, 5],
                        pointRadius: 5,
                        pointBackgroundColor: '#0284c7',
                        fill: true,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 4. Sales by Category Chart
        const ctxCat = document.getElementById('categoryChartCanvas')?.getContext('2d');
        if (ctxCat) {
            if (window.categoryChartInstance) window.categoryChartInstance.destroy();
            window.categoryChartInstance = new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryChart['labels'] ?? []),
                    datasets: [{
                        data: @json($categoryChart['revenues'] ?? []),
                        backgroundColor: ['#c084fc', '#60a5fa', '#34d399', '#fbbf24', '#f87171', '#a78bfa']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: theme.textColor } } }
                }
            });
        }

        // 5. Sales by Branch Chart
        const ctxBranch = document.getElementById('branchChartCanvas')?.getContext('2d');
        if (ctxBranch) {
            if (window.branchChartInstance) window.branchChartInstance.destroy();
            window.branchChartInstance = new Chart(ctxBranch, {
                type: 'bar',
                data: {
                    labels: @json($branchChart['labels'] ?? []),
                    datasets: [
                        { label: 'Omzet Penjualan', data: @json($branchChart['revenues'] ?? []), backgroundColor: '#06b6d4', borderRadius: 6 },
                        { label: 'Laba Kotor Est.', data: @json($branchChart['profits'] ?? []), backgroundColor: '#8b5cf6', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 6. Daily Traffic Chart
        const ctxDaily = document.getElementById('dailyTrafficChartCanvas')?.getContext('2d');
        if (ctxDaily) {
            if (window.dailyTrafficChartInstance) window.dailyTrafficChartInstance.destroy();
            window.dailyTrafficChartInstance = new Chart(ctxDaily, {
                type: 'bar',
                data: {
                    labels: @json($dailyTrafficChart['labels'] ?? []),
                    datasets: [{
                        label: 'Jumlah Transaksi / Hari',
                        data: @json($dailyTrafficChart['counts'] ?? []),
                        backgroundColor: '#f43f5e',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 7. Hourly Traffic Jam Chart
        const ctxHourly = document.getElementById('hourlyTrafficChartCanvas')?.getContext('2d');
        if (ctxHourly) {
            if (window.hourlyTrafficChartInstance) window.hourlyTrafficChartInstance.destroy();
            window.hourlyTrafficChartInstance = new Chart(ctxHourly, {
                type: 'line',
                data: {
                    labels: @json($hourlyTrafficChart['labels'] ?? []),
                    datasets: [{
                        label: 'Kepadatan Jam Transaksi',
                        data: @json($hourlyTrafficChart['counts'] ?? []),
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.15)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }

        // 8. Sales Performance Chart
        const ctxSalesPerf = document.getElementById('salesPerformanceChartCanvas')?.getContext('2d');
        if (ctxSalesPerf) {
            if (window.salesPerfChartInstance) window.salesPerfChartInstance.destroy();
            window.salesPerfChartInstance = new Chart(ctxSalesPerf, {
                type: 'bar',
                data: {
                    labels: @json($salesPerformanceChart['labels'] ?? []),
                    datasets: [
                        { label: 'Capaian Sales (Rp)', data: @json($salesPerformanceChart['achieved_sales'] ?? []), backgroundColor: '#10b981', borderRadius: 6 },
                        { label: 'Target Sales (Rp)', data: @json($salesPerformanceChart['target_sales'] ?? []), backgroundColor: '#94a3b8', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: theme.textColor } } },
                    scales: {
                        x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                        y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                    }
                }
            });
        }
    }
</script>
