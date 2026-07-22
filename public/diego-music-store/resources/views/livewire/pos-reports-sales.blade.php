<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Penjualan ERP"
                backLabel="Dashboard"
            />

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto no-scrollbar p-6">
                <div class="w-full space-y-6">

                    <!-- Header & Breadcrumb -->
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
                                            <span class="text-slate-400">Laporan</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Laporan Penjualan</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Laporan Penjualan ERP</h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Common Filter Toolbar Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            <!-- Date Range, Branch & Search Filters -->
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Dari:</span>
                                    <x-pos.form.input type="date" model="dateFrom" :live="true" size="sm" />
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">s/d</span>
                                    <x-pos.form.input type="date" model="dateTo" :live="true" size="sm" />
                                </div>

                                @if (count($branches) > 1)
                                    <div class="min-w-[160px]">
                                        <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                                            <option value="">Semua Cabang</option>
                                            @foreach ($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </x-pos.form.select>
                                    </div>
                                @endif

                                <div class="min-w-[220px]">
                                    <x-pos.form.input model="search" :live="true" placeholder="Cari invoice/pelanggan..." icon="ph-magnifying-glass" size="sm" />
                                </div>
                            </div>

                            <!-- Quick Date Presets -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset:</span>
                                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('today')">Hari Ini</x-pos.utility.button>
                                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_week')">Minggu Ini</x-pos.utility.button>
                                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_month')">Bulan Ini</x-pos.utility.button>
                                <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_year')">Tahun Ini</x-pos.utility.button>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Cards -->
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
            </div>
        </main>
    </div>

    <!-- FORMAL BLACK & WHITE ERP PRINT TEMPLATE (Printed on paper only) -->
    <div class="hidden print:block font-serif text-black bg-white p-0 m-0 leading-tight w-full">
        <style>
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 12mm 15mm;
                }
                body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-family: Arial, Helvetica, sans-serif;
                }
                .erp-print-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    margin-bottom: 15px;
                }
                .erp-print-table th, .erp-print-table td {
                    border: 1px solid #000000;
                    padding: 5px 6px;
                    font-size: 9pt;
                }
                .erp-print-table th {
                    background-color: #e5e7eb !important;
                    font-weight: bold;
                    text-transform: uppercase;
                    text-align: left;
                }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .font-mono { font-family: 'Courier New', Courier, monospace; }
            }
        </style>

        <!-- Company Header -->
        <div style="border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1 style="font-size: 16pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ $currentBranch?->store_name ?: 'DIEGO MUSIC STORE' }}
                    </h1>
                    <div style="font-size: 9pt; margin-top: 3px; font-weight: bold;">
                        CABANG: {{ strtoupper($currentBranch?->name ?: 'KANTOR PUSAT') }}
                    </div>
                    <div style="font-size: 9pt; color: #333;">
                        {{ $currentBranch?->address ?: 'Jl. Utama Music Store ERP' }} | Telp: {{ $currentBranch?->phone ?: '-' }}
                    </div>
                </div>
                <div style="text-align: right; font-size: 8pt;" class="font-mono">
                    <div>TGL CETAK: {{ now()->format('d/m/Y H:i:s') }}</div>
                    <div>PETUGAS: {{ strtoupper(auth()->user()?->name ?: 'ADMIN') }}</div>
                    <div>STATUS: DOKUMEN RESMI ERP</div>
                </div>
            </div>
        </div>

        <!-- Title & Subtitle -->
        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline;">
                LAPORAN PENJUALAN
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                PERIODE: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'AWAL' }} S/D {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'SEKARANG' }}
            </div>
        </div>

        <!-- Summary Table -->
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total Omzet Penjualan</td>
                <td style="border: 1px solid #000; padding: 5px; width: 25%; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['grand_total'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total HPP Barang</td>
                <td style="border: 1px solid #000; padding: 5px; width: 25%;" class="font-mono text-right">Rp {{ number_format($reportData['total_cogs'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Laba Kotor (Gross Profit)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['gross_profit'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Margin Laba Kotor</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">{{ $reportData['profit_margin'] ?? 0 }}%</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Total Transaksi Selesai</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">{{ $reportData['total_transactions'] ?? 0 }} Transaksi</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Rincian Diskon / Pajak</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Disc: Rp {{ number_format($reportData['total_discount'] ?? 0, 0, ',', '.') }} | Tax: Rp {{ number_format($reportData['total_tax'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Main ERP Table Grid -->
        <table class="erp-print-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th style="width: 110px;">NO INVOICE</th>
                    <th style="width: 75px;">TANGGAL</th>
                    <th>PELANGGAN</th>
                    <th style="width: 70px;">KATEGORI</th>
                    <th style="width: 80px;">METODE</th>
                    <th style="width: 95px;" class="text-right">GRAND TOTAL</th>
                    <th style="width: 85px;" class="text-right">EST. HPP</th>
                    <th style="width: 90px;" class="text-right">LABA KOTOR</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['sales'] ?? [] as $idx => $sale)
                    @php
                        $saleCOGS = 0;
                        foreach ($sale->items as $item) {
                            $hpp = $item->variant ? ($item->variant->hpp ?: $item->variant->cost_price ?: 0) : 0;
                            $saleCOGS += ($hpp * $item->quantity);
                        }
                        $profit = $sale->grand_total - $saleCOGS;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->invoice_date->format('d/m/Y') }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in / Umum' }}</td>
                        <td>{{ $sale->sale_category ?: 'Store' }}</td>
                        <td>{{ strtoupper($sale->payment_method) }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($saleCOGS, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($profit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 15px; font-style: italic;">Tidak ada data transaksi penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures Section -->
        <div style="margin-top: 40px; page-break-inside: avoid;">
            <table style="width: 100%; border: none; font-size: 9pt;">
                <tr style="text-align: center;">
                    <td style="width: 33%; border: none;">
                        Dibuat oleh,<br><br><br><br>
                        <strong>( {{ auth()->user()?->name ?: 'Admin Kasir' }} )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Staf Operasional POS</span>
                    </td>
                    <td style="width: 33%; border: none;">
                        Diperiksa oleh,<br><br><br><br>
                        <strong>( __________________ )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Supervisor / Accounting</span>
                    </td>
                    <td style="width: 33%; border: none;">
                        Disetujui oleh,<br><br><br><br>
                        <strong>( __________________ )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Manager / Owner</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
