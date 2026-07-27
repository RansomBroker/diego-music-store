<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pelunasan Hutang Supplier - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
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
            LAPORAN PELUNASAN HUTANG SUPPLIER {{ strtoupper($data['mode']) === 'DETAIL' ? '(RINCIAN ALOKASI FAKTUR)' : '(RINGKASAN BUKTI BAYAR)' }}
        </div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Supplier: <strong>{{ $data['supplier_name'] }}</strong>
            &bull; Akun Kas/Bank: <strong>{{ $data['account_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'detail')
        @foreach($data['payments'] as $pay)
            <div style="background-color: #111827; color: #ffffff; padding: 4px 8px; font-weight: bold; font-size: 9.5px; margin-top: 8px;">
                BUKTI: {{ $pay['payment_no'] }} (Ref: {{ $pay['payment_reference'] }}) &bull; Supplier: {{ $pay['supplier_name'] }} &bull; Tgl: {{ \Illuminate\Support\Carbon::parse($pay['payment_date'])->format('d/m/Y') }} &bull; Total Bayar: Rp {{ number_format($pay['total_amount'], 0, ',', '.') }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">No. Faktur Beli</th>
                        <th style="width: 15%;">Inv Supplier</th>
                        <th style="width: 12%;">Tgl Faktur</th>
                        <th style="width: 17%;" class="text-right">Total Faktur (Rp)</th>
                        <th style="width: 18%;" class="text-right">Hutang Sblm Bayar (Rp)</th>
                        <th style="width: 18%;" class="text-right">Nominal Dilunasi (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pay['items'] as $item)
                        <tr>
                            <td class="font-mono font-bold">{{ $item['transaction_no'] }}</td>
                            <td class="font-mono">{{ $item['invoice_number'] }}</td>
                            <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($item['purchase_date'])->format('d/m/Y') }}</td>
                            <td class="text-right font-mono">{{ number_format($item['grand_total'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono">{{ number_format($item['amount_due'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($item['amount_paid'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">No. Bukti</th>
                    <th style="width: 9%;">Tanggal</th>
                    <th style="width: 22%;">Supplier</th>
                    <th style="width: 9%;">Metode</th>
                    <th style="width: 16%;">Akun Kas/Bank</th>
                    <th style="width: 12%;">No. Ref</th>
                    <th style="width: 8%;" class="text-center">Faktur</th>
                    <th style="width: 10%;" class="text-right">Total Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['payments'] as $pay)
                    <tr>
                        <td class="font-mono font-bold">{{ $pay['payment_no'] }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($pay['payment_date'])->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $pay['supplier_name'] }}</td>
                        <td>{{ $pay['payment_method'] }}</td>
                        <td>{{ $pay['account_name'] }}</td>
                        <td class="font-mono">{{ $pay['payment_reference'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($pay['items_count'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($pay['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada transaksi pelunasan hutang supplier pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN PELUNASAN: {{ number_format($data['total_payments_count'], 0, ',', '.') }} Bukti Pelunasan &bull; {{ number_format($data['total_invoices_paid'], 0, ',', '.') }} Faktur Dilunasi &bull; {{ number_format($data['total_suppliers_paid'], 0, ',', '.') }} Supplier Terbayar</td>
                <td class="text-right font-mono">GRAND TOTAL PELUNASAN: Rp {{ number_format($data['total_amount_paid'], 0, ',', '.') }}</td>
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
