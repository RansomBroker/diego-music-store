<!-- Chart.js JS Script for Yearly Sales Performance -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initSalesEmployeeChart();
        setupSalesThemeObserver();
    });

    document.addEventListener('livewire:navigated', function () {
        initSalesEmployeeChart();
    });

    if (window.Livewire) {
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                setTimeout(() => { initSalesEmployeeChart(); }, 100);
            });
        });
    }

    function getSalesChartThemeColors() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            textColor: isDark ? '#cbd5e1' : '#475569',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)'
        };
    }

    function setupSalesThemeObserver() {
        if (window.salesThemeObserverSetup) return;
        window.salesThemeObserverSetup = true;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    initSalesEmployeeChart();
                }
            });
        });

        observer.observe(document.documentElement, { attributes: true });
    }

    function initSalesEmployeeChart() {
        const theme = getSalesChartThemeColors();

        const ctx = document.getElementById('yearlySalesPerformanceCanvas')?.getContext('2d');
        if (ctx) {
            if (window.yearlySalesChartInstance) window.yearlySalesChartInstance.destroy();
            window.yearlySalesChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($yearlyPerformanceChart['labels']),
                    datasets: [{
                        label: 'Omzet Personal (Rp)',
                        data: @json($yearlyPerformanceChart['values']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#059669'
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
    }
</script>
