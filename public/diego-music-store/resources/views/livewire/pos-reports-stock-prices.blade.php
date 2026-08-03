<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Daftar Stok & Nilai Persediaan"
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
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Daftar Stok & Nilai Persediaan</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Daftar Stok & Nilai Persediaan {{ $currentBranch?->name ? '— ' . $currentBranch->name : '' }}</h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Common Filter Toolbar Card (4-Column Layout) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4.5 shadow-sm space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <!-- Col 1: Status Ketersediaan Stok -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Status Ketersediaan Stok</label>
                                <x-pos.form.select model="stockStatus" :live="true" size="sm" icon="ph-funnel">
                                    <option value="all">Semua Status Stok</option>
                                    <option value="available">Stok Tersedia / Aman</option>
                                    <option value="low">Stok Rendah (Batas Min)</option>
                                    <option value="out_of_stock">Stok Habis (Kosong)</option>
                                </x-pos.form.select>
                            </div>

                            <!-- Col 2: Filter Cabang -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Filter Cabang</label>
                                <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                                    <option value="">Semua Cabang (Total Stok)</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </x-pos.form.select>
                            </div>

                            <!-- Col 3: Kategori Produk -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Produk</label>
                                <x-pos.form.select model="selectedCategory" :live="true" size="sm" icon="ph-tag">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </x-pos.form.select>
                            </div>

                            <!-- Col 4: Pencarian Barang & Reset -->
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pencarian Barang</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 min-w-0">
                                        <x-pos.form.input model="search" :live="true" placeholder="Cari SKU / Barcode / Nama / Merk..." icon="ph-magnifying-glass" size="sm" />
                                    </div>
                                    <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="shrink-0" wire:click="resetFilters" title="Reset Filter">
                                        Reset
                                    </x-pos.utility.button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Cards (Identik dengan Back Office StockListReport) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Total SKU / Varian</span>
                            <div class="text-xl font-black text-slate-900 dark:text-white mt-1 font-mono">
                                {{ number_format($reportData['total_variants'] ?? 0, 0, ',', '.') }} Item
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">Total Fisik: {{ number_format($reportData['total_physical_qty'] ?? 0, 0, ',', '.') }} pcs</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Status Stok Kritis</span>
                            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1 font-mono">
                                {{ number_format($reportData['total_out_of_stock_count'] ?? 0, 0, ',', '.') }} Habis
                            </div>
                            <span class="text-[11px] text-amber-500 font-bold mt-1 block">{{ number_format($reportData['total_low_stock_count'] ?? 0, 0, ',', '.') }} Stok Rendah</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Total Nilai Aset (HPP)</span>
                            <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
                                Rp {{ number_format($reportData['grand_total_valuation'] ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">HPP Inventory Valuation</span>
                        </div>

                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Potensi Nilai Jual</span>
                            <div class="text-xl font-black text-purple-600 dark:text-purple-400 mt-1 font-mono">
                                Rp {{ number_format($reportData['grand_total_retail_value'] ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-purple-500 font-bold mt-1 block">Retail Sales Value</span>
                        </div>
                    </div>

                    <!-- Table Card (Identik dengan Kolom Back Office StockListReport) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                            <span>LAPORAN DAFTAR STOK & NILAI PERSEDIAAN BARANG</span>
                            <span class="text-xs text-slate-400 font-normal">Cabang: {{ $reportData['branch_name'] ?? 'Semua Cabang' }} &bull; Kategori: {{ $reportData['category'] ?? 'Semua Kategori' }}</span>
                        </div>

                        <x-pos.table.container>
                            <x-pos.table>
                                <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                    <tr>
                                        <x-pos.table.th>SKU / Barcode</x-pos.table.th>
                                        <x-pos.table.th>Nama Produk & Variasi</x-pos.table.th>
                                        <x-pos.table.th>Kategori & Merk</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Stok Fisik</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Batas Min</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Satuan</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Status Stok</x-pos.table.th>
                                        <x-pos.table.th class="text-center">Diskon</x-pos.table.th>
                                        <x-pos.table.th class="text-center">PPN</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Harga Beli (HPP)</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Harga Jual</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Total Nilai HPP</x-pos.table.th>
                                        <x-pos.table.th class="text-right">Total Nilai Jual</x-pos.table.th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    @forelse ($reportData['rows'] ?? [] as $row)
                                        <x-pos.table.tr>
                                            <x-pos.table.td class="font-mono text-xs text-slate-900 dark:text-white">
                                                <div class="font-bold text-primary dark:text-blue-400">{{ $row['sku'] }}</div>
                                                <div class="text-[10px] text-slate-400">BC: {{ $row['barcode'] }}</div>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="font-bold text-xs text-slate-900 dark:text-white">
                                                {{ $row['full_name'] }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-200 block w-fit">{{ $row['category'] }}</span>
                                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $row['brand'] }}</span>
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center font-mono font-extrabold text-xs text-slate-900 dark:text-white">
                                                {{ number_format($row['stock'], 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center font-mono text-xs text-slate-500">
                                                {{ number_format($row['min_stock'], 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center text-xs text-slate-500 font-semibold">
                                                {{ $row['unit'] }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center">
                                                @if ($row['badge_color'] === 'danger')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                                        {{ $row['status_label'] }}
                                                    </span>
                                                @elseif ($row['badge_color'] === 'warning')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                                        {{ $row['status_label'] }}
                                                    </span>
                                                @elseif ($row['badge_color'] === 'info')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                                        {{ $row['status_label'] }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        {{ $row['status_label'] }}
                                                    </span>
                                                @endif
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                                                {{ $row['discount'] }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                                                {{ $row['tax'] }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-right font-mono text-xs text-rose-600 dark:text-rose-400 font-semibold">
                                                Rp {{ number_format($row['cost_price'], 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-right font-mono text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                                                Rp {{ number_format($row['retail_price'], 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-right font-mono font-extrabold text-xs text-blue-600 dark:text-blue-400">
                                                Rp {{ number_format($row['valuation'], 0, ',', '.') }}
                                            </x-pos.table.td>

                                            <x-pos.table.td class="text-right font-mono font-extrabold text-xs text-purple-600 dark:text-purple-400">
                                                Rp {{ number_format($row['retail_value'], 0, ',', '.') }}
                                            </x-pos.table.td>
                                        </x-pos.table.tr>
                                    @empty
                                        <x-pos.table.empty colspan="13" icon="ph-package" message="Belum ada data stok produk ditemukan." />
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
                    size: A4 landscape;
                    margin: 10mm 12mm;
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
                    padding: 4px 5px;
                    font-size: 8pt;
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
                LAPORAN DAFTAR STOK & NILAI PERSEDIAAN
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                CABANG: {{ strtoupper($reportData['branch_name'] ?? 'SEMUA CABANG') }} &bull; KATEGORI: {{ strtoupper($reportData['category'] ?? 'SEMUA KATEGORI') }}
            </div>
        </div>

        <!-- Summary -->
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total SKU / Varian</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">{{ number_format($reportData['total_variants'] ?? 0, 0, ',', '.') }} SKU ({{ number_format($reportData['total_physical_qty'] ?? 0, 0, ',', '.') }} pcs)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Grand Total Nilai HPP</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['grand_total_valuation'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Status Stok Kritis</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">{{ number_format($reportData['total_out_of_stock_count'] ?? 0, 0, ',', '.') }} Habis | {{ number_format($reportData['total_low_stock_count'] ?? 0, 0, ',', '.') }} Rendah</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Potensi Nilai Jual</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['grand_total_retail_value'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Grid -->
        <table class="erp-print-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th style="width: 100px;">SKU / BARCODE</th>
                    <th>NAMA PRODUK & VARIASI</th>
                    <th>KATEGORI</th>
                    <th>MERK</th>
                    <th style="width: 45px;" class="text-center">STOK</th>
                    <th style="width: 45px;" class="text-center">MIN</th>
                    <th style="width: 45px;" class="text-center">SATUAN</th>
                    <th style="width: 70px;" class="text-center">STATUS</th>
                    <th style="width: 75px;" class="text-right">HARGA HPP</th>
                    <th style="width: 75px;" class="text-right">HARGA JUAL</th>
                    <th style="width: 90px;" class="text-right">NILAI HPP</th>
                    <th style="width: 90px;" class="text-right">NILAI JUAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['rows'] ?? [] as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $row['sku'] }}<br><span style="font-size: 7.5pt; font-weight: normal; color: #444;">BC: {{ $row['barcode'] }}</span></td>
                        <td style="font-weight: bold;">{{ $row['full_name'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['brand'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($row['stock'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono">{{ number_format($row['min_stock'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row['unit'] }}</td>
                        <td class="text-center font-bold" style="font-size: 7.5pt;">{{ $row['status_label'] }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($row['retail_price'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($row['valuation'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($row['retail_value'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data stok produk ditemukan.</td>
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
