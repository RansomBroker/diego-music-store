<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jurnal Umum (Journal Report) - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #111827;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
            color: #111827;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 9px;
            color: #374151;
        }
        .entry-header {
            background-color: #111827;
            color: #ffffff;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 15px;
            margin-bottom: 2px;
        }
        .entry-desc {
            background-color: #f3f4f6;
            padding: 4px 8px;
            font-size: 9px;
            color: #374151;
            border-left: 3px solid #111827;
            margin-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.data-table th {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #111827;
            border-bottom: 1.5px solid #111827;
            padding: 3px 4px;
            text-align: left;
        }
        table.data-table td {
            padding: 3px 4px;
            border-bottom: 1px solid #e5e7eb;
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
        .subtotal-box {
            background-color: #f9fafb;
            border: 1px solid #111827;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            margin-top: 2px;
            margin-bottom: 12px;
        }
        .grand-total-box {
            background-color: #f3f4f6;
            border: 2px solid #111827;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 10.5px;
            margin-top: 15px;
        }
        .signatures {
            margin-top: 35px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 60px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 9px;
            color: #111827;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">LAPORAN JURNAL UMUM (JOURNAL REPORT)</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Status: <strong>{{ strtoupper($data['status']) }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @foreach($data['entries'] as $entry)
        <div class="entry-header">
            NO BUKTI: {{ $entry['entry_no'] }} | Tanggal: {{ \Illuminate\Support\Carbon::parse($entry['date'])->format('d/m/Y') }} | Status: {{ strtoupper($entry['status']) }} | Cabang: {{ $entry['branch_name'] }}
        </div>
        <div class="entry-desc">
            Deskripsi: {{ $entry['description'] ?: '-' }}
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Kode Akun</th>
                    <th style="width: 42%;">Nama Akun</th>
                    <th style="width: 20%;">Memo</th>
                    <th style="width: 10%;" class="text-right">Debit (Rp)</th>
                    <th style="width: 10%;" class="text-right">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry['items'] as $item)
                    <tr>
                        <td class="font-mono">{{ $item['account_code'] }}</td>
                        <td class="font-bold">{{ $item['account_name'] }}</td>
                        <td>{{ $item['notes'] ?: '-' }}</td>
                        <td class="text-right font-mono">{{ $item['debit'] > 0 ? number_format($item['debit'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $item['credit'] > 0 ? number_format($item['credit'], 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="subtotal-box">
            <table style="width: 100%;">
                <tr>
                    <td>TOTAL BUKTI JURNAL</td>
                    <td class="text-right font-mono">Debit: {{ number_format($entry['total_debit'], 0, ',', '.') }} | Kredit: {{ number_format($entry['total_credit'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="grand-total-box">
        <table style="width: 100%;">
            <tr>
                <td>GRAND TOTAL DEBIT & KREDIT ({{ $data['total_entries'] }} BUKTI JURNAL)</td>
                <td class="text-right font-mono">Debit: {{ number_format($data['grand_total_debit'], 0, ',', '.') }} | Kredit: {{ number_format($data['grand_total_credit'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

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
