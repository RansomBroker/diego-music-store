<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kas & Bank - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
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
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .report-meta {
            font-size: 9px;
            color: #374151;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            font-size: 8.5px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #111827;
            padding: 4px 5px;
            text-align: left;
            background-color: #f3f4f6;
        }
        table.data-table td {
            padding: 4px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .subtotal-box {
            background-color: #f3f4f6;
            border: 1.5px solid #111827;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 9.5px;
            margin-top: 10px;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 55px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 3px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">
            LAPORAN KAS & BANK {{ strtoupper($data['mode']) === 'SUMMARY_CATEGORY' ? '(REKAPITULASI KATEGORI)' : '(BUKU KAS BERJALAN)' }}
        </div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Akun Kas/Bank: <strong>{{ $data['account_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'summary_category')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Kategori Transaksi / Lawan Akun</th>
                    <th style="width: 18%;" class="text-right">Total Masuk (Inflow) (Rp)</th>
                    <th style="width: 18%;" class="text-right">Total Keluar (Outflow) (Rp)</th>
                    <th style="width: 19%;" class="text-right">Selisih Net (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['categories'] as $cat)
                    <tr>
                        <td class="font-bold">{{ $cat['category_name'] }}</td>
                        <td class="text-right font-mono">{{ $cat['inflow'] > 0 ? number_format($cat['inflow'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $cat['outflow'] > 0 ? number_format($cat['outflow'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($cat['net_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada mutasi kas & bank pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">No. Jurnal</th>
                    <th style="width: 9%;">Tanggal</th>
                    <th style="width: 16%;">Akun Kas/Bank</th>
                    <th style="width: 18%;">Lawan Akun</th>
                    <th style="width: 17%;">Keterangan</th>
                    <th style="width: 8%;" class="text-right">Masuk (Rp)</th>
                    <th style="width: 8%;" class="text-right">Keluar (Rp)</th>
                    <th style="width: 10%;" class="text-right">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color: #f9fafb; font-weight: bold;">
                    <td class="font-mono" style="color: #6b7280;">-</td>
                    <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}</td>
                    <td>{{ $data['account_name'] }}</td>
                    <td>SALDO AWAL PERIODE</td>
                    <td>Saldo awal kas/bank sebelum periode</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono font-bold">{{ number_format($data['initial_balance'], 0, ',', '.') }}</td>
                </tr>

                @forelse($data['rows'] as $row)
                    <tr>
                        <td class="font-mono font-bold">{{ $row['entry_no'] }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td class="font-mono">{{ $row['account_name'] }}</td>
                        <td class="font-bold">{{ $row['opposing_account'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td class="text-right font-mono font-bold">{{ $row['inflow'] > 0 ? number_format($row['inflow'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $row['outflow'] > 0 ? number_format($row['outflow'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($row['running_balance'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada mutasi transaksi kas & bank pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>SALDO AWAL: Rp {{ number_format($data['initial_balance'], 0, ',', '.') }} &bull; MASUK: Rp {{ number_format($data['total_inflow'], 0, ',', '.') }} &bull; KELUAR: Rp {{ number_format($data['total_outflow'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">SALDO AKHIR: Rp {{ number_format($data['ending_balance'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Kasir / Akuntansi)</div>
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
