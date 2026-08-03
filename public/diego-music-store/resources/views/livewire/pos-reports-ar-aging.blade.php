<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Piutang Usaha (AR Aging)"
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
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Laporan Piutang</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Laporan Piutang Usaha (AR Aging)</h1>
                        </div>

                        <!-- Print Action Button -->
                        <div class="flex items-center gap-2">
                            <x-pos.utility.button variant="primary" size="sm" icon="ph-printer" onclick="window.print()">
                                Cetak Laporan
                            </x-pos.utility.button>
                        </div>
                    </div>

                    <!-- Comprehensive Filter Toolbar Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
                            <!-- Filter Cabang -->
                            @if (count($branches) > 1)
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Cabang</label>
                                    <x-pos.form.select model="selectedBranchId" :live="true" size="sm" icon="ph-storefront">
                                        <option value="">Semua Cabang</option>
                                        @foreach ($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </x-pos.form.select>
                                </div>
                            @endif

                            <!-- Filter Pelanggan -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</label>
                                <select wire:model.live="selectedCustomerId" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                                    <option value="">Semua Pelanggan</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Kategori Umur Piutang -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Umur Piutang</label>
                                <select wire:model.live="agingGroupFilter" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                                    <option value="">Semua Umur</option>
                                    <option value="0-30">0 - 30 Hari (Lancar)</option>
                                    <option value="31-60">31 - 60 Hari</option>
                                    <option value="61-90">61 - 90 Hari</option>
                                    <option value="over-90">> 90 Hari (Menunggak)</option>
                                </select>
                            </div>

                            <!-- Filter Rentang Tanggal Dari -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Dari Tgl Inv</label>
                                <input type="date" wire:model.live="dateFrom" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                            </div>

                            <!-- Filter Rentang Tanggal Sampai -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Sampai Tgl Inv</label>
                                <input type="date" wire:model.live="dateTo" class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-primary focus:outline-none">
                            </div>

                            <!-- Search Keyword -->
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                                    <x-pos.form.input model="search" :live="true" placeholder="No Invoice / Nama..." icon="ph-magnifying-glass" size="sm" />
                                </div>
                                <button wire:click="resetFilters" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 text-xs font-semibold rounded-lg transition-colors" title="Reset Filter">
                                    <i class="ph ph-arrow-counter-clockwise text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

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
                                        <x-pos.table.tr wire:click="showDetails({{ $ar['sale_id'] }})" class="cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
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
            </div>
        </main>
    </div>

    {{-- ===================== MODAL: DETAIL & HISTORI LAPORAN AR AGING ===================== --}}
    <x-pos.modal
        wire:model="showDetailModal"
        title="Detail Laporan Piutang Usaha (AR Aging)"
        subtitle="Rincian transaksi invoice, umur piutang, dan riwayat cicilan pelunasan"
        icon="ph-chart-bar"
        maxWidth="8xl"
    >
        @if ($selectedSale)
            @php
                $piutangRemaining = $selectedSale->getPiutangAmount();
                $totalPaidCalculated = max(0, floatval($selectedSale->grand_total) - $piutangRemaining);
            @endphp
            <div class="space-y-6">
                <!-- Summary Meta Grid -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">No. Invoice</span>
                        <span class="text-base font-mono font-extrabold text-slate-900 dark:text-white">{{ $selectedSale->invoice_number }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Pelanggan</span>
                        <span class="text-base font-bold text-slate-900 dark:text-white">{{ $selectedSale->customer->name ?? 'Pelanggan Umum' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Transaksi</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $selectedSale->invoice_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status Umur Piutang</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $selectedAgeDays <= 30 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 border border-emerald-200' : ($selectedAgeDays <= 60 ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 border border-blue-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 border border-rose-200') }}">
                            {{ $selectedAgeDays }} Hari ({{ $selectedAgingGroup }})
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status Tagihan</span>
                        @if ($piutangRemaining <= 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-900/40">
                                Lunas (Completed)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full border border-amber-200 dark:border-amber-900/40">
                                Belum Lunas (Piutang)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Financial Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Total Belanja Invoice</span>
                        <span class="text-lg font-mono font-black text-slate-900 dark:text-white">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-emerald-50/40 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 text-center">
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider block mb-1">Total Sudah Dibayar</span>
                        <span class="text-lg font-mono font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPaidCalculated, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-rose-50/40 dark:bg-rose-950/20 p-4 rounded-xl border border-rose-200/60 dark:border-rose-900/40 text-center">
                        <span class="text-xs text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wider block mb-1">Sisa Piutang / Tagihan</span>
                        <span class="text-xl font-mono font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($piutangRemaining, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Settlement History Table -->
                <div>
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Histori Riwayat Pembayaran / Cicilan</h4>
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                                <tr>
                                    <x-pos.table.th>No. Jurnal / Ref</x-pos.table.th>
                                    <x-pos.table.th>Tanggal Pelunasan</x-pos.table.th>
                                    <x-pos.table.th>Akun Penerima (Kas/Bank)</x-pos.table.th>
                                    <x-pos.table.th>Keterangan</x-pos.table.th>
                                    <x-pos.table.th>Kasir / Operator</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Nominal Dibayar</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($settlementHistory as $history)
                                    <x-pos.table.tr>
                                        <x-pos.table.td class="whitespace-nowrap font-mono font-bold text-xs text-slate-900 dark:text-slate-100">
                                            {{ $history['entry_number'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['date'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs font-bold text-blue-600 dark:text-blue-400">
                                            {{ $history['account_name'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['description'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-xs text-slate-600 dark:text-slate-355">
                                            {{ $history['user_name'] }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="whitespace-nowrap text-right font-mono font-extrabold text-sm text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($history['amount'], 0, ',', '.') }}
                                        </x-pos.table.td>
                                    </x-pos.table.tr>
                                @empty
                                    <x-pos.table.empty colspan="6" icon="ph-receipt" message="Belum ada riwayat cicilan/pelunasan yang tercatat untuk invoice ini." />
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button
                        type="button"
                        wire:click="closeDetails"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        @endif
    </x-pos.modal>

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

        <!-- Title -->
        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline;">
                LAPORAN PIUTANG USAHA (AR AGING SCHEDULE)
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                PER TANGGAL: {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <!-- Summary Aging Table -->
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 20%;">Total Saldo Piutang</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['total_outstanding'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 20%;">0 - 30 Hari (Lancar)</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Rp {{ number_format($reportData['aging_0_30'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">31 - 60 Hari</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Rp {{ number_format($reportData['aging_31_60'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">61 - 90 Hari</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Rp {{ number_format($reportData['aging_61_90'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">> 90 Hari (Menunggak)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['aging_over_90'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Total Invoice Active</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">{{ $reportData['count_invoices'] ?? 0 }} Invoice</td>
            </tr>
        </table>

        <!-- Main Grid -->
        <table class="erp-print-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th>NAMA PELANGGAN</th>
                    <th style="width: 110px;">NO INVOICE</th>
                    <th style="width: 75px;">TGL INV</th>
                    <th style="width: 75px;">JTH TEMPO</th>
                    <th style="width: 90px;" class="text-right">TOTAL INV</th>
                    <th style="width: 85px;" class="text-right">DIBAYAR</th>
                    <th style="width: 95px;" class="text-right">SISA PIUTANG</th>
                    <th style="width: 90px;" class="text-center">UMUR & STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $idx => $ar)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold;">{{ $ar['customer_name'] }}</td>
                        <td class="font-mono font-bold">{{ $ar['invoice_number'] }}</td>
                        <td>{{ $ar['invoice_date'] }}</td>
                        <td>{{ $ar['due_date'] }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($ar['grand_total'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($ar['paid_amount'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($ar['outstanding'], 0, ',', '.') }}</td>
                        <td class="text-center" style="font-size: 8pt;">{{ $ar['age_days'] }} Hari ({{ $ar['aging_group'] }})</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 15px; font-style: italic;">Tidak ada piutang aktif yang menunggak saat ini.</td>
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
                        <span style="font-size: 8pt; color: #444;">Staf Operasional POS</span>
                    </td>
                    <td style="width: 33%; border: none;">
                        Diperiksa oleh,<br><br><br><br>
                        <strong>( __________________ )</strong><br>
                        <span style="font-size: 8pt; color: #444;">Supervisor / AR Officer</span>
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
