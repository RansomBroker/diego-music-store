<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Potongan & Denda Presensi"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Top Action & Filter Header -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/70 dark:border-slate-700/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-xl">
                                <i class="ph-bold ph-warning-circle"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Potongan & Denda Presensi</h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Aturan keterlambatan, pulang cepat, log denda presensi & sinkronisasi ke Payroll</p>
                            </div>
                        </div>
                    </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
            <!-- Filter Cabang -->
            <div class="w-full sm:w-44">
                <select
                    wire:model.live="filterBranchId"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none"
                >
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Bulan/Periode -->
            <div class="w-full sm:w-36">
                <input
                    type="month"
                    wire:model.live="filterMonth"
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none"
                />
            </div>

            <!-- Button Export CSV -->
            <button
                type="button"
                wire:click="exportCsv()"
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 cursor-pointer"
            >
                <i class="ph-bold ph-download-simple text-base"></i>
                <span>Export Rekap CSV</span>
            </button>

            <!-- Button Tambah Aturan -->
            <button
                type="button"
                wire:click="openRuleModal()"
                class="px-4 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 cursor-pointer"
            >
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>Tambah Aturan Denda</span>
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Keterlambatan -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                <i class="ph-bold ph-clock"></i>
            </div>
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Keterlambatan</div>
                <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                    {{ number_format($totalLateMinutesPeriod) }} <span class="text-xs font-normal text-slate-400">Menit</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Pulang Cepat -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                <i class="ph-bold ph-sign-out"></i>
            </div>
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pulang Cepat</div>
                <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                    {{ number_format($totalEarlyMinutesPeriod) }} <span class="text-xs font-normal text-slate-400">Menit</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Denda Presensi -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                <i class="ph-bold ph-currency-dollar"></i>
            </div>
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Nominal Denda</div>
                <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($totalDeductionPeriod, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Card 4: Denda Disetujui (Payroll Ready) -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <div>
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Disetujui ke Payroll</div>
                <div class="text-lg font-mono font-black text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($totalApprovedDeductionPeriod, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm overflow-hidden">
        <div class="flex border-b border-slate-200 dark:border-slate-700/80 overflow-x-auto no-scrollbar">
            <button
                type="button"
                wire:click="$set('activeTab', 'recap')"
                class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'recap' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
            >
                <i class="ph-bold ph-chart-pie-slice text-base"></i>
                <span>1. Rekap Potongan Presensi</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'rules')"
                class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'rules' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
            >
                <i class="ph-bold ph-gear text-base"></i>
                <span>2. Aturan & Denda Pelanggaran</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'logs')"
                class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'logs' ? 'border-primary text-primary dark:text-blue-400 bg-primary/5' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
            >
                <i class="ph-bold ph-list-numbers text-base"></i>
                <span>3. Log Detail Pelanggaran</span>
            </button>
        </div>

        <div class="p-6">
            <!-- TAB 1: REKAP POTONGAN PRESENSI PER KARYAWAN -->
            @if ($activeTab === 'recap')
                <div class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                    <th class="p-4">Staf Karyawan</th>
                                    <th class="p-4">Terlambat (Menit)</th>
                                    <th class="p-4">Pulang Cepat (Menit)</th>
                                    <th class="p-4">Total Denda (Rp)</th>
                                    <th class="p-4">Disetujui Payroll (Rp)</th>
                                    <th class="p-4 text-center">Status Waiver / Payroll</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                @forelse ($recapData as $row)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                        <td class="p-4">
                                            <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $row['employee']->name }}</div>
                                            <div class="text-[10px] font-mono text-slate-400">NIK: {{ $row['employee']->nik }} &bull; {{ $row['employee']->branch?->name ?: '-' }}</div>
                                        </td>
                                        <td class="p-4 font-mono font-bold text-amber-600 dark:text-amber-400">
                                            {{ number_format($row['late_minutes']) }}m
                                        </td>
                                        <td class="p-4 font-mono font-bold text-purple-600 dark:text-purple-400">
                                            {{ number_format($row['early_minutes']) }}m
                                        </td>
                                        <td class="p-4 font-mono font-black text-rose-600 dark:text-rose-400">
                                            Rp {{ number_format($row['total_deduction'], 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 font-mono font-black text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($row['approved_deduction'], 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 text-center">
                                            @if ($row['approved_deduction'] > 0)
                                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-full text-[10px] font-black uppercase">
                                                    Siap ke Slip Gaji
                                                </span>
                                            @elseif ($row['pending_count'] > 0)
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 rounded-full text-[10px] font-black uppercase">
                                                    {{ $row['pending_count'] }} Menunggu Approval
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400 rounded-full text-[10px] font-bold">
                                                    Tidak Ada Potongan
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">
                                            Belum ada data presensi & denda pada periode bulan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 2: ATURAN & DENDA PELANGGARAN -->
            @if ($activeTab === 'rules')
                <div class="space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                    <th class="p-4">Nama Aturan</th>
                                    <th class="p-4">Jenis Pelanggaran</th>
                                    <th class="p-4">Batas Menit</th>
                                    <th class="p-4">Tipe Denda / Potongan</th>
                                    <th class="p-4">Nominal Denda</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                @forelse ($rules as $r)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                        <td class="p-4">
                                            <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $r->name }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $r->violation_type === 'late_in' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                                {{ $r->violation_type === 'late_in' ? 'Keterlambatan (Late In)' : ($r->violation_type === 'early_out' ? 'Pulang Cepat (Early Out)' : $r->violation_type) }}
                                            </span>
                                        </td>
                                        <td class="p-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $r->min_minutes }}m - {{ $r->max_minutes ? $r->max_minutes . 'm' : 'Tanpa Batas' }}
                                        </td>
                                        <td class="p-4 text-slate-600 dark:text-slate-300 capitalize">
                                            {{ str_replace('_', ' ', $r->deduction_type) }}
                                        </td>
                                        <td class="p-4 font-mono font-black text-slate-900 dark:text-slate-100">
                                            Rp {{ number_format($r->deduction_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <button
                                                type="button"
                                                wire:click="toggleRuleStatus({{ $r->id }})"
                                                class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase cursor-pointer transition {{ $r->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}"
                                            >
                                                {{ $r->is_active ? 'Aktif' : 'Non-Aktif' }}
                                            </button>
                                        </td>
                                        <td class="p-4 text-center space-x-2">
                                            <button
                                                type="button"
                                                wire:click="openRuleModal({{ $r->id }})"
                                                class="p-1.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 transition cursor-pointer"
                                                title="Edit Aturan"
                                            >
                                                <i class="ph-bold ph-pencil-simple text-sm"></i>
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="deleteRule({{ $r->id }})"
                                                wire:confirm="Yakin ingin menghapus aturan denda ini?"
                                                class="p-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-100 transition cursor-pointer"
                                                title="Hapus Aturan"
                                            >
                                                <i class="ph-bold ph-trash text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">
                                            Belum ada aturan denda presensi terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 3: LOG DETAIL PELANGGARAN -->
            @if ($activeTab === 'logs')
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700/60">
                        <div class="w-full sm:w-64">
                            <select
                                wire:model.live="selectedEmployeeId"
                                class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-800 dark:text-slate-200 outline-none"
                            >
                                <option value="">Semua Staf Karyawan</option>
                                @foreach ($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }} (NIK: {{ $e->nik }})</option>
                                @endforeach
                            </select>
                        </div>

                        @if (count($selectedLogIds) > 0)
                            <button
                                type="button"
                                wire:click="bulkApproveLogs()"
                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow transition flex items-center gap-2 cursor-pointer"
                            >
                                <i class="ph-bold ph-check-square text-base"></i>
                                <span>Setujui {{ count($selectedLogIds) }} Log Terpilih ke Payroll</span>
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                    <th class="p-4 w-10 text-center">
                                        <input type="checkbox" disabled class="rounded border-slate-300" />
                                    </th>
                                    <th class="p-4">Tanggal & Karyawan</th>
                                    <th class="p-4">Jenis Pelanggaran</th>
                                    <th class="p-4">Durasi Menit</th>
                                    <th class="p-4">Nominal Denda (Rp)</th>
                                    <th class="p-4">Catatan</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Aksi Approval</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                @forelse ($logs as $l)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                        <td class="p-4 text-center">
                                            <input
                                                type="checkbox"
                                                value="{{ $l->id }}"
                                                wire:model.live="selectedLogIds"
                                                class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4"
                                            />
                                        </td>
                                        <td class="p-4">
                                            <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $l->employee?->name }}</div>
                                            <div class="text-[10px] font-mono text-slate-400">{{ $l->date->format('d M Y') }}</div>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $l->violation_type === 'late_in' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                                {{ $l->violation_type === 'late_in' ? 'Terlambat Masuk' : 'Pulang Cepat' }}
                                            </span>
                                        </td>
                                        <td class="p-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                            {{ $l->late_early_minutes }} Menit
                                        </td>
                                        <td class="p-4 font-mono font-black text-rose-600 dark:text-rose-400">
                                            Rp {{ number_format($l->deduction_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="p-4 text-slate-500 max-w-xs truncate">
                                            {{ $l->notes ?: '-' }}
                                        </td>
                                        <td class="p-4 text-center">
                                            @if ($l->status === 'approved')
                                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-full text-[10px] font-black uppercase">
                                                    Disetujui
                                                </span>
                                            @elseif ($l->status === 'waived')
                                                <span class="px-2.5 py-1 bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 rounded-full text-[10px] font-black uppercase">
                                                    Waived (Dihapus)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 rounded-full text-[10px] font-black uppercase">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center space-x-1">
                                            @if ($l->status === 'pending')
                                                <button
                                                    type="button"
                                                    wire:click="approveLog({{ $l->id }})"
                                                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[10px] rounded-lg transition cursor-pointer"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="waiveLog({{ $l->id }})"
                                                    class="px-2.5 py-1 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-[10px] rounded-lg transition cursor-pointer"
                                                >
                                                    Waive
                                                </button>
                                            @else
                                                <span class="text-[10px] text-slate-400 font-mono">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-8 text-center text-slate-400">
                                            Belum ada log pelanggaran presensi pada periode bulan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
            </div>
        </div>
    </main>

    <!-- MODAL FORM ATURAN DENDA PELANGGARAN -->
    @if ($showRuleModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100">
                        {{ $editingRuleId ? 'Edit Aturan Denda Presensi' : 'Tambah Aturan Denda Presensi Baru' }}
                    </h3>
                    <button type="button" wire:click="$set('showRuleModal', false)" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>

                <form wire:submit="saveRule" class="space-y-4">
                    <!-- Nama Aturan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Aturan Pelanggaran <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="ruleName"
                            required
                            placeholder="Contoh: Terlambat Masuk 1-15 Menit"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                        />
                    </div>

                    <!-- Jenis Pelanggaran -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Jenis Pelanggaran <span class="text-rose-500">*</span>
                        </label>
                        <select
                            wire:model="violationType"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                        >
                            <option value="late_in">Keterlambatan (Late In)</option>
                            <option value="early_out">Pulang Cepat (Early Out)</option>
                            <option value="unexcused_absence">Mangkir / Absen Tanpa Keterangan</option>
                            <option value="leave_over_quota">Izin / Off Day Melampaui Kuota</option>
                        </select>
                    </div>

                    <!-- Batas Menit (Min - Max) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Min. Menit Terlambat <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                wire:model="minMinutes"
                                required
                                min="0"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Max. Menit (Opsional)
                            </label>
                            <input
                                type="number"
                                wire:model="maxMinutes"
                                placeholder="Kosongkan jika tanpa batas"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                    </div>

                    <!-- Tipe Denda & Nominal -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tipe Denda / Potongan <span class="text-rose-500">*</span>
                            </label>
                            <select
                                wire:model="deductionType"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            >
                                <option value="fixed_amount">Nominal Flat (Rp)</option>
                                <option value="percentage_per_minute">Nominal Per Menit (Rp/menit)</option>
                                <option value="percentage_daily_salary">Persentase Gaji Harian (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nominal Denda (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="deductionAmount"
                                required
                                placeholder="10000"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                            />
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            wire:model="isActive"
                            id="isActiveRule"
                            class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4"
                        />
                        <label for="isActiveRule" class="text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                            Aktifkan Aturan Denda Ini
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="$set('showRuleModal', false)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                        >
                            Simpan Aturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
