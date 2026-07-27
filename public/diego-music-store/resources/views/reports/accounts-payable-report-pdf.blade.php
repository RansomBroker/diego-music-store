<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hutang Usaha - Per {{ $data['as_of_date'] }}</title>
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
            @if($data['mode'] === 'summary_supplier')
                LAPORAN REKAPITULASI HUTANG PER SUPPLIER
            @elseif($data['mode'] === 'aging')
                LAPORAN ANALISIS UMUR HUTANG (AP AGING MATRIX)
            @else
                LAPORAN RINCIAN FAKTUR HUTANG BELUM LUNAS
            @endif
        </div>
        <div class="report-meta">
            Per Tanggal: <strong>{{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Supplier: <strong>{{ $data['supplier_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'summary_supplier')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Supplier</th>
                    <th style="width: 15%;">Telepon</th>
                    <th style="width: 15%;" class="text-center">Jumlah Faktur</th>
                    <th style="width: 13%;" class="text-right">Total Pembelian (Rp)</th>
                    <th style="width: 13%;" class="text-right">Total Terbayar (Rp)</th>
                    <th style="width: 14%;" class="text-right">Sisa Hutang (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['suppliers'] as $sup)
                    <tr>
                        <td class="font-bold">{{ $sup['supplier_name'] }}</td>
                        <td class="font-mono">{{ $sup['supplier_phone'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($sup['count_invoices'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($sup['grand_total'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($sup['paid_amount'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($sup['unpaid_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada saldo hutang supplier pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($data['mode'] === 'aging')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Supplier</th>
                    <th style="width: 13%;" class="text-right">Belum Jt Tempo (Rp)</th>
                    <th style="width: 12%;" class="text-right">1-30 Hari (Rp)</th>
                    <th style="width: 12%;" class="text-right">31-60 Hari (Rp)</th>
                    <th style="width: 12%;" class="text-right">61-90 Hari (Rp)</th>
                    <th style="width: 12%;" class="text-right">>90 Hari (Rp)</th>
                    <th style="width: 14%;" class="text-right">Total Hutang (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['suppliers'] as $sup)
                    <tr>
                        <td class="font-bold">{{ $sup['supplier_name'] }}</td>
                        <td class="text-right font-mono">{{ $sup['current'] > 0 ? number_format($sup['current'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $sup['aging_1_30'] > 0 ? number_format($sup['aging_1_30'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $sup['aging_31_60'] > 0 ? number_format($sup['aging_31_60'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $sup['aging_61_90'] > 0 ? number_format($sup['aging_61_90'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ $sup['aging_90_plus'] > 0 ? number_format($sup['aging_90_plus'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($sup['unpaid_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada saldo hutang supplier pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">No. Faktur</th>
                    <th style="width: 10%;">Tgl Beli</th>
                    <th style="width: 10%;">Jt Tempo</th>
                    <th style="width: 24%;">Supplier</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;" class="text-right">Total (Rp)</th>
                    <th style="width: 10%;" class="text-right">Terbayar (Rp)</th>
                    <th style="width: 10%;" class="text-right">Sisa (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['invoices'] as $inv)
                    <tr>
                        <td class="font-mono font-bold">{{ $inv['transaction_no'] }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($inv['date'])->format('d/m/Y') }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($inv['due_date'])->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $inv['supplier_name'] }}</td>
                        <td>{{ $inv['is_overdue'] ? ('Jatuh Tempo (' . $inv['overdue_days'] . ' hr)') : 'Lancar' }}</td>
                        <td class="text-right font-mono">{{ number_format($inv['grand_total'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($inv['paid_amount'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($inv['unpaid_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada faktur hutang belum lunas pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN AP: {{ number_format($data['total_invoices'], 0, ',', '.') }} Faktur &bull; Belum Jt Tempo: Rp {{ number_format($data['total_current'], 0, ',', '.') }} &bull; Sudah Jt Tempo: Rp {{ number_format($data['total_overdue'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">TOTAL SISA HUTANG: Rp {{ number_format($data['total_unpaid'], 0, ',', '.') }}</td>
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
