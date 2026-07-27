<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Bank (Bank Ledger) - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
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
        .account-header {
            background-color: #111827;
            color: #ffffff;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
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
            background-color: #f3f4f6;
            border: 1px solid #111827;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9.5px;
            margin-top: 4px;
            margin-bottom: 15px;
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
        <div class="report-title">LAPORAN BUKU BANK (BANK LEDGER REPORT)</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @foreach($data['ledgers'] as $ledger)
        <div class="account-header">
            REKENING BANK: {{ $ledger['account_code'] }} - {{ $ledger['account_name'] }}
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 20%;">No. Bukti</th>
                    <th style="width: 35%;">Keterangan Transaksi</th>
                    <th style="width: 10%;" class="text-right">Uang Masuk (Rp)</th>
                    <th style="width: 10%;" class="text-right">Uang Keluar (Rp)</th>
                    <th style="width: 10%;" class="text-right">Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color: #f9fafb; font-weight: bold;">
                    <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }}</td>
                    <td class="font-mono">-</td>
                    <td>SALDO AWAL REKENING BANK (BEGINNING BALANCE)</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono">{{ number_format($ledger['beginning_balance'], 0, ',', '.') }}</td>
                </tr>

                @forelse($ledger['transactions'] as $tx)
                    <tr>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($tx['date'])->format('d/m/Y') }}</td>
                        <td class="font-mono">{{ $tx['entry_no'] }}</td>
                        <td>{{ $tx['description'] }}</td>
                        <td class="text-right font-mono">{{ $tx['debit'] > 0 ? number_format($tx['debit'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $tx['credit'] > 0 ? number_format($tx['credit'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($tx['running_balance'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-right" style="color: #6b7280; font-style: italic;">Tidak ada mutasi pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="subtotal-box">
            <table style="width: 100%;">
                <tr>
                    <td>TOTAL MUTASI REKENING: Masuk {{ number_format($ledger['total_in'], 0, ',', '.') }} | Keluar {{ number_format($ledger['total_out'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono">SALDO AKHIR BANK: Rp {{ number_format($ledger['ending_balance'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endforeach

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
