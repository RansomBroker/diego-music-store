<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Daftar Stok & Penilaian Harga"
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
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Stok & Harga</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Daftar Stok & Penilaian Harga</h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Common Filter Toolbar Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                        <div class="flex flex-wrap items-center gap-3">
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

                            <div class="min-w-[250px]">
                                <x-pos.form.input model="search" :live="true" placeholder="Cari SKU / barcode / nama..." icon="ph-magnifying-glass" size="sm" />
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Cards -->
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

                    <!-- Table Card -->
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
            </div>
        </main>
    </div>

    <!-- FORMAL BLACK & WHITE ERP PRINT TEMPLATE -->
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
                    font-size: 8.5pt;
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

        <!-- Title -->
        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline;">
                DAFTAR STOK & PENILAIAN HARGA BARANG
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                PER TANGGAL: {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <!-- Summary -->
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total SKU / Varian</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">{{ number_format($reportData['total_sku'] ?? 0, 0, ',', '.') }} SKU ({{ number_format($reportData['total_qty'] ?? 0, 0, ',', '.') }} pcs)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Nilai Persediaan (Total HPP)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['total_hpp_valuation'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Total Potensi Omzet Jual</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Rp {{ number_format($reportData['total_retail_valuation'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Potensi Profit Kotor</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['potential_profit'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Grid -->
        <table class="erp-print-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th style="width: 110px;">SKU / BARCODE</th>
                    <th>NAMA PRODUK / VARIAN</th>
                    <th style="width: 50px;" class="text-center">STOK</th>
                    <th style="width: 80px;" class="text-right">HARGA HPP</th>
                    <th style="width: 80px;" class="text-right">HARGA JUAL</th>
                    <th style="width: 95px;" class="text-right">TOTAL NILAI HPP</th>
                    <th style="width: 100px;" class="text-right">TOTAL NILAI JUAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $idx => $st)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $st['sku'] }}<br><span style="font-size: 7.5pt; font-weight: normal; color: #444;">BC: {{ $st['barcode'] }}</span></td>
                        <td style="font-weight: bold;">{{ $st['product_name'] }} <span style="font-size: 7.5pt; font-weight: normal;">({{ $st['type'] }})</span></td>
                        <td class="text-center font-mono font-bold">{{ number_format($st['stock'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($st['hpp'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($st['price'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($st['total_hpp_valuation'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($st['total_retail_valuation'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data stok produk ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures -->
        <div style="margin-top: 40px; page-break-inside: avoid;">
            <table style="width: 100%; border: none; font-size: 9pt;">
                <tr style="text-align: center;">
                    <td style="width: 33%; border: none;">
                        Dibuat oleh,<br><br><br><br>
                        <strong>( {{ auth()->user()?->name ?: 'Admin Kasir' }} )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Staf Logistik / POS</span>
                    </td>
                    <td style="width: 33%; border: none;">
                        Diperiksa oleh,<br><br><br><br>
                        <strong>( __________________ )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Kepala Gudang / Supervisor</span>
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
