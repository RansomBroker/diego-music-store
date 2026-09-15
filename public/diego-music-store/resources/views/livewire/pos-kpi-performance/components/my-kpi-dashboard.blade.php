<!-- TAB 2: REALTIME DASHBOARD KPI SAYA -->
<div class="space-y-6">
    <!-- Month Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Dashboard Realtime KPI Staf</h3>
            <p class="text-xs text-slate-400">Pencapaian 4 indikator performa & estimasi perolehan bonus</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Periode:</span>
            <div class="w-full sm:w-44">
                <x-pos.form.input
                    type="month"
                    model="filterMonth"
                    :live="true"
                    icon="ph-calendar"
                    size="sm"
                />
            </div>
        </div>
    </div>

    @if ($myEvaluation)
        <!-- Upper Summary KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Final Composite Score Card -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-extrabold tracking-wider opacity-80">Skor KPI Komposit Saya</span>
                    <span class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold">
                        <i class="ph-bold ph-chart-line-up"></i>
                    </span>
                </div>
                <div class="my-4">
                    <div class="text-5xl font-black tracking-tight">{{ $myEvaluation->final_kpi_score }}%</div>
                    <p class="text-xs text-blue-100 mt-1 font-medium">Periode Perhitungan: {{ $filterMonth }}</p>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-300 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $myEvaluation->final_kpi_score) }}%"></div>
                </div>
            </div>

            <!-- Earned Bonus Card -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-extrabold tracking-wider opacity-80">Estimasi Bonus Cair</span>
                    <span class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold">
                        <i class="ph-bold ph-currency-dollar"></i>
                    </span>
                </div>
                <div class="my-4">
                    <div class="text-3xl font-black tracking-tight">Rp {{ number_format($myEvaluation->earned_bonus_amount, 0, ',', '.') }}</div>
                    <p class="text-xs text-emerald-100 mt-1 font-medium">Maksimal Potensi Bonus: Rp {{ number_format($myEvaluation->template->max_bonus_amount ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Template Info Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Template KPI Dipakai</span>
                    <h4 class="text-lg font-black text-slate-900 dark:text-slate-100 mt-1">
                        {{ $myEvaluation->template->name ?? 'Default KPI' }}
                    </h4>
                    <span class="text-xs font-bold text-primary dark:text-blue-400 bg-primary/10 py-0.5 px-2.5 rounded-full mt-2 inline-block">
                        Jabatan: {{ $myEvaluation->template->position ?? 'Umum' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4">
                    Skor komposit dihitung berdasarkan bobot % dari ke-4 indikator performa Anda.
                </p>
            </div>
        </div>

        <!-- 4 Indicator Visual Progress Bars -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-6">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">
                Breakdown 4 Indikator Performa Utama
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Indikator 1: Target Sales -->
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">1. Target Omset Penjualan</h4>
                            <span class="text-[10px] text-slate-400">Bobot: {{ $myEvaluation->template->weight_sales ?? 40 }}%</span>
                        </div>
                        <span class="text-sm font-black text-primary dark:text-blue-400">{{ $myEvaluation->sales_score }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ min(100, $myEvaluation->sales_score) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-500">
                        <span>Capai: Rp {{ number_format($myEvaluation->actual_sales_amount, 0, ',', '.') }}</span>
                        <span>Target: Rp {{ number_format($myEvaluation->template->target_sales_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Indikator 2: ATV -->
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">2. Average Transaction Value (ATV)</h4>
                            <span class="text-[10px] text-slate-400">Bobot: {{ $myEvaluation->template->weight_atv ?? 20 }}%</span>
                        </div>
                        <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $myEvaluation->atv_score }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $myEvaluation->atv_score) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-500">
                        <span>Capai: Rp {{ number_format($myEvaluation->actual_atv_amount, 0, ',', '.') }}</span>
                        <span>Target: Rp {{ number_format($myEvaluation->template->target_atv_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Indikator 3: Absensi -->
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">3. Tingkat Kehadiran Absensi</h4>
                            <span class="text-[10px] text-slate-400">Bobot: {{ $myEvaluation->template->weight_attendance ?? 20 }}%</span>
                        </div>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $myEvaluation->attendance_score }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $myEvaluation->attendance_score) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-500">
                        <span>Capai: {{ $myEvaluation->actual_attendance_pct }}%</span>
                        <span>Target: {{ $myEvaluation->template->target_attendance_pct ?? 95 }}%</span>
                    </div>
                </div>

                <!-- Indikator 4: Ketepatan Waktu Datang -->
                <div class="p-5 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">4. Ketepatan Waktu Datang (No Late)</h4>
                            <span class="text-[10px] text-slate-400">Bobot: {{ $myEvaluation->template->weight_punctuality ?? 20 }}%</span>
                        </div>
                        <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $myEvaluation->punctuality_score }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $myEvaluation->punctuality_score) }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] font-semibold text-slate-500">
                        <span>Capai: {{ $myEvaluation->actual_punctuality_pct }}%</span>
                        <span>Target: {{ $myEvaluation->template->target_punctuality_pct ?? 95 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-xl p-12 text-center text-slate-400 border border-slate-200 dark:border-slate-800 shadow-sm">
            <i class="ph-bold ph-warning-circle text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
            <p class="text-sm font-bold mb-3">Profil karyawan Anda belum terhubung atau belum dilakukan kalkulasi KPI untuk bulan {{ $filterMonth }}.</p>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-arrows-clockwise"
                wire:click="recalculateAllKpi()"
            >
                Kalkulasi Realtime Sekarang
            </x-pos.utility.button>
        </div>
    @endif
</div>
