<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Manajemen Komisi Sales"
            backLabel="Dashboard"
            backUrl="/pos/dashboard"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Header Actions & Period Filter Bar -->
                <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-3xl p-6 shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/70 dark:border-slate-700/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-blue-950/50 text-primary dark:text-blue-400 flex items-center justify-center font-black text-xl">
                                <i class="ph-bold ph-percent"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Manajemen Komisi Sales</h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Kelola skema komisi, hitung perolehan sales, dan setujui rekap komisi bulanan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Bulan -->
                        <div>
                            <input
                                type="month"
                                wire:model.live="filterMonth"
                                class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            />
                        </div>

                        <!-- Filter Cabang -->
                        <div>
                            <select
                                wire:model.live="filterBranchId"
                                class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            >
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
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

                        <!-- Button Tambah Skema -->
                        <button
                            type="button"
                            wire:click="openSchemeModal()"
                            class="px-4 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm hover:shadow transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            <span>Tambah Skema Komisi</span>
                        </button>
                    </div>
                </div>

                <!-- KPI Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Penjualan Staf -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
                            <i class="ph-bold ph-chart-line-up"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Penjualan Staf</div>
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100 font-mono mt-0.5">
                                Rp {{ number_format($totalSalesPeriod, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Total Komisi Dihasilkan -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
                            <i class="ph-bold ph-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Total Komisi Periode Ini</div>
                            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                                Rp {{ number_format($totalCommissionPeriod, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Komisi Disetujui (Approved) -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
                            <i class="ph-bold ph-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Komisi Approved</div>
                            <div class="text-lg font-black text-purple-600 dark:text-purple-400 font-mono mt-0.5">
                                Rp {{ number_format($totalApprovedPeriod, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Komisi Menunggu (Pending) -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/70 dark:border-slate-700/80 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-2xl flex-shrink-0">
                            <i class="ph-bold ph-clock"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Menunggu Approval</div>
                            <div class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5">
                                Rp {{ number_format($totalPendingPeriod, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2">
                    <button
                        type="button"
                        wire:click="$set('activeTab', 'recap')"
                        class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'recap' ? 'bg-primary text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800' }}"
                    >
                        <i class="ph-bold ph-users-three text-base"></i>
                        <span>1. Rekap Komisi Sales</span>
                    </button>

                    <button
                        type="button"
                        wire:click="$set('activeTab', 'schemes')"
                        class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'schemes' ? 'bg-primary text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800' }}"
                    >
                        <i class="ph-bold ph-sliders text-base"></i>
                        <span>2. Skema & Aturan Komisi</span>
                    </button>

                    <button
                        type="button"
                        wire:click="$set('activeTab', 'logs')"
                        class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'logs' ? 'bg-primary text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800' }}"
                    >
                        <i class="ph-bold ph-list-bullets text-base"></i>
                        <span>3. Log Transaksi Komisi</span>
                    </button>
                </div>

                <!-- TAB 1: REKAP KOMISI SALES PER KARYAWAN -->
                @if ($activeTab === 'recap')
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/70 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="ph-bold ph-user-check text-primary"></i>
                                Rekapitulasi Komisi Sales Staf — Periode {{ \Carbon\Carbon::parse($filterMonth . '-01')->translatedFormat('F Y') }}
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4">Karyawan / Sales</th>
                                        <th class="p-4">Cabang</th>
                                        <th class="p-4 text-right">Total Penjualan</th>
                                        <th class="p-4 text-right">Total Komisi</th>
                                        <th class="p-4 text-right">Disetujui (Approved)</th>
                                        <th class="p-4 text-right">Menunggu Approval</th>
                                        <th class="p-4 text-center">Aksi Approval</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                    @forelse ($recapData as $row)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                            <td class="p-4">
                                                <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $row['employee']->name }}</div>
                                                <div class="text-[10px] font-mono text-slate-400">NIK: {{ $row['employee']->nik }}</div>
                                            </td>
                                            <td class="p-4 text-slate-600 dark:text-slate-300">
                                                {{ $row['employee']->branch?->name ?: 'Cabang Utama' }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-bold text-slate-800 dark:text-slate-100">
                                                Rp {{ number_format($row['sales_total'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($row['commission_total'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-bold text-purple-600 dark:text-purple-400">
                                                Rp {{ number_format($row['approved_total'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-bold text-amber-600 dark:text-amber-400">
                                                Rp {{ number_format($row['pending_total'], 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-center">
                                                @if ($row['pending_count'] > 0)
                                                    <button
                                                        type="button"
                                                        wire:click="approveEmployeeRecap({{ $row['employee']->id }})"
                                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] rounded-lg shadow-sm transition cursor-pointer"
                                                        title="Setujui {{ $row['pending_count'] }} komisi pending"
                                                    >
                                                        Approve Komisi ({{ $row['pending_count'] }})
                                                    </button>
                                                @else
                                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[10px] font-bold rounded-lg">
                                                        Semua Approved
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-8 text-center text-slate-400">
                                                Belum ada data staf karyawan pada cabang ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- TAB 2: SKEMA & ATURAN KOMISI -->
                @if ($activeTab === 'schemes')
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/70 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="ph-bold ph-sliders text-primary"></i>
                                Daftar Skema & Aturan Komisi Aktif
                            </h3>
                            <button
                                type="button"
                                wire:click="openSchemeModal()"
                                class="px-3.5 py-1.5 bg-primary hover:bg-primaryDark text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center gap-1.5"
                            >
                                <i class="ph-bold ph-plus"></i>
                                <span>Tambah Skema Baru</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4">Nama Skema</th>
                                        <th class="p-4">Cabang</th>
                                        <th class="p-4">Target Karyawan / Sales</th>
                                        <th class="p-4">Tipe Kalkulasi</th>
                                        <th class="p-4">Tarif / Rate</th>
                                        <th class="p-4">Target Penerapan</th>
                                        <th class="p-4 text-center">Status</th>
                                        <th class="p-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                    @forelse ($schemes as $sch)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                            <td class="p-4">
                                                <div class="font-extrabold text-slate-900 dark:text-slate-100">{{ $sch->name }}</div>
                                            </td>
                                            <td class="p-4 text-slate-600 dark:text-slate-300">
                                                {{ $sch->branch?->name ?: 'Semua Cabang' }}
                                            </td>
                                            <td class="p-4">
                                                @if ($sch->employees->count() > 0)
                                                    <div class="flex flex-wrap items-center gap-1">
                                                        @foreach ($sch->employees as $schEmp)
                                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-md text-[11px] font-extrabold">
                                                                {{ $schEmp->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @elseif ($sch->employee)
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-md text-[11px] font-extrabold">
                                                        {{ $sch->employee->name }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-md text-[11px] font-bold">
                                                        Semua Karyawan (Umum)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-4">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $sch->calculation_type === 'percentage' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                                    {{ $sch->calculation_type === 'percentage' ? 'Persentase (%)' : 'Flat Nominal (Rp)' }}
                                                </span>
                                            </td>
                                            <td class="p-4 font-mono font-bold text-slate-900 dark:text-slate-100">
                                                @if ($sch->calculation_type === 'percentage')
                                                    {{ $sch->rate }}%
                                                @else
                                                    Rp {{ number_format($sch->rate, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="p-4 text-slate-600 dark:text-slate-300 capitalize">
                                                @if ($sch->applies_to === 'product')
                                                    <div>Produk: {{ $sch->targetProduct?->name ?: '-' }}</div>
                                                @elseif ($sch->applies_to === 'category')
                                                    <div>Kategori: {{ $sch->targetCategory?->name ?: '-' }}</div>
                                                @else
                                                    <div>Semua Transaksi Sales</div>
                                                @endif
                                            </td>
                                            <td class="p-4 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="toggleSchemeStatus({{ $sch->id }})"
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase cursor-pointer transition {{ $sch->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}"
                                                >
                                                    {{ $sch->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                </button>
                                            </td>
                                            <td class="p-4 text-center space-x-2">
                                                <button
                                                    type="button"
                                                    wire:click="openSchemeModal({{ $sch->id }})"
                                                    class="p-1.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 transition cursor-pointer"
                                                    title="Edit Skema"
                                                >
                                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="deleteScheme({{ $sch->id }})"
                                                    wire:confirm="Yakin ingin menghapus skema komisi ini?"
                                                    class="p-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-100 transition cursor-pointer"
                                                    title="Hapus Skema"
                                                >
                                                    <i class="ph-bold ph-trash text-sm"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-8 text-center text-slate-400">
                                                Belum ada skema komisi yang dikonfigurasi. Klik "Tambah Skema Baru" untuk membuat aturan komisi.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- TAB 3: LOG TRANSAKSI KOMISI -->
                @if ($activeTab === 'logs')
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/70 dark:border-slate-700/80 shadow-sm overflow-hidden transition-colors">
                        <div class="p-5 border-b border-slate-100 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="ph-bold ph-list-bullets text-primary"></i>
                                Log Transaksi Komisi Per Penjualan
                            </h3>

                            <div class="flex items-center gap-3">
                                <select
                                    wire:model.live="selectedEmployeeId"
                                    class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none"
                                >
                                    <option value="">Semua Staf Karyawan</option>
                                    @foreach ($employees as $e)
                                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->nik }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/40 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                        <th class="p-4">Tanggal</th>
                                        <th class="p-4">Karyawan</th>
                                        <th class="p-4">Transaksi ID</th>
                                        <th class="p-4 text-right">Nilai Penjualan</th>
                                        <th class="p-4 text-right">Komisi Earned</th>
                                        <th class="p-4 text-center">Status</th>
                                        <th class="p-4">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                                    @forelse ($logs as $log)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                            <td class="p-4 font-mono text-slate-600 dark:text-slate-300">
                                                {{ $log->date->format('d/m/Y') }}
                                            </td>
                                            <td class="p-4 font-bold text-slate-900 dark:text-slate-100">
                                                {{ $log->employee?->name ?: '-' }}
                                            </td>
                                            <td class="p-4 font-mono text-slate-600 dark:text-slate-300">
                                                #{{ $log->sale_id ?: '-' }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-bold text-slate-800 dark:text-slate-100">
                                                Rp {{ number_format($log->sale_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($log->commission_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="p-4 text-center">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $log->status === 'approved' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : ($log->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300') }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                            <td class="p-4 text-slate-500 text-[11px]">
                                                {{ $log->notes ?: '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-8 text-center text-slate-400">
                                                Belum ada log transaksi komisi untuk periode filter ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($logs->hasPages())
                            <div class="p-4 border-t border-slate-100 dark:border-slate-700">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </main>

    <!-- Modal Form Skema Komisi -->
    <x-pos.modal
        wire:model="showSchemeModal"
        title="{{ $editingSchemeId ? 'Edit Skema Komisi' : 'Tambah Skema Komisi Baru' }}"
        subtitle="Atur kriteria kalkulasi komisi sales untuk cabang dan produk/kategori"
        icon="ph-percent"
        maxWidth="md"
    >
        <form wire:submit.prevent="saveScheme" class="space-y-4">
            <!-- Nama Skema -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Nama Skema Komisi <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    wire:model="schemeName"
                    required
                    placeholder="Contoh: Komisi Sales Senior 3.5%"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                />
            </div>

            <!-- Target Staf Karyawan Spesifik (Opsional - Multiple Choice) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Target Staf Sales / Karyawan Spesifik (Pilihan Ganda / Multiple Choice)
                </label>
                <div class="max-h-36 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg space-y-1.5 no-scrollbar">
                    @php
                        $availableCount = 0;
                    @endphp
                    @foreach ($employees as $emp)
                        @php
                            $isUnavailable = in_array($emp->id, $unavailableEmployeeIds);
                        @endphp
                        @if (!$isUnavailable)
                            @php $availableCount++; @endphp
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer p-1.5 rounded hover:bg-slate-200/50 dark:hover:bg-slate-800 transition">
                                <input
                                    type="checkbox"
                                    value="{{ $emp->id }}"
                                    wire:model="targetEmployeeIds"
                                    class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4"
                                />
                                <span>{{ $emp->name }}</span>
                                <span class="text-[10px] font-mono text-slate-400">({{ $emp->nik }})</span>
                            </label>
                        @endif
                    @endforeach

                    @if ($availableCount === 0)
                        <div class="text-[11px] text-slate-400 p-2 text-center">
                            Semua karyawan sudah terdaftar pada skema komisi lain (atau belum ada data karyawan).
                        </div>
                    @endif
                </div>
                <p class="text-[10px] text-slate-400 mt-1">* Karyawan yang sudah terdaftar pada skema komisi khusus lain secara otomatis disembunyikan dari pilihan ini.</p>
            </div>

            <!-- Tipe Kalkulasi & Rate -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Tipe Kalkulasi <span class="text-rose-500">*</span>
                    </label>
                    <select
                        wire:model.live="calculationType"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                    >
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed_amount">Nominal Flat (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Nilai / Rate <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        wire:model="rate"
                        required
                        placeholder="{{ $calculationType === 'percentage' ? '2.0' : '25000' }}"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                    />
                </div>
            </div>

            <!-- Target Penerapan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Target Penerapan <span class="text-rose-500">*</span>
                </label>
                <select
                    wire:model.live="appliesTo"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                >
                    <option value="all_sales">Semua Transaksi Sales</option>
                    <option value="category">Spesifik Kategori Penjualan</option>
                    <option value="product">Spesifik Produk Tertentu</option>
                </select>
            </div>

            @if ($appliesTo === 'category')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Pilih Kategori Penjualan <span class="text-rose-500">*</span>
                    </label>
                    <select
                        wire:model="targetSaleCategoryId"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                    >
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($appliesTo === 'product')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Pilih Produk Spesifik <span class="text-rose-500">*</span>
                    </label>
                    <select
                        wire:model="targetProductId"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                    >
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Minimal Target Omset Bulanan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Target Minimal Omset Bulanan (Opsional)
                </label>
                <input
                    type="number"
                    step="1000"
                    wire:model="minMonthlySalesTarget"
                    placeholder="0"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                />
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-2 pt-1">
                <input
                    type="checkbox"
                    id="isActiveCheck"
                    wire:model="isActive"
                    class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary h-4 w-4"
                />
                <label for="isActiveCheck" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                    Aktifkan Skema Komisi Ini
                </label>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showSchemeModal', false)"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition cursor-pointer"
                >
                    Simpan Skema
                </button>
            </div>
        </form>
    </x-pos.modal>
</div>
