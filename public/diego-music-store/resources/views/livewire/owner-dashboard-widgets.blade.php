<div class="space-y-6">
    @if (!$isOwner)
        <!-- Hidden for non-owner roles -->
    @else
        <!-- Include Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- OWNER EXECUTIVE DASHBOARD CONTAINER -->
        <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6 transition-colors duration-200">

            <!-- DASHBOARD HEADER & FILTER TOOLBAR (Item 11) -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Analisis Performansi & Keuangan Bisnis
                    </h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <a href="{{ route('pos.branch-performance') }}" class="px-3 py-1.5 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-500/30 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                            <i class="ph-bold ph-buildings text-sm"></i> Performa Cabang
                        </a>
                        <a href="{{ route('pos') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                            <i class="ph-bold ph-shopping-cart text-sm"></i> POS Kasir
                        </a>
                        <a href="/backoffice" class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                            <i class="ph-bold ph-house text-sm"></i> Backoffice
                        </a>
                    </div>
                </div>

                <!-- Interactive Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Date From -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                        <input type="date" wire:model.live="dateFrom" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none transition">
                    </div>

                    <!-- Date To -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                        <input type="date" wire:model.live="dateTo" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none transition">
                    </div>

                    <!-- Branch Filter -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Cabang Toko</label>
                        <select wire:model.live="branchId" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none transition">
                            <option value="">Semua Cabang</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Category Filter -->
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">Kategori Produk</label>
                        <select wire:model.live="productCategory" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:outline-none transition">
                            <option value="">Semua Kategori</option>
                            @foreach ($productCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Filter Button -->
                    <div class="flex flex-col justify-end">
                        <button wire:click="resetFilters" class="px-3 py-1.5 bg-rose-50 dark:bg-rose-500/20 hover:bg-rose-100 dark:hover:bg-rose-500/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/40 text-xs font-bold rounded-xl transition flex items-center gap-1">
                            <i class="ph-bold ph-arrows-counter-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- 1. RINGKASAN KEUANGAN CARDS (Item 1) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Penjualan -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-emerald-500/50 transition">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                        <span>Total Penjualan</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i class="ph-bold ph-trend-up text-lg"></i>
                        </div>
                    </div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-2">
                        Rp {{ number_format($financialSummary['total_penjualan'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Est. Laba Kotor: <strong class="text-slate-800 dark:text-white">Rp {{ number_format($financialSummary['laba_kotor_est'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                <!-- Total Pengeluaran -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-rose-500/50 transition">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                        <span>Total Pengeluaran Kas</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                            <i class="ph-bold ph-receipt text-lg"></i>
                        </div>
                    </div>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono mt-2">
                        Rp {{ number_format($financialSummary['total_pengeluaran'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Beban Operasional & Kas Keluar
                    </div>
                </div>

                <!-- Total Hutang Supplier -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-amber-500/50 transition">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                        <span>Hutang Supplier (Kredit)</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i class="ph-bold ph-hand-coins text-lg"></i>
                        </div>
                    </div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono mt-2">
                        Rp {{ number_format($financialSummary['total_hutang'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Sisa Belum Lunas Supplier
                    </div>
                </div>

                <!-- Saldo Kas & Bank -->
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 hover:border-blue-500/50 transition">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                        <span>Saldo Kas & Bank</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i class="ph-bold ph-vault text-lg"></i>
                        </div>
                    </div>
                    <div class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono mt-2">
                        Rp {{ number_format($financialSummary['saldo_kas_bank'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Akumulasi Kas Toko & Rekening
                    </div>
                </div>
            </div>

            <!-- GRID ROW 1: Penjualan vs Pembelian (Item 2) & Turn Over Stok (Item 3) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Grafik Penjualan vs Pembelian -->
                <div class="lg:col-span-7 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-chart-line-up text-emerald-600 dark:text-emerald-400"></i> Penjualan vs Pembelian
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Grafik area perbandingan omzet vs pasokan barang</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="salesVsPurchasesChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Grafik Turn Over Stok -->
                <div class="lg:col-span-5 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-arrows-clockwise text-indigo-600 dark:text-indigo-400"></i> Turn Over Stok
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Rasio kecepatan perputaran persediaan per kategori</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="stockTurnoverChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRID ROW 2: Pareto 80/20 (Item 4) & Monthly Trend + Forecast (Item 5) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Grafik Pareto 80/20 -->
                <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-sparkle text-amber-500 dark:text-amber-400"></i> Analisis Pareto 80/20
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Produk & Pelanggan penyumbang 80% omzet terbesar</p>
                        </div>
                        <span class="px-2.5 py-0.5 bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-black rounded-full border border-amber-500/30">
                            Ratio: {{ $paretoChart['products']['pareto_ratio'] ?? 0 }}% Top Items
                        </span>
                    </div>

                    <!-- Pareto Summary Table -->
                    <div class="overflow-x-auto max-h-56 no-scrollbar">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-200/70 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 uppercase text-[9px] font-bold">
                                <tr>
                                    <th class="px-3 py-2">Item / Pelanggan</th>
                                    <th class="px-3 py-2 text-right">Omzet</th>
                                    <th class="px-3 py-2 text-center">Pareto Class</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                                @forelse (array_slice($paretoChart['products']['items'], 0, 6) as $p)
                                    <tr class="hover:bg-slate-100 dark:hover:bg-slate-700/30 transition">
                                        <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-200 truncate max-w-[150px]">{{ $p['name'] }}</td>
                                        <td class="px-3 py-2 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($p['value'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold {{ $p['is_top_80'] ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                                {{ $p['pareto_class'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-slate-400">Belum ada data transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grafik Tren Penjualan Bulanan & Prediksi -->
                <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-trend-up text-blue-600 dark:text-blue-400"></i> Tren & Prediksi Penjualan
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Historis tren & proyeksi regresi linier bulan depan</p>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold">Proyeksi Bulan Depan</div>
                            <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                Rp {{ number_format($monthlyTrendChart['predicted_next_val'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="h-52">
                        <canvas id="monthlyTrendChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRID ROW 3: Kategori (Item 6) & Per-Cabang (Item 7) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Penjualan Per-Kategori -->
                <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-circles-three text-purple-600 dark:text-purple-400"></i> Penjualan Per-Kategori
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Kontribusi omzet & laba kotor per kategori produk</p>
                        </div>
                    </div>
                    <div class="h-60">
                        <canvas id="categoryChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Penjualan Per-Cabang -->
                <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-buildings text-cyan-600 dark:text-cyan-400"></i> Penjualan Per-Cabang
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Kontribusi omzet & profit per cabang toko</p>
                        </div>
                    </div>
                    <div class="h-60">
                        <canvas id="branchChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRID ROW 4: Daily Traffic (Item 8) & Hourly Traffic Jam (Item 9) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Grafik Monthly Report (Daily Visitor Low/Peak) -->
                <div class="lg:col-span-7 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-calendar-blank text-rose-600 dark:text-rose-400"></i> Monthly Traffic (Daily Low & Peak)
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Pola jumlah pengunjung & transaksi harian toko</p>
                        </div>
                        <div class="flex items-center gap-2 text-[10px]">
                            <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 rounded font-bold">
                                Peak: {{ $dailyTrafficChart['peak_day'] }} ({{ $dailyTrafficChart['peak_count'] }} Transaksi)
                            </span>
                            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 rounded font-bold">
                                Low: {{ $dailyTrafficChart['low_day'] }} ({{ $dailyTrafficChart['low_count'] }} Transaksi)
                            </span>
                        </div>
                    </div>
                    <div class="h-56">
                        <canvas id="dailyTrafficChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Grafik Waktu Pengunjung (Hourly Traffic Jam) -->
                <div class="lg:col-span-5 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <i class="ph-bold ph-clock-afternoon text-amber-600 dark:text-amber-400"></i> Hourly Traffic Jam (Jam Sibuk)
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Kepadatan pengunjung per jam operasional</p>
                        </div>
                        <span class="px-2.5 py-0.5 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-black rounded-full border border-amber-300 dark:border-amber-500/30">
                            Busy: {{ $hourlyTrafficChart['busy_hour'] }}
                        </span>
                    </div>
                    <div class="h-56">
                        <canvas id="hourlyTrafficChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRID ROW 5: Grafik Performa Sales (Item 10) -->
            <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-user-check text-emerald-600 dark:text-emerald-400"></i> Performa Sales & Absensi Staf
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Capaian target omzet bulanan & persentase tingkat kehadiran staf sales</p>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="salesPerformanceChartCanvas"></canvas>
                </div>
            </div>

        </div>

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
                            labels: @json($salesVsPurchasesChart['labels']),
                            datasets: [
                                {
                                    label: 'Penjualan (Omzet)',
                                    data: @json($salesVsPurchasesChart['sales']),
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Pembelian (Pasokan)',
                                    data: @json($salesVsPurchasesChart['purchases']),
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
                            labels: @json($stockTurnoverChart['labels']),
                            datasets: [{
                                label: 'Rasio Perputaran Stok',
                                data: @json($stockTurnoverChart['turnover_ratios']),
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
                            labels: @json($monthlyTrendChart['forecast_labels']),
                            datasets: [{
                                label: 'Tren Omzet + Prediksi',
                                data: @json($monthlyTrendChart['forecast_data']),
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
                            labels: @json($categoryChart['labels']),
                            datasets: [{
                                data: @json($categoryChart['revenues']),
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
                            labels: @json($branchChart['labels']),
                            datasets: [
                                { label: 'Omzet Penjualan', data: @json($branchChart['revenues']), backgroundColor: '#06b6d4', borderRadius: 6 },
                                { label: 'Laba Kotor Est.', data: @json($branchChart['profits']), backgroundColor: '#8b5cf6', borderRadius: 6 }
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
                            labels: @json($dailyTrafficChart['labels']),
                            datasets: [{
                                label: 'Jumlah Transaksi / Hari',
                                data: @json($dailyTrafficChart['counts']),
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
                            labels: @json($hourlyTrafficChart['labels']),
                            datasets: [{
                                label: 'Kepadatan Jam Transaksi',
                                data: @json($hourlyTrafficChart['counts']),
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
                            labels: @json($salesPerformanceChart['labels']),
                            datasets: [
                                { label: 'Capaian Sales (Rp)', data: @json($salesPerformanceChart['achieved_sales']), backgroundColor: '#10b981', borderRadius: 6 },
                                { label: 'Target Sales (Rp)', data: @json($salesPerformanceChart['target_sales']), backgroundColor: '#94a3b8', borderRadius: 6 }
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
    @endif
</div>
