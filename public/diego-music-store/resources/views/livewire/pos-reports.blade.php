<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Pusat Laporan & Analisis Keuangan ERP"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Content -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Header & Navigation Bar -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Laporan ERP</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pusat Laporan ERP & Akuntansi</h1>
                    </div>

                    <!-- Print / Export Action Button -->
                    <div class="flex items-center gap-2">
                        <button
                            onclick="window.print()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow transition cursor-pointer"
                        >
                            <i class="ph-bold ph-printer text-base text-blue-400"></i>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>
                </div>

                <!-- 5 Tab Executive Navigation Bar -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1.5 shadow-sm overflow-x-auto no-scrollbar">
                    <div class="flex items-center min-w-max gap-1">
                        <button
                            wire:click="setTab('sales')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'sales' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <i class="ph-bold ph-chart-line-up text-base"></i>
                            <span>1. Laporan Penjualan</span>
                        </button>

                        <button
                            wire:click="setTab('ar-aging')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'ar-aging' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <i class="ph-bold ph-credit-card text-base"></i>
                            <span>2. Laporan Piutang</span>
                        </button>

                        <button
                            wire:click="setTab('ar-settlement')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'ar-settlement' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <i class="ph-bold ph-hand-coins text-base"></i>
                            <span>3. Pelunasan Piutang</span>
                        </button>

                        <button
                            wire:click="setTab('daily-cash')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'daily-cash' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <i class="ph-bold ph-wallet text-base"></i>
                            <span>4. Kas Harian</span>
                        </button>

                        <button
                            wire:click="setTab('stock-prices')"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer {{ $activeTab === 'stock-prices' ? 'bg-primary text-white shadow-md shadow-blue-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <i class="ph-bold ph-package text-base"></i>
                            <span>5. Stok & Harga</span>
                        </button>
                    </div>
                </div>

                <!-- Common Filter Toolbar Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        
                        <!-- Date Range & Branch Filters -->
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($activeTab !== 'ar-aging' && $activeTab !== 'stock-prices')
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-500">Dari:</span>
                                    <input
                                        type="date"
                                        wire:model.live="dateFrom"
                                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                                    >
                                    <span class="text-xs font-bold text-slate-500">s/d</span>
                                    <input
                                        type="date"
                                        wire:model.live="dateTo"
                                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                                    >
                                </div>
                            @endif

                            @if (count($branches) > 1)
                                <select
                                    wire:model.live="selectedBranchId"
                                    class="px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="relative min-w-[200px]">
                                <i class="ph ph-magnifying-glass text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
                                <input
                                    type="text"
                                    wire:model.live.debounce.250ms="search"
                                    placeholder="Cari kata kunci..."
                                    class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
                                >
                            </div>
                        </div>

                        <!-- Quick Date Presets -->
                        @if ($activeTab !== 'ar-aging' && $activeTab !== 'stock-prices')
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset:</span>
                                <button wire:click="setQuickDateRange('today')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">Hari Ini</button>
                                <button wire:click="setQuickDateRange('this_week')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">Minggu Ini</button>
                                <button wire:click="setQuickDateRange('this_month')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">Bulan Ini</button>
                                <button wire:click="setQuickDateRange('this_year')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">Tahun Ini</button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- TAB 1: LAPORAN PENJUALAN (SALES REPORT)                                    -->
                <!-- ========================================================================= -->
                @if ($activeTab === 'sales')
                    <div class="space-y-6">
                        <!-- KPI Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Omzet Penjualan</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['grand_total'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['total_transactions'] ?? 0 }} Transaksi Selesai</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total HPP Barang</span>
                                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_cogs'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">Harga Pokok Penjualan</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Laba Kotor (Gross Profit)</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['gross_profit'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Margin: {{ $reportData['profit_margin'] ?? 0 }}%</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Diskon & Pajak</span>
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1">Disc: Rp {{ number_format($reportData['total_discount'] ?? 0, 0, ',', '.') }}</div>
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-300">Pajak: Rp {{ number_format($reportData['total_tax'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Sales Table Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                                <span>Rincian Transaksi Penjualan</span>
                                <span class="text-xs text-slate-400 font-normal">Menampilkan {{ count($reportData['sales'] ?? []) }} baris</span>
                            </div>

                            <x-pos.table.container>
                                <x-pos.table>
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>No Invoice</x-pos.table.th>
                                            <x-pos.table.th>Tanggal</x-pos.table.th>
                                            <x-pos.table.th>Pelanggan</x-pos.table.th>
                                            <x-pos.table.th>Kategori</x-pos.table.th>
                                            <x-pos.table.th>Metode Bayar</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Grand Total</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Est. HPP</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Laba Kotor</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($reportData['sales'] ?? [] as $sale)
                                            @php
                                                $saleCOGS = 0;
                                                foreach ($sale->items as $item) {
                                                    $hpp = $item->variant ? ($item->variant->hpp ?: $item->variant->cost_price ?: 0) : 0;
                                                    $saleCOGS += ($hpp * $item->quantity);
                                                }
                                                $profit = $sale->grand_total - $saleCOGS;
                                            @endphp
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-bold text-xs text-primary dark:text-blue-400 font-mono">{{ $sale->invoice_number }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $sale->invoice_date->format('d/m/Y') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs font-semibold text-slate-900 dark:text-white">{{ $sale->customer->name ?? 'Walk-in / Umum' }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs"><span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded font-bold">{{ $sale->sale_category ?: 'Store' }}</span></x-pos.table.td>
                                                <x-pos.table.td class="text-xs uppercase font-bold text-slate-600 dark:text-slate-300">{{ $sale->payment_method }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-slate-900 dark:text-white">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-semibold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($saleCOGS, 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($profit, 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="8" icon="ph-receipt" message="Belum ada data transaksi penjualan pada periode ini." />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>
                        </div>
                    </div>
                @endif

                <!-- ========================================================================= -->
                <!-- TAB 2: LAPORAN PIUTANG (ACCOUNTS RECEIVABLE / AR AGING)                   -->
                <!-- ========================================================================= -->
                @if ($activeTab === 'ar-aging')
                    <div class="space-y-6">
                        <!-- AR Aging Summary Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm col-span-2 md:col-span-1">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Saldo Piutang</span>
                                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_outstanding'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['count_invoices'] ?? 0 }} Invoice Belum Lunas</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/40 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">0 - 30 Hari (Lancar)</span>
                                <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_0_30'] ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/40 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">31 - 60 Hari</span>
                                <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_31_60'] ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/40 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase">61 - 90 Hari</span>
                                <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_61_90'] ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/40 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase">> 90 Hari (Menunggak)</span>
                                <div class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format($reportData['aging_over_90'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- AR Table Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                                <span>Rincian Saldo Piutang Usaha per Pelanggan</span>
                                <span class="text-xs text-slate-400 font-normal">Standard ERP AR Aging Schedule</span>
                            </div>

                            <x-pos.table.container>
                                <x-pos.table>
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>Pelanggan</x-pos.table.th>
                                            <x-pos.table.th>No Invoice</x-pos.table.th>
                                            <x-pos.table.th>Tgl Invoice</x-pos.table.th>
                                            <x-pos.table.th>Jatuh Tempo</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Inv</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Sudah Dibayar</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Sisa Piutang</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Umur (Hari)</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($reportData['items'] ?? [] as $ar)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">{{ $ar['customer_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-mono text-xs font-bold text-primary dark:text-blue-400">{{ $ar['invoice_number'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $ar['invoice_date'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $ar['due_date'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-semibold text-xs text-slate-900 dark:text-white">Rp {{ number_format($ar['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-semibold text-xs text-emerald-600">Rp {{ number_format($ar['paid_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($ar['outstanding'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $ar['age_days'] <= 30 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40' : ($ar['age_days'] <= 60 ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40') }}">
                                                        {{ $ar['age_days'] }} Hari ({{ $ar['aging_group'] }})
                                                    </span>
                                                </x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="8" icon="ph-check-circle" message="Tidak ada piutang aktif yang menunggak saat ini." />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>
                        </div>
                    </div>
                @endif

                <!-- ========================================================================= -->
                <!-- TAB 3: LAPORAN PELUNASAN PIUTANG (AR SETTLEMENT)                         -->
                <!-- ========================================================================= -->
                @if ($activeTab === 'ar-settlement')
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Pelunasan Piutang Diterima</span>
                                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['total_settled'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">{{ $reportData['total_count'] ?? 0 }} Transaksi Pelunasan</span>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
                                Riwayat Penerimaan & Pelunasan Piutang Pelanggan
                            </div>

                            <x-pos.table.container>
                                <x-pos.table>
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>No Bukti / Jurnal</x-pos.table.th>
                                            <x-pos.table.th>Tanggal Pelunasan</x-pos.table.th>
                                            <x-pos.table.th>Pelanggan</x-pos.table.th>
                                            <x-pos.table.th>No Invoice Terkait</x-pos.table.th>
                                            <x-pos.table.th>Akun Masuk (Kas/Bank)</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Jumlah Pelunasan</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($reportData['settlements'] ?? [] as $st)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $st['entry_no'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300">{{ $st['date'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">{{ $st['customer_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-mono text-xs font-bold text-primary dark:text-blue-400">{{ $st['invoice_no'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-700 dark:text-slate-300"><span class="bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded font-bold">{{ $st['account_name'] }}</span></x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($st['amount'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="6" icon="ph-hand-coins" message="Belum ada transaksi pelunasan piutang pada periode ini." />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>
                        </div>
                    </div>
                @endif

                <!-- ========================================================================= -->
                <!-- TAB 4: LAPORAN KAS HARIAN (DAILY CASH REPORT)                              -->
                <!-- ========================================================================= -->
                @if ($activeTab === 'daily-cash')
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Kas Masuk (Inflow)</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['total_inflow'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Kas Keluar (Outflow)</span>
                                <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">Rp {{ number_format($reportData['total_outflow'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Arus Kas Bersih (Net Cash)</span>
                                <div class="text-xl font-black text-primary dark:text-blue-400 mt-1">Rp {{ number_format($reportData['net_cash_flow'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
                                Mutasi BUKU KAS HARIAN POS
                            </div>

                            <x-pos.table.container>
                                <x-pos.table>
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>Waktu</x-pos.table.th>
                                            <x-pos.table.th>Petugas</x-pos.table.th>
                                            <x-pos.table.th>Kategori Transaksi</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Tipe</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Jumlah</x-pos.table.th>
                                            <x-pos.table.th>Keterangan / Notes</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($reportData['transactions'] ?? [] as $tx)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="text-xs text-slate-600 dark:text-slate-300 font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs font-bold text-slate-900 dark:text-white">{{ $tx->creator?->name ?? $tx->user?->name ?? '-' }}</x-pos.table.td>
                                                <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $tx->category }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tx->type === 'inflow' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40' }}">
                                                        {{ $tx->type }}
                                                    </span>
                                                </x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs {{ $tx->type === 'inflow' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                                </x-pos.table.td>
                                                <x-pos.table.td class="text-xs text-slate-500 italic">{{ $tx->notes ?: '-' }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="6" icon="ph-wallet" message="Belum ada mutasi kas harian pada periode ini." />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>
                        </div>
                    </div>
                @endif

                <!-- ========================================================================= -->
                <!-- TAB 5: DAFTAR STOK DAN HARGA (STOCK & PRICE VALUATION)                   -->
                <!-- ========================================================================= -->
                @if ($activeTab === 'stock-prices')
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total SKU / Varian</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($reportData['total_sku'] ?? 0, 0, ',', '.') }} Item</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">Total Fisik: {{ number_format($reportData['total_qty'] ?? 0, 0, ',', '.') }} pcs</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Nilai Persediaan (HPP)</span>
                                <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1">Rp {{ number_format($reportData['total_hpp_valuation'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">Asset Inventory Valuation</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Total Potensi Omzet</span>
                                <div class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1">Rp {{ number_format($reportData['total_retail_valuation'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-slate-500 font-medium mt-1 block">Retail Sales Value</span>
                            </div>

                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Potensi Profit Kotor</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($reportData['potential_profit'] ?? 0, 0, ',', '.') }}</div>
                                <span class="text-[11px] text-emerald-500 font-bold mt-1 block">Potensi Margin Bersih</span>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white">
                                Laporan Daftar Stok & Penilaian Harga Barang
                            </div>

                            <x-pos.table.container>
                                <x-pos.table>
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>SKU / Barcode</x-pos.table.th>
                                            <x-pos.table.th>Nama Produk</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Stok (Pcs)</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Harga HPP</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Harga Jual</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Nilai HPP</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Nilai Jual</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($reportData['items'] ?? [] as $st)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                                                    <div class="font-bold text-primary dark:text-blue-400">{{ $st['sku'] }}</div>
                                                    <div class="text-[10px] text-slate-400">BC: {{ $st['barcode'] }}</div>
                                                </x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">
                                                    {{ $st['product_name'] }}
                                                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded ml-1">{{ $st['type'] }}</span>
                                                </x-pos.table.td>
                                                <x-pos.table.td class="text-center font-bold text-xs text-slate-900 dark:text-white">{{ number_format($st['stock'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-semibold text-xs text-rose-600 dark:text-rose-400">Rp {{ number_format($st['hpp'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-semibold text-xs text-emerald-600 dark:text-emerald-400">Rp {{ number_format($st['price'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-blue-600 dark:text-blue-400">Rp {{ number_format($st['total_hpp_valuation'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-bold text-xs text-purple-600 dark:text-purple-400">Rp {{ number_format($st['total_retail_valuation'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="7" icon="ph-package" message="Belum ada data stok produk ditemukan." />
                                        @endforelse
                                    </tbody>
                                </x-pos.table>
                            </x-pos.table.container>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>
