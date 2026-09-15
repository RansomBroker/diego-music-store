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
            . { font-family: 'Courier New', Courier, monospace; }
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
            <div style="text-align: right; font-size: 8pt;" class="">
                <div>TGL CETAK: {{ now()->format('d/m/Y H:i:s') }}</div>
                <div>PETUGAS: {{ strtoupper(auth()->user()?->name ?: 'ADMIN') }}</div>
                <div>STATUS: DOKUMEN RESMI ERP</div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div style="text-align: center; margin-bottom: 15px;">
        <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline;">
            LAPORAN PELUNASAN PIUTANG USAHA
        </h2>
        <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
            PERIODE: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'AWAL' }} S/D {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'SEKARANG' }}
        </div>
    </div>

    <!-- Summary -->
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
        <tr>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 30%;">Total Pelunasan Diterima</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class=" text-right">Rp {{ number_format($reportData['total_settled'] ?? 0, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 30%;">Jumlah Transaksi Pelunasan</td>
            <td style="border: 1px solid #000; padding: 5px;" class=" text-right">{{ $reportData['total_count'] ?? 0 }} Transaksi</td>
        </tr>
    </table>

    <!-- Grid -->
    <table class="erp-print-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">NO</th>
                <th style="width: 120px;">NO BUKTI / JURNAL</th>
                <th style="width: 80px;">TGL PELUNASAN</th>
                <th>NAMA PELANGGAN</th>
                <th style="width: 110px;">NO INVOICE TERKAIT</th>
                <th style="width: 130px;">AKUN MASUK (KAS/BANK)</th>
                <th style="width: 110px;" class="text-right">JUMLAH PELUNASAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData['settlements'] ?? [] as $idx => $st)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class=" font-bold">{{ $st['entry_no'] }}</td>
                    <td>{{ $st['date'] }}</td>
                    <td style="font-weight: bold;">{{ $st['customer_name'] }}</td>
                    <td class="">{{ $st['invoice_no'] }}</td>
                    <td>{{ $st['account_name'] }}</td>
                    <td class="text-right  font-bold">Rp {{ number_format($st['amount'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; font-style: italic;">Belum ada transaksi pelunasan piutang pada periode ini.</td>
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
