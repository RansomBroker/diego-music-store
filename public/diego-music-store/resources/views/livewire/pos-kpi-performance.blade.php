<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Performance KPI & Insentif Karyawan"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Top Header Filter Bar -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200/70 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-2xl">
                                <i class="ph-bold ph-trophy"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Performance KPI & Bonus Insentif</h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Evaluasi Target Penjualan, ATV, Absensi, Ketepatan Waktu & Pencairan Bonus Karyawan</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                        <!-- Filter Cabang -->
                        <div class="w-full sm:w-44">
                            <select
                                wire:model.live="filterBranchId"
                                class="w-full px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            >
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Bulan -->
                        <div class="w-full sm:w-36">
                            <input
                                type="month"
                                wire:model.live="filterMonth"
                                class="w-full px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            />
                        </div>

                        <!-- Recalculate KPI -->
                        <button
                            type="button"
                            wire:click="recalculateAllKpi()"
                            class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                            title="Hitung Ulang Skor KPI & Bonus Realtime"
                        >
                            <i class="ph-bold ph-arrows-clockwise text-base"></i>
                            <span>Kalkulasi Realtime</span>
                        </button>

                        <!-- Tambah Template KPI -->
                        <button
                            type="button"
                            wire:click="openTemplateModal()"
                            class="px-4 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            <span>Template KPI Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-slate-200 dark:border-slate-800 space-x-4">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'recap')"
                        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'recap' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        <i class="ph-bold ph-list-checks mr-1.5"></i>
                        1. Rekap Evaluasi Bulanan
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'dashboard')"
                        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'dashboard' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        <i class="ph-bold ph-gauge mr-1.5"></i>
                        2. Dashboard Realtime KPI Saya
                    </button>
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'templates')"
                        class="pb-3 px-2 font-extrabold text-sm border-b-2 transition cursor-pointer {{ $activeTab === 'templates' ? 'border-primary text-primary dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        <i class="ph-bold ph-sliders-horizontal mr-1.5"></i>
                        3. Template KPI per Jabatan/User
                    </button>
                </div>

                <!-- TAB 1: REKAP EVALUASI BULANAN & PENCATATAN BONUS -->
                @if ($activeTab === 'recap')
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">
                                Daftar Pencapaian KPI & Insentif Karyawan (Periode: {{ $filterMonth }})
                            </h3>
                            <span class="text-xs font-semibold text-slate-400">Total Data: {{ $evaluations->total() }}</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-900/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-3 px-4">Nama Karyawan</th>
                                        <th class="py-3 px-4">Cabang</th>
                                        <th class="py-3 px-4">Template KPI</th>
                                        <th class="py-3 px-4 text-right">Omset Sales</th>
                                        <th class="py-3 px-4 text-right">ATV (Rata²)</th>
                                        <th class="py-3 px-4 text-center">Absensi %</th>
                                        <th class="py-3 px-4 text-center">Tepat Waktu %</th>
                                        <th class="py-3 px-4 text-center">Skor KPI</th>
                                        <th class="py-3 px-4 text-right">Bonus Cair</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @forelse ($evaluations as $eval)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                                {{ $eval->employee->name ?? '-' }}
                                                <span class="block text-[10px] text-slate-400 font-normal">{{ $eval->employee->nik ?? '' }}</span>
                                            </td>
                                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                                {{ $eval->branch->name ?? '-' }}
                                            </td>
                                            <td class="py-3 px-4 font-medium text-slate-600 dark:text-slate-400">
                                                {{ $eval->template->name ?? 'Default' }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                                Rp {{ number_format($eval->actual_sales_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                                                Rp {{ number_format($eval->actual_atv_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                                {{ $eval->actual_attendance_pct }}%
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                                {{ $eval->actual_punctuality_pct }}%
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $eval->final_kpi_score >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400' : ($eval->final_kpi_score >= 75 ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-400') }}">
                                                    {{ $eval->final_kpi_score }}%
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-black text-amber-600 dark:text-amber-400">
                                                Rp {{ number_format($eval->earned_bonus_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-400">
                                                Belum ada data evaluasi KPI untuk periode {{ $filterMonth }}. Klik tombol <strong>Kalkulasi Realtime</strong> untuk memproses.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                            {{ $evaluations->links() }}
                        </div>
                    </div>
                @endif

                <!-- TAB 2: REALTIME DASHBOARD KPI SAYA -->
                @if ($activeTab === 'dashboard')
                    <div class="space-y-6">
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
                            <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center text-slate-400 border border-slate-200 dark:border-slate-700/80">
                                <i class="ph-bold ph-warning-circle text-4xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-bold">Profil karyawan Anda belum terhubung atau belum dilakukan kalkulasi KPI untuk bulan {{ $filterMonth }}.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- TAB 3: KELOLA TEMPLATE KPI PER JABATAN / USER -->
                @if ($activeTab === 'templates')
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">
                                Master Template KPI & Penetapan Target Insentif
                            </h3>
                            <button
                                type="button"
                                wire:click="openTemplateModal()"
                                class="px-3 py-1.5 bg-primary text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                            >
                                <i class="ph-bold ph-plus-circle mr-1"></i> Buat Template KPI Baru
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-900/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-3 px-4">Nama Template</th>
                                        <th class="py-3 px-4">Target Per Jabatan/User</th>
                                        <th class="py-3 px-4 text-right">Target Sales (Rp)</th>
                                        <th class="py-3 px-4 text-right">Target ATV (Rp)</th>
                                        <th class="py-3 px-4 text-center">Target Absensi %</th>
                                        <th class="py-3 px-4 text-center">Target Tepat Waktu %</th>
                                        <th class="py-3 px-4 text-right">Max Bonus (Rp)</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @forelse ($templates as $tpl)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                                {{ $tpl->name }}
                                            </td>
                                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                                @if ($tpl->employee)
                                                    <span class="bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold text-[11px]">
                                                        Khusus: {{ $tpl->employee->name }}
                                                    </span>
                                                @else
                                                    <span class="bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold text-[11px]">
                                                        Jabatan: {{ $tpl->position ?: 'Semua Jabatan' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold text-slate-800 dark:text-slate-200">
                                                Rp {{ number_format($tpl->target_sales_amount, 0, ',', '.') }}
                                                <span class="block text-[10px] text-slate-400">Bobot: {{ $tpl->weight_sales }}%</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold text-slate-800 dark:text-slate-200">
                                                Rp {{ number_format($tpl->target_atv_amount, 0, ',', '.') }}
                                                <span class="block text-[10px] text-slate-400">Bobot: {{ $tpl->weight_atv }}%</span>
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                                {{ $tpl->target_attendance_pct }}%
                                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_attendance }}%</span>
                                            </td>
                                            <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                                {{ $tpl->target_punctuality_pct }}%
                                                <span class="block text-[10px] text-slate-400 font-normal">Bobot: {{ $tpl->weight_punctuality }}%</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-black text-amber-600 dark:text-amber-400">
                                                Rp {{ number_format($tpl->max_bonus_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if ($tpl->is_active)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400">Aktif</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="openTemplateModal({{ $tpl->id }})"
                                                    class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-[11px] rounded-lg transition cursor-pointer"
                                                >
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-400">
                                                Belum ada data template KPI. Klik <strong>Buat Template KPI Baru</strong> untuk menambahkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </main>

    <!-- MODAL FORM TEMPLATE KPI -->
    @if ($showTemplateModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto no-scrollbar">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100">
                        {{ $editingTemplateId ? 'Edit Template KPI & Bonus' : 'Tambah Template KPI Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showTemplateModal', false)" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>

                <form wire:submit="saveTemplate" class="space-y-4">
                    <!-- Nama Template & Jabatan Target -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Template KPI <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="templateName"
                                required
                                placeholder="Contoh: KPI Sales Executive 2026"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Peruntukan Jabatan (Position)
                            </label>
                            <input
                                type="text"
                                wire:model="templatePosition"
                                placeholder="Contoh: Sales Executive / Cashier / Manager"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                    </div>

                    <!-- Target Karyawan Spesifik (Optional Override) & Max Bonus -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Karyawan Spesifik (Opsional Override)
                            </label>
                            <select
                                wire:model="templateEmployeeId"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            >
                                <option value="">Semua Karyawan Jabatan Ini</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nik ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Maksimum Nominal Bonus Insentif (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="1000"
                                wire:model="maxBonusAmount"
                                required
                                placeholder="500000"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                    </div>

                    <!-- 4 Indikator & Bobot -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            Pengaturan 4 Indikator Utama & Bobot Persentase (%)
                        </h4>

                        <!-- 1. Target Sales -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target Omset Penjualan (Rp)</label>
                                <input type="number" wire:model="targetSalesAmount" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Bobot Sales (%)</label>
                                <input type="number" step="0.1" wire:model="weightSales" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                        </div>

                        <!-- 2. Target ATV -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target ATV / Rata² Transaksi (Rp)</label>
                                <input type="number" wire:model="targetAtvAmount" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Bobot ATV (%)</label>
                                <input type="number" step="0.1" wire:model="weightAtv" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                        </div>

                        <!-- 3. Target Absensi -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target Kehadiran Absensi (%)</label>
                                <input type="number" step="0.1" wire:model="targetAttendancePct" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Bobot Absensi (%)</label>
                                <input type="number" step="0.1" wire:model="weightAttendance" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                        </div>

                        <!-- 4. Target Ketepatan Waktu -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target Ketepatan Waktu (%)</label>
                                <input type="number" step="0.1" wire:model="targetPunctualityPct" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Bobot Ketepatan Waktu (%)</label>
                                <input type="number" step="0.1" wire:model="weightPunctuality" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold" />
                            </div>
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            wire:model="isActiveTemplate"
                            id="isActiveTemplate"
                            class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4"
                        />
                        <label for="isActiveTemplate" class="text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                            Aktifkan Template KPI Ini
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="$set('showTemplateModal', false)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer"
                        >
                            Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
