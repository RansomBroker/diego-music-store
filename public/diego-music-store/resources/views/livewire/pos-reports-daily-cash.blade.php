<div class="flex-1 flex h-full w-full overflow-hidden">
    <!-- WEB DISPLAY (Hidden on Print) -->
    <div class="print:hidden flex-1 flex h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Sidebar -->
        <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden">

            <!-- Navbar -->
            <x-pos.navbar
                pageTitle="Laporan Kas Harian ERP"
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
                                            <span class="text-slate-650 dark:text-slate-300 font-bold">Kas Harian</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Laporan Kas Harian ERP</h1>
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
                            
                            <!-- Date Range & Branch Filters -->
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

                    <!-- KPI Cards -->
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

                    <!-- Table Card -->
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
                LAPORAN MUTASI KAS HARIAN
            </h2>
            <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
                PERIODE: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'AWAL' }} S/D {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'SEKARANG' }}
            </div>
        </div>

        <!-- Summary -->
        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total Kas Masuk (Inflow)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right">Rp {{ number_format($reportData['total_inflow'] ?? 0, 0, ',', '.') }}</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total Kas Keluar (Outflow)</td>
                <td style="border: 1px solid #000; padding: 5px;" class="font-mono text-right">Rp {{ number_format($reportData['total_outflow'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Arus Kas Bersih (Net Cash)</td>
                <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class="font-mono text-right" colspan="3">Rp {{ number_format($reportData['net_cash_flow'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Grid -->
        <table class="erp-print-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th style="width: 110px;">WAKTU</th>
                    <th style="width: 120px;">PETUGAS</th>
                    <th>KATEGORI TRANSAKSI</th>
                    <th style="width: 60px;" class="text-center">TIPE</th>
                    <th style="width: 110px;" class="text-right">JUMLAH</th>
                    <th>KETERANGAN / NOTES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['transactions'] ?? [] as $idx => $tx)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-weight: bold;">{{ $tx->creator?->name ?? $tx->user?->name ?? '-' }}</td>
                        <td>{{ $tx->category }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ strtoupper($tx->type) }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                        <td style="font-style: italic;">{{ $tx->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px; font-style: italic;">Belum ada mutasi kas harian pada periode ini.</td>
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
