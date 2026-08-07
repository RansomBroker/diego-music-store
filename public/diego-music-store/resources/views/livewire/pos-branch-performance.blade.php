<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <x-pos.navbar
            pageTitle="Manajemen & Performa Cabang"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 no-scrollbar">

            <!-- Filter Header & Branch Selector Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <i class="ph-bold ph-buildings text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                                {{ $currentBranch?->store_name ?: ($currentBranch?->name ?: 'Konsolidasi Seluruh Cabang') }}
                            </h2>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                                Mengelola stok, pelanggan, dan Laba Rugi per cabang dalam satu entitas bisnis
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Date Range & Branch Filters -->
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <!-- Preset Date Buttons -->
                    <div class="flex items-center bg-slate-100 dark:bg-slate-700/50 p-1 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <button type="button" wire:click="setQuickDateRange('today')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->format('Y-m-d') && $dateTo === now()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                            Hari Ini
                        </button>
                        <button type="button" wire:click="setQuickDateRange('this_month')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->startOfMonth()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                            Bulan Ini
                        </button>
                        <button type="button" wire:click="setQuickDateRange('this_year')" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition-all {{ $dateFrom === now()->startOfYear()->format('Y-m-d') ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}">
                            Tahun Ini
                        </button>
                    </div>

                    <!-- Filter Dropdown Cabang -->
                    <select wire:model.live="selectedBranchId" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="all">🌐 Semua Cabang (Konsolidasi Entitas Bisnis)</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city ?: $b->store_name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Executive KPI Cards Grid (Laba Rugi, Stok, Pelanggan, Omset) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- 1. Omset Penjualan -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-blue-500/50 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Omset Penjualan</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i class="ph-bold ph-trend-up text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
                            {{ number_format($totalSalesCount) }} transaksi berhasil
                        </p>
                    </div>
                </div>

                <!-- 2. Nilai Persediaan Stok Cabang -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Persediaan</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i class="ph-bold ph-package text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Rp {{ number_format($totalStockValue, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                            {{ number_format($totalStockItems) }} unit barang tersedia
                        </p>
                    </div>
                </div>

                <!-- 3. Pelanggan & Piutang AR -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-purple-500/50 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Pelanggan & Piutang</span>
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i class="ph-bold ph-users text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Rp {{ number_format($totalArUnpaid, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 mt-1">
                            {{ number_format($totalCustomers) }} pelanggan terdaftar
                        </p>
                    </div>
                </div>

                <!-- 4. Estimasi Laba Bersih Operasional -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm relative overflow-hidden group hover:border-amber-500/50 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Laba Bersih Cabang</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i class="ph-bold ph-scales text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black {{ $netOperatingIncome >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                            Rp {{ number_format($netOperatingIncome, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">
                            Laba Kotor: Rp {{ number_format($grossProfit, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Breakdown Laba Rugi Multi-Step Cabang -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm">
                <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2 mb-4">
                    <i class="ph-bold ph-receipt text-blue-500"></i>
                    Rincian Laba Rugi Multi-Step Cabang Aktif
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">1. Pendapatan Penjualan</div>
                        <div class="text-lg font-black text-slate-900 dark:text-slate-100 mt-1">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">2. Harga Pokok Penjualan (HPP)</div>
                        <div class="text-lg font-black text-rose-600 dark:text-rose-400 mt-1">
                            (Rp {{ number_format($totalCogs, 0, ',', '.') }})
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">3. Beban Operasional Kas Keluar</div>
                        <div class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1">
                            (Rp {{ number_format($totalOperationalExpenses, 0, ',', '.') }})
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Perbandingan Performa Seluruh Cabang (Entitas Bisnis Konsolidasi) -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="ph-bold ph-chart-bar text-blue-500"></i>
                            Tabel Perbandingan Performa Seluruh Cabang
                        </h3>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                            Konsolidasi entitas bisnis: Omset, Nilai Stok, Piutang, dan Laba Bersih antar cabang
                        </p>
                    </div>

                    <a href="{{ route('pos.reports.sales') }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-2xl text-xs font-extrabold transition-all flex items-center gap-2">
                        <span>Lihat Laporan ERP</span>
                        <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-700 text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Nama Cabang / Store</th>
                                <th class="py-3 px-4 text-right">Omset Penjualan</th>
                                <th class="py-3 px-4 text-right">HPP (COGS)</th>
                                <th class="py-3 px-4 text-right">Laba Kotor</th>
                                <th class="py-3 px-4 text-right">Nilai Stok</th>
                                <th class="py-3 px-4 text-right">Piutang AR</th>
                                <th class="py-3 px-4 text-right">Laba Bersih</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                            @foreach ($branchComparison as $bc)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors {{ $bc['id'] == $selectedBranchId ? 'bg-blue-50/40 dark:bg-blue-950/20' : '' }}">
                                    <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                                        <div>{{ $bc['name'] }}</div>
                                        <div class="text-[10px] font-normal text-slate-400 dark:text-slate-500">{{ $bc['store_name'] }} • {{ $bc['city'] }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($bc['revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                                        Rp {{ number_format($bc['cogs'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                        Rp {{ number_format($bc['gross_profit'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($bc['stock_value'], 0, ',', '.') }}
                                        <div class="text-[10px] text-slate-400 font-normal">({{ number_format($bc['stock_items']) }} item)</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-purple-600 dark:text-purple-400">
                                        Rp {{ number_format($bc['ar_unpaid'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black {{ $bc['net_income'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        Rp {{ number_format($bc['net_income'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="{{ route('pos.switch-branch', $bc['id']) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 text-slate-700 hover:text-white dark:bg-slate-700 dark:hover:bg-blue-600 dark:text-slate-200 rounded-xl text-[11px] font-bold transition-all inline-block">
                                            Pilih Cabang
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>
