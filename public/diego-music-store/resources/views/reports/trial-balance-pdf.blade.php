<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca Saldo (Trial Balance 6-Kolom) - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #111827;
            margin: 0;
            padding: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #111827;
            padding-bottom: 6px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
            color: #111827;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 3px;
        }
        .report-meta {
            font-size: 8.5px;
            color: #374151;
        }
        .status-badge {
            display: block;
            padding: 4px 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            background-color: #f3f4f6;
            color: #111827;
            border: 1px solid #111827;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.data-table th {
            font-size: 8px;
            text-transform: uppercase;
            color: #111827;
            border: 1px solid #111827;
            padding: 4px 3px;
            text-align: center;
            background-color: #f3f4f6;
        }
        table.data-table td {
            padding: 3px 4px;
            border: 1px solid #d1d5db;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .pl-detail {
            padding-left: 10px !important;
        }
        .total-row td {
            background-color: #f3f4f6;
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
            font-weight: bold;
            font-size: 9px;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 50px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 8.5px;
            color: #111827;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">LAPORAN NERACA SALDO (TRIAL BALANCE 6-KOLOM)</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="status-badge">
        STATUS KESEIMBANGAN: {{ $data['is_balanced'] ? 'SEIMBANG (BALANCED 100%)' : 'OUT OF BALANCE (SELISIH Rp ' . number_format($data['difference'], 0, ',', '.') . ')' }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;" rowspan="2">Kode</th>
                <th style="width: 34%;" rowspan="2">Nama Akun / Kategori</th>
                <th colspan="2">Saldo Awal (Rp)</th>
                <th colspan="2">Mutasi Periode (Rp)</th>
                <th colspan="2">Saldo Akhir (Rp)</th>
            </tr>
            <tr>
                <th style="width: 9%;">Debit</th>
                <th style="width: 9%;">Kredit</th>
                <th style="width: 9%;">Debit</th>
                <th style="width: 9%;">Kredit</th>
                <th style="width: 9%;">Debit</th>
                <th style="width: 9%;">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                    <td class="font-mono">{{ $item['code'] }}</td>
                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                    <td class="text-right font-mono">{{ $item['beginning_debit'] > 0 ? number_format($item['beginning_debit'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono">{{ $item['beginning_credit'] > 0 ? number_format($item['beginning_credit'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono">{{ $item['period_debit'] > 0 ? number_format($item['period_debit'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono">{{ $item['period_credit'] > 0 ? number_format($item['period_credit'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono font-bold">{{ $item['ending_debit'] > 0 ? number_format($item['ending_debit'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono font-bold">{{ $item['ending_credit'] > 0 ? number_format($item['ending_credit'], 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #6b7280;">Tidak ada akun untuk ditampilkan</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="font-bold">TOTAL GRAND TOTAL</td>
                <td class="text-right font-mono">{{ number_format($data['total_beginning_debit'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($data['total_beginning_credit'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($data['total_period_debit'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($data['total_period_credit'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">{{ number_format($data['total_ending_debit'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">{{ number_format($data['total_ending_credit'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Staf Akuntansi)</div>
            </td>
            <td>
                <div class="sig-line">Diperiksa Oleh (Manager Finance)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
