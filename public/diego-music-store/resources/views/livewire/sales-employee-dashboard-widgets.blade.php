<div class="space-y-6">
    <!-- Include Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CONTAINER SALES & EMPLOYEE DASHBOARD WIDGETS -->
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-3xl p-6 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6 transition-colors duration-200">

        <!-- HEADER SECTION -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                    Performa Sales & Indikator Target
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Monitoring pencapaian target harian, komisi, tier bonus, &absensi personal
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-black">
                    {{ $monthlyTarget['progress_percent'] }}% Target Bulanan
                </span>
            </div>
        </div>

        <!-- GRID ROW 1: Target Bulanan (1), Target Harian (2), Komisi (3), Unlock Tier (4) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Pantau Target Penjualan Bulanan -->
            <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                    <span>Target Penjualan Bulanan</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="ph-bold ph-target text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="text-lg font-black text-slate-900 dark:text-white font-mono">
                        Rp {{ number_format($monthlyTarget['achieved_amount'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        Target: <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($monthlyTarget['target_amount'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="space-y-1 pt-1">
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                        <span>Pencapaian</span>
                        <span class="text-blue-600 dark:text-blue-400">{{ $monthlyTarget['progress_percent'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ $monthlyTarget['progress_percent'] }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- 2. Pantau Target Penjualan Harian -->
            <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                    <span>Target Penjualan Harian</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="ph-bold ph-calendar-check text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="text-lg font-black text-slate-900 dark:text-white font-mono">
                        Rp {{ number_format($dailyTarget['achieved_amount'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        Target Hari Ini: <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($dailyTarget['target_amount'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="space-y-1 pt-1">
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                        <span>Status: <strong class="text-emerald-600 dark:text-emerald-400">{{ $dailyTarget['status'] }}</strong></span>
                        <span class="text-emerald-600 dark:text-emerald-400">{{ $dailyTarget['progress_percent'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500" style="width: {{ $dailyTarget['progress_percent'] }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Komisi Penjualan Bulan Ini -->
            <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                    <span>Komisi Penjualan Bulan Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="ph-bold ph-coins text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono mt-1">
                        Rp {{ number_format($monthlyCommission, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Tier Aktif: <strong class="text-amber-600 dark:text-amber-300 font-extrabold">{{ $tierInfo['current_tier'] }} ({{ $tierInfo['current_rate'] }}%)</strong>
                    </div>
                </div>
                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center gap-1">
                    <i class="ph-bold ph-check-circle"></i> Berdasarkan Transaksi Lunas
                </div>
            </div>

            <!-- 4. Sisa Target Unlock Tier Komisi -->
            <div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-xs font-bold">
                    <span>Unlock Tier Komisi Next</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i class="ph-bold ph-lock-key-open text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-extrabold text-purple-700 dark:text-purple-300">
                        Target {{ $tierInfo['next_tier'] }}
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white font-mono mt-0.5">
                        Sisa: Rp {{ number_format($tierInfo['remaining_to_next'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="space-y-1 pt-1">
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-500">
                        <span>Progress Tier</span>
                        <span class="text-purple-600 dark:text-purple-400">{{ $tierInfo['tier_progress'] }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500" style="width: {{ $tierInfo['tier_progress'] }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID ROW 2: Leaderboard (5) & Produk Fokus Bulan Ini (6) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 5. Leaderboard Top 3 Sales -->
            <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-trophy text-amber-500 dark:text-amber-400"></i> Leaderboard Top 3 Sales Bulan Ini
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ranking staf penjualan tertinggi di toko</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($leaderboard as $index => $rank)
                        @php
                            $rankColorClass = match($rank['rank']) {
                                1 => 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 border-amber-300 dark:border-amber-500/40',
                                2 => 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-200 border-slate-300 dark:border-slate-600',
                                3 => 'bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-300 border-orange-300 dark:border-orange-500/40',
                                default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 border-slate-200',
                            };
                            $badgePillClass = match($rank['rank']) {
                                1 => 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border-amber-500/30',
                                2 => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600',
                                3 => 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 border-orange-500/30',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 shadow-sm hover:scale-[1.01] transition-all">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <!-- Rank Medal Avatar -->
                                <div class="w-10 h-10 rounded-2xl {{ $rankColorClass }} border text-xl font-black flex items-center justify-center flex-shrink-0 shadow-sm">
                                    {{ $rank['medal'] }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">{{ $rank['name'] }}</h4>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border tracking-wider flex-shrink-0 {{ $badgePillClass }}">
                                            {{ $rank['badge_name'] ?? 'Top Sales' }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Rank #{{ $rank['rank'] }} Sales Staff</div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <div class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($rank['sales_val'], 0, ',', '.') }}
                                </div>
                                <div class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider">Total Omzet</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-6">Belum ada data ranking transaksi bulan ini</div>
                    @endforelse
                </div>
            </div>

            <!-- 6. Produk Fokus Bulan Ini -->
            <div class="lg:col-span-6 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-star text-purple-500 dark:text-purple-400"></i> Produk Fokus Bulan Ini
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Produk prioritas dengan insentif komisi ekstra</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse ($focusProducts as $item)
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate max-w-[130px]">{{ $item['name'] }}</div>
                                <div class="text-[10px] text-slate-400">{{ $item['category'] }}</div>
                                <div class="text-xs font-mono font-black text-primary dark:text-blue-400 mt-1">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/40 rounded-lg text-[9px] font-black uppercase text-center">
                                {{ $item['incentive'] }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-2 text-center text-xs text-slate-400 py-4">Belum ada produk fokus terdaftar</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- GRID ROW 3: Grafik Performa Sales 1 Tahun (7) & Info/Bar Absensi (8) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 7. Grafik Performa Sales Tren 1 Tahun -->
            <div class="lg:col-span-8 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="ph-bold ph-chart-line-up text-emerald-500 dark:text-emerald-400"></i> Grafik Performa Penjualan Sales (1 Tahun)
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Tren pencapaian omzet personal dalam 12 bulan terakhir</p>
                    </div>
                </div>
                <div class="h-60">
                    <canvas id="yearlySalesPerformanceCanvas"></canvas>
                </div>
            </div>

            <!-- 8. Info / Bar Absensi -->
            <div class="lg:col-span-4 bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="ph-bold ph-user-check text-blue-500 dark:text-blue-400"></i> Info & Bar Absensi Staf
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Ringkasan kehadiran & sisa kuota off-day bulan ini</p>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                    <div class="flex items-center justify-between text-xs font-extrabold">
                        <span class="text-slate-600 dark:text-slate-300">Total Kehadiran:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono text-sm">{{ $attendanceInfo['total_hadir'] }} Hari</span>
                    </div>

                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] font-bold text-slate-400">
                            <span>Tingkat Kehadiran</span>
                            <span>{{ $attendanceInfo['attendance_percent'] }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $attendanceInfo['attendance_percent'] }}%;"></div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between text-xs font-semibold">
                        <span class="text-slate-500 dark:text-slate-400">Kuota Off-Day Terpakai:</span>
                        <span class="{{ $attendanceInfo['is_over_quota'] ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-800 dark:text-slate-200 font-bold' }}">
                            {{ $attendanceInfo['used_off_days'] }} / {{ $attendanceInfo['monthly_off_days_quota'] }} Hari
                        </span>
                    </div>

                    @if ($attendanceInfo['is_over_quota'])
                        <div class="p-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-[10px] font-black uppercase text-center">
                            ⚠️ Terdeteksi Melebihi Kuota Off-Day!
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

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
</div>
