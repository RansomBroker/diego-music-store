<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
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
        <div class="report-title">LAPORAN PEMBELIAN {{ strtoupper($data['mode']) === 'DETAIL' ? '(RINCIAN DETAIL PRODUK)' : '(RINGKASAN FAKTUR)' }}</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Supplier: <strong>{{ $data['supplier_name'] }}</strong>
            &bull; Tipe: <strong>{{ strtoupper($data['purchase_type']) }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'detail')
        @foreach($data['purchases'] as $p)
            <div style="background-color: #111827; color: #ffffff; padding: 4px 8px; font-weight: bold; font-size: 9.5px; margin-top: 8px;">
                FAKTUR: {{ $p['transaction_no'] }} (Inv: {{ $p['invoice_number'] }}) &bull; Supplier: {{ $p['supplier_name'] }} &bull; Tgl: {{ \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y') }} &bull; Total: Rp {{ number_format($p['grand_total'], 0, ',', '.') }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 40%;">Nama Produk & Variasi</th>
                        <th style="width: 10%;" class="text-center">Qty</th>
                        <th style="width: 10%;" class="text-center">Satuan</th>
                        <th style="width: 15%;" class="text-right">Harga Satuan (Rp)</th>
                        <th style="width: 10%;" class="text-right">Subtotal Item (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($p['items'] as $item)
                        <tr>
                            <td class="font-mono">{{ $item['sku'] }}</td>
                            <td class="font-bold">{{ $item['product_name'] }}</td>
                            <td class="text-center font-mono font-bold">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ $item['unit'] }}</td>
                            <td class="text-right font-mono">{{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 13%;">No. Faktur</th>
                    <th style="width: 9%;">Tanggal</th>
                    <th style="width: 22%;">Supplier</th>
                    <th style="width: 8%;">Tipe</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;" class="text-right">Subtotal (Rp)</th>
                    <th style="width: 8%;" class="text-right">Diskon (Rp)</th>
                    <th style="width: 9%;" class="text-right">Pajak/Ongkir (Rp)</th>
                    <th style="width: 10%;" class="text-right">Grand Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['purchases'] as $p)
                    <tr>
                        <td class="font-mono font-bold">{{ $p['transaction_no'] }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($p['date'])->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $p['supplier_name'] }}</td>
                        <td>{{ $p['purchase_type'] }}</td>
                        <td>{{ $p['payment_status'] }}</td>
                        <td class="text-right font-mono">{{ number_format($p['subtotal'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ $p['discount'] > 0 ? number_format($p['discount'], 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">{{ number_format($p['tax'] + $p['shipping'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($p['grand_total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada transaksi pembelian pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN TOTAL PEMBELIAN: {{ number_format($data['total_transactions'], 0, ',', '.') }} Transaksi ({{ number_format($data['total_qty'], 0, ',', '.') }} Qty Produk)</td>
                <td class="text-right font-mono">GRAND TOTAL: Rp {{ number_format($data['total_grand_total'], 0, ',', '.') }} | TERBAYAR: Rp {{ number_format($data['total_paid'], 0, ',', '.') }} | SISA HUTANG: Rp {{ number_format($data['total_unpaid'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Staf Pembelian)</div>
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
