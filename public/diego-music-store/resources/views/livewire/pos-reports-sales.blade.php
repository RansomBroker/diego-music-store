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
                            <nav class="text-sm font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
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
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Laporan Penjualan {{ $currentBranch?->name ?? '' }}</h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Common Filter Toolbar Card (Expandable / Collapsible) -->
                    <div x-data="{ isExpanded: true }" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm transition-all duration-200 overflow-hidden">
                        
                        <!-- Filter Header Bar (Clickable to Collapse / Expand) -->
                        <div @click="isExpanded = !isExpanded" class="p-4 px-4.5 flex items-center justify-between cursor-pointer select-none bg-slate-50/50 dark:bg-slate-800/20 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <i class="ph ph-funnel text-base font-bold"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                        Filter Laporan & Parameter
                                    </h3>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Klik untuk menyembunyikan / menampilkan opsi filter
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span x-show="!isExpanded" class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Filter Sembunyi
                                </span>
                                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-200/50 dark:hover:bg-slate-800 transition-all">
                                    <i class="ph text-base transition-transform duration-200" :class="isExpanded ? 'ph-caret-up' : 'ph-caret-down'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filter Form Content Body (Collapsible) -->
                        <div x-show="isExpanded" x-collapse class="p-4.5 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <!-- Col 1: Mode Laporan -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Mode Laporan</label>
                                    <x-pos.form.select model="viewMode" :live="true" size="sm" icon="ph-squares-four">
                                        <option value="detail">Detail Transaksi</option>
                                        <option value="per_day">Per Hari</option>
                                        <option value="per_nota">Per Nota</option>
                                        <option value="top_selling">Terlaris</option>
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 2: Periode Dari -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Dari Tanggal</label>
                                    <x-pos.form.input type="date" model="dateFrom" :live="true" size="sm" />
                                </div>

                                <!-- Col 3: Periode s/d -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Sampai Tanggal</label>
                                    <x-pos.form.input type="date" model="dateTo" :live="true" size="sm" />
                                </div>

                                <!-- Col 4: Cabang -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Cabang</label>
                                    <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                                        <option value="">Semua Cabang</option>
                                        @foreach ($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 5: Pelanggan -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Pelanggan</label>
                                    <x-pos.form.select model="selectedCustomerId" :live="true" size="sm" icon="ph-user">
                                        <option value="">Semua Pelanggan</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 6: Jenis Pembayaran -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Jenis Pembayaran</label>
                                    <x-pos.form.select model="selectedPaymentMethod" :live="true" size="sm" icon="ph-credit-card">
                                        <option value="">Semua Jenis Bayar</option>
                                        @foreach ($paymentMethods as $pm)
                                            <option value="{{ $pm }}">{{ $pm }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 7: Sales Rep -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Sales Representative</label>
                                    <x-pos.form.select model="selectedSalesRepId" :live="true" size="sm" icon="ph-identification-badge">
                                        <option value="">Semua Sales</option>
                                        @foreach ($salesUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 8: Kasir -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kasir / Operator</label>
                                    <x-pos.form.select model="selectedCashierId" :live="true" size="sm" icon="ph-user-gear">
                                        <option value="">Semua Kasir</option>
                                        @foreach ($cashierUsers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 9: Kategori Penjualan -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Penjualan</label>
                                    <x-pos.form.select model="selectedSaleCategory" :live="true" size="sm" icon="ph-tag">
                                        <option value="">Semua Kat. Penjualan</option>
                                        @foreach ($saleCategories as $sc)
                                            <option value="{{ $sc }}">{{ $sc }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 10: Kategori Barang -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kategori Barang</label>
                                    <x-pos.form.select model="selectedProductCategory" :live="true" size="sm" icon="ph-package">
                                        <option value="">Semua Kat. Barang</option>
                                        @foreach ($productCategories as $pc)
                                            <option value="{{ $pc }}">{{ $pc }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 11: Filter Produk Spesifik -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Filter Produk</label>
                                    <x-pos.form.select model="selectedProductId" :live="true" size="sm" icon="ph-music-notes">
                                        <option value="">Semua Produk</option>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>

                                <!-- Col 12: Pencarian / Search -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Kata Kunci / Search</label>
                                    <x-pos.form.input model="search" :live="true" placeholder="Cari invoice/pelanggan/barang..." icon="ph-magnifying-glass" size="sm" />
                                </div>

                                <!-- Col 13: Actions (Reset) -->
                                <div>
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1 block">Reset</label>
                                    <x-pos.utility.button variant="secondary" size="sm" icon="ph-arrow-counter-clockwise" class="w-full justify-center" wire:click="resetFilters">
                                        Reset Filter
                                    </x-pos.utility.button>
                                </div>
                            </div>

                            <!-- Presets Bar -->
                            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase mr-1">Preset Tanggal:</span>
                                    <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('today')">Hari Ini</x-pos.utility.button>
                                    <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_week')">Minggu Ini</x-pos.utility.button>
                                    <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_month')">Bulan Ini</x-pos.utility.button>
                                    <x-pos.utility.button variant="secondary" size="sm" wire:click="setQuickDateRange('this_year')">Tahun Ini</x-pos.utility.button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales KPI Summary Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Card 1: Total Omzet Penjualan -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-sm font-semibold text-slate-400 uppercase">Total Omzet Penjualan</span>
                            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                Rp {{ number_format($summaryData['grand_total'] ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">
                                {{ number_format($summaryData['total_transactions'] ?? 0, 0, ',', '.') }} Transaksi Selesai
                            </span>
                        </div>

                        <!-- Card 2: Total HPP Barang -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-sm font-semibold text-slate-400 uppercase">Total HPP Barang</span>
                            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                                Rp {{ number_format($summaryData['total_cogs'] ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium mt-1 block">
                                Harga Pokok Penjualan
                            </span>
                        </div>

                        <!-- Card 3: Laba Kotor (Gross Profit) -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-sm font-semibold text-slate-400 uppercase">Laba Kotor (Gross Profit)</span>
                            <div class="text-xl font-black text-primary dark:text-blue-400 mt-1">
                                Rp {{ number_format($summaryData['gross_profit'] ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
                                Margin: {{ $summaryData['profit_margin'] ?? 0 }}%
                            </span>
                        </div>

                        <!-- Card 4: Diskon & Pajak -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                            <span class="text-sm font-semibold text-slate-400 uppercase">Diskon & Pajak</span>
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-200 mt-1 space-y-0.5 font-mono">
                                <div>Disc: <span class="text-rose-600">Rp {{ number_format($summaryData['total_discount'] ?? 0, 0, ',', '.') }}</span></div>
                                <div>Pajak: <span class="text-blue-600">Rp {{ number_format($summaryData['total_tax'] ?? 0, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card (Dynamic per View Mode) -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-900 dark:text-white flex justify-between items-center">
                            <span>
                                @if ($viewMode === 'detail')
                                    Detail Transaksi Penjualan Harian (Per Barang)
                                @elseif ($viewMode === 'per_day')
                                    Ringkasan Penjualan Per Hari
                                @elseif ($viewMode === 'per_nota')
                                    Ringkasan Penjualan Per Nota / Invoice
                                @elseif ($viewMode === 'top_selling')
                                    Laporan Produk Terlaris (Top Selling)
                                @endif
                            </span>
                            <span class="text-sm text-slate-400 font-normal uppercase">Mode: {{ str_replace('_', ' ', $viewMode) }}</span>
                        </div>

                        <x-pos.table.container>
                            <x-pos.table>
                                @if ($viewMode === 'detail')
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>Tanggal</x-pos.table.th>
                                            <x-pos.table.th>Kategori Jual</x-pos.table.th>
                                            <x-pos.table.th>No. Invoice</x-pos.table.th>
                                            <x-pos.table.th>Pelanggan</x-pos.table.th>
                                            <x-pos.table.th>Catatan / Note</x-pos.table.th>
                                            <x-pos.table.th>Kasir</x-pos.table.th>
                                            <x-pos.table.th>Sales</x-pos.table.th>
                                            <x-pos.table.th>Jenis Bayar</x-pos.table.th>
                                            <x-pos.table.th>Kode Barang</x-pos.table.th>
                                            <x-pos.table.th>Nama Barang</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Jlh / Qty</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Harga Satuan</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Diskon Item</x-pos.table.th>
                                            <x-pos.table.th class="text-right">PPN / Pajak</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Subtotal</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($paginatedReportData['paginated_items'] ?? [] as $item)
                                            <x-pos.table.tr class="align-top">
                                                @if (!empty($item['is_first_item']))
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['invoice_date'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200">{{ $item['sale_category'] }}</span>
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-mono font-bold text-primary dark:text-blue-400 whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['invoice_number'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['customer_name'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-800 dark:text-slate-200 italic whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['notes'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['cashier_name'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['sales_rep_name'] }}
                                                    </x-pos.table.td>
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="font-bold text-slate-900 dark:text-white whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-r border-slate-200 dark:border-slate-800 align-top">
                                                        {{ $item['payment_method'] }}
                                                    </x-pos.table.td>
                                                @endif
                                                <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $item['sku'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $item['product_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $item['quantity'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                @if (!empty($item['is_first_item']))
                                                    <x-pos.table.td rowspan="{{ $item['rowspan'] }}" class="text-right font-mono font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap bg-slate-50/40 dark:bg-slate-800/30 border-l border-slate-200 dark:border-slate-800 align-top">
                                                        Rp {{ number_format($item['tax_amount'], 0, ',', '.') }}
                                                    </x-pos.table.td>
                                                @endif
                                                <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="15" icon="ph-receipt" message="Belum ada transaksi penjualan harian pada periode ini." />
                                        @endforelse
                                    </tbody>
                                @elseif ($viewMode === 'per_day')
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>Tanggal</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Jumlah Nota / Transaksi</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Subtotal</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Diskon</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total PPN / Pajak</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Grand Total Penjualan</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($paginatedReportData['paginated_items'] ?? [] as $row)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $row['date'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white"><span class="px-2.5 py-1 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-600 font-bold">{{ $row['invoice_count'] }} Nota</span></x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($row['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($row['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="6" icon="ph-calendar" message="Belum ada data penjualan per hari pada periode ini." />
                                        @endforelse
                                    </tbody>
                                @elseif ($viewMode === 'per_nota')
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th>No. Invoice</x-pos.table.th>
                                            <x-pos.table.th>Tanggal</x-pos.table.th>
                                            <x-pos.table.th>Kategori Jual</x-pos.table.th>
                                            <x-pos.table.th>Pelanggan</x-pos.table.th>
                                            <x-pos.table.th>Kasir</x-pos.table.th>
                                            <x-pos.table.th>Sales</x-pos.table.th>
                                            <x-pos.table.th>Jenis Bayar</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Jlh Item</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Subtotal</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Diskon</x-pos.table.th>
                                            <x-pos.table.th class="text-right">PPN / Pajak</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Grand Total</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($paginatedReportData['paginated_items'] ?? [] as $nota)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="font-mono font-bold text-primary dark:text-blue-400">{{ $nota['invoice_number'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $nota['date'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white"><span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-bold text-slate-800 dark:text-slate-200">{{ $nota['sale_category'] }}</span></x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['customer_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['cashier_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['sales_rep_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $nota['payment_method'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center font-bold text-slate-900 dark:text-white">{{ $nota['item_count'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-slate-900 dark:text-white">Rp {{ number_format($nota['subtotal'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($nota['discount_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($nota['tax_amount'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($nota['grand_total'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="12" icon="ph-receipt" message="Belum ada transaksi nota penjualan pada periode ini." />
                                        @endforelse
                                    </tbody>
                                @elseif ($viewMode === 'top_selling')
                                    <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                        <tr>
                                            <x-pos.table.th class="text-center">Rangking</x-pos.table.th>
                                            <x-pos.table.th>Kode Barang (SKU/Barcode)</x-pos.table.th>
                                            <x-pos.table.th>Nama Barang</x-pos.table.th>
                                            <x-pos.table.th>Kategori Produk</x-pos.table.th>
                                            <x-pos.table.th class="text-center">Total Qty Terjual</x-pos.table.th>
                                            <x-pos.table.th class="text-right">Total Omzet Penjualan</x-pos.table.th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($paginatedReportData['paginated_items'] ?? [] as $rank => $prod)
                                            <x-pos.table.tr>
                                                <x-pos.table.td class="text-center font-black">
                                                    <span class="w-6 h-6 inline-flex items-center justify-center rounded-full font-bold {{ $rank === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : ($rank === 1 ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' : ($rank === 2 ? 'bg-amber-50 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                                                        #{{ $rank + 1 }}
                                                    </span>
                                                </x-pos.table.td>
                                                <x-pos.table.td class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $prod['sku'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $prod['product_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="font-bold text-slate-900 dark:text-white">{{ $prod['category_name'] }}</x-pos.table.td>
                                                <x-pos.table.td class="text-center font-extrabold text-primary dark:text-blue-400">{{ number_format($prod['total_qty'], 0, ',', '.') }}</x-pos.table.td>
                                                <x-pos.table.td class="text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($prod['total_revenue'], 0, ',', '.') }}</x-pos.table.td>
                                            </x-pos.table.tr>
                                        @empty
                                            <x-pos.table.empty colspan="6" icon="ph-trophy" message="Belum ada data produk terlaris pada periode ini." />
                                        @endforelse
                                    </tbody>
                                @endif
                            </x-pos.table>
                        </x-pos.table.container>

                        <!-- Table Footer Component -->
                        <x-pos.table.footer :paginator="$paginatedReportData['paginated_items'] ?? null" />
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
                LAPORAN PENJUALAN (MODE: {{ strtoupper(str_replace('_', ' ', $viewMode)) }})
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                PERIODE: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'AWAL' }} S/D {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'SEKARANG' }}
            </div>
        </div>

        <!-- Print Grid -->
        <table class="erp-print-table">
            @if ($viewMode === 'detail')
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">NO</th>
                        <th>TGL</th>
                        <th>KATEGORI</th>
                        <th>NO INVOICE</th>
                        <th>PELANGGAN</th>
                        <th>CATATAN</th>
                        <th>KASIR</th>
                        <th>SALES</th>
                        <th>BAYAR</th>
                        <th>KODE</th>
                        <th>NAMA BARANG</th>
                        <th class="text-center">QTY</th>
                        <th class="text-right">HARGA</th>
                        <th class="text-right">DISKON</th>
                        <th class="text-right">PPN</th>
                        <th class="text-right">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['items'] ?? [] as $idx => $item)
                        <tr>
                            @if (!empty($item['is_first_item']))
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top; text-align: center;">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['invoice_date'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['sale_category'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;" class="font-mono font-bold">{{ $item['invoice_number'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['customer_name'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top; font-style: italic;">{{ $item['notes'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['cashier_name'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['sales_rep_name'] }}</td>
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['payment_method'] }}</td>
                            @endif
                            <td class="font-mono">{{ $item['sku'] }}</td>
                            <td style="font-weight: bold;">{{ $item['product_name'] }}</td>
                            <td class="text-center font-bold">{{ $item['quantity'] }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</td>
                            @if (!empty($item['is_first_item']))
                                <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;" class="text-right font-mono">Rp {{ number_format($item['tax_amount'], 0, ',', '.') }}</td>
                            @endif
                            <td class="text-right font-mono font-bold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data penjualan harian pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'per_day')
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">NO</th>
                        <th>TANGGAL</th>
                        <th class="text-center">JUMLAH NOTA</th>
                        <th class="text-right">TOTAL SUBTOTAL</th>
                        <th class="text-right">TOTAL DISKON</th>
                        <th class="text-right">TOTAL PPN</th>
                        <th class="text-right">GRAND TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['items'] ?? [] as $idx => $row)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold;">{{ $row['date'] }}</td>
                            <td class="text-center font-bold">{{ $row['invoice_count'] }} Nota</td>
                            <td class="text-right font-mono">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($row['discount_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($row['grand_total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data penjualan per hari pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'per_nota')
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">NO</th>
                        <th>NO INVOICE</th>
                        <th>TANGGAL</th>
                        <th>KATEGORI</th>
                        <th>PELANGGAN</th>
                        <th>KASIR</th>
                        <th>SALES</th>
                        <th>JENIS BAYAR</th>
                        <th class="text-center">JLH ITEM</th>
                        <th class="text-right">SUBTOTAL</th>
                        <th class="text-right">DISKON</th>
                        <th class="text-right">PPN</th>
                        <th class="text-right">GRAND TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['items'] ?? [] as $idx => $nota)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-mono font-bold">{{ $nota['invoice_number'] }}</td>
                            <td>{{ $nota['date'] }}</td>
                            <td>{{ $nota['sale_category'] }}</td>
                            <td style="font-weight: bold;">{{ $nota['customer_name'] }}</td>
                            <td>{{ $nota['cashier_name'] }}</td>
                            <td>{{ $nota['sales_rep_name'] }}</td>
                            <td>{{ $nota['payment_method'] }}</td>
                            <td class="text-center font-bold">{{ $nota['item_count'] }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($nota['subtotal'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($nota['discount_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">Rp {{ number_format($nota['tax_amount'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($nota['grand_total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center" style="padding: 15px; font-style: italic;">Belum ada transaksi nota penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            @elseif ($viewMode === 'top_selling')
                <thead>
                    <tr>
                        <th style="width: 30px;" class="text-center">RANK</th>
                        <th>KODE BARANG</th>
                        <th>NAMA BARANG</th>
                        <th>KATEGORI PRODUK</th>
                        <th class="text-center">TOTAL QTY TERJUAL</th>
                        <th class="text-right">TOTAL OMZET</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['items'] ?? [] as $rank => $prod)
                        <tr>
                            <td class="text-center font-bold">#{{ $rank + 1 }}</td>
                            <td class="font-mono">{{ $prod['sku'] }}</td>
                            <td style="font-weight: bold;">{{ $prod['product_name'] }}</td>
                            <td>{{ $prod['category_name'] }}</td>
                            <td class="text-center font-bold">{{ number_format($prod['total_qty'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($prod['total_revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data produk terlaris pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            @endif
        </table>

        <!-- Signatures -->
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
                        <span style="font-size: 8pt; color: #444;">Supervisor / Finance</span>
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
