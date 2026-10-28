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
                padding: 4px 6px;
                font-size: 8.5pt;
            }
            .erp-print-table th {
                background-color: #f1f5f9 !important;
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
                <div style="font-size: 8.5pt; color: #333;">
                    {{ $currentBranch?->address ?: 'Jl. Utama Music Store ERP' }} | Telp: {{ $currentBranch?->phone ?: '-' }}
                </div>
            </div>
            <div style="text-align: right; font-size: 8.5pt;">
                <div style="font-weight: bold; font-size: 11pt; text-transform: uppercase;">
                    LAPORAN GILIRAN SALES
                </div>
                <div style="margin-top: 4px;">Tanggal: {{ \Illuminate\Support\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</div>
                <div>Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }}</div>
                <div>Oleh: {{ auth()->user()?->name ?: 'Kasir' }}</div>
            </div>
        </div>
    </div>

    <!-- Ringkasan KPI -->
    <table class="erp-print-table" style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th class="text-center">Total Giliran</th>
                <th class="text-center">Closing Nota</th>
                <th class="text-center">Rasio Closing</th>
                <th class="text-right">Total Omset Closing</th>
                <th class="text-center">Rata-rata Durasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="font-weight: bold;">{{ $summaryStats['total_servings'] }}x</td>
                <td class="text-center" style="font-weight: bold;">{{ $summaryStats['total_closings'] }} Nota</td>
                <td class="text-center" style="font-weight: bold;">{{ $summaryStats['conversion_rate'] }}%</td>
                <td class="text-right font-mono" style="font-weight: bold;">Rp {{ number_format($summaryStats['total_revenue'], 0, ',', '.') }}</td>
                <td class="text-center">{{ $summaryStats['avg_duration_minutes'] }} Menit</td>
            </tr>
        </tbody>
    </table>

    <!-- Bagian 1: Rekapitulasi Per Sales -->
    <div style="font-weight: bold; font-size: 9.5pt; text-transform: uppercase; margin-bottom: 4px;">
        I. Rekapitulasi Performa Giliran Sales
    </div>
    <table class="erp-print-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Nama Sales</th>
                <th>NIK</th>
                <th class="text-center">Giliran</th>
                <th class="text-center">Closing</th>
                <th class="text-center">Konversi</th>
                <th class="text-right">Total Omset</th>
                <th class="text-center">Rata2 Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($leaderboard as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">{{ $row['name'] }}</td>
                    <td>{{ $row['employee_nik'] }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $row['total_servings'] }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $row['total_closings'] }}</td>
                    <td class="text-center">{{ $row['closing_rate'] }}%</td>
                    <td class="text-right font-mono" style="font-weight: bold;">
                        Rp {{ number_format($row['total_revenue'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">{{ $row['avg_duration_minutes'] }} mnt</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 10px; font-style: italic;">
                        Belum ada aktivitas giliran sales pada tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian 2: Riwayat Sesi Giliran -->
    @php
        $allDayLogs = \App\Models\SalesDutyLog::with(['user.employee', 'sale', 'creator'])
            ->where('branch_id', $currentBranch?->id)
            ->whereDate('started_at', $selectedDate)
            ->orderBy('started_at', 'asc')
            ->get();
    @endphp

    <div style="font-weight: bold; font-size: 9.5pt; text-transform: uppercase; margin-top: 15px; margin-bottom: 4px;">
        II. Riwayat Detail Giliran Melayani (Total: {{ $allDayLogs->count() }} Sesi)
    </div>
    <table class="erp-print-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Waktu</th>
                <th class="text-center">Durasi</th>
                <th>Sales Rep</th>
                <th class="text-center">Status</th>
                <th>No. Faktur (Closing)</th>
                <th class="text-right">Nilai Belanja</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($allDayLogs as $i => $log)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $log->started_at->format('H:i') }} - {{ $log->ended_at ? $log->ended_at->format('H:i') : 'Aktif' }}</td>
                    <td class="text-center">{{ $log->duration_text }}</td>
                    <td style="font-weight: bold;">{{ $log->user->name ?? '-' }}</td>
                    <td class="text-center">{{ ucfirst($log->status) }}</td>
                    <td>{{ $log->sale?->invoice_number ?? '-' }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">
                        @if ($log->closing_amount > 0)
                            Rp {{ number_format($log->closing_amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $log->creator?->name ?? 'Kasir' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 10px; font-style: italic;">
                        Tidak ada catatan log pada tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures Block -->
    <div style="margin-top: 30px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; font-size: 8.5pt;">
            <tr style="text-align: center;">
                <td style="width: 33%; border: none;">
                    Dibuat oleh,<br><br><br><br>
                    <strong>( {{ auth()->user()?->name ?: 'Admin Kasir' }} )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Staf Operasional POS</span>
                </td>
                <td style="width: 33%; border: none;">
                    Diperiksa oleh,<br><br><br><br>
                    <strong>( __________________ )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Supervisor Toko</span>
                </td>
                <td style="width: 33%; border: none;">
                    Disetujui oleh,<br><br><br><br>
                    <strong>( __________________ )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Store Manager / Owner</span>
                </td>
            </tr>
        </table>
    </div>
</div>
