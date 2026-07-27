<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Barang & Kartu Stok - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
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
            LAPORAN MUTASI BARANG & KARTU STOK {{ strtoupper($data['mode']) === 'SUMMARY' ? '(REKAPITULASI PRODUK)' : '(MOVEMENT LOG)' }}
        </div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Kategori: <strong>{{ $data['category'] }}</strong>
            &bull; Jenis: <strong>{{ strtoupper($data['type_filter']) }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'summary')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">SKU</th>
                    <th style="width: 30%;">Nama Produk & Variasi</th>
                    <th style="width: 14%;">Kategori</th>
                    <th style="width: 9%;" class="text-center">Qty Masuk (+)</th>
                    <th style="width: 9%;" class="text-center">Qty Keluar (-)</th>
                    <th style="width: 9%;" class="text-center">Net Mutasi</th>
                    <th style="width: 7%;" class="text-center">Satuan</th>
                    <th style="width: 10%;" class="text-right">Total Valuasi (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['summary_rows'] as $row)
                    <tr>
                        <td class="font-mono font-bold">{{ $row['sku'] }}</td>
                        <td class="font-bold">{{ $row['full_name'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td class="text-center font-mono font-bold">+{{ number_format($row['in_qty'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">-{{ number_format($row['out_qty'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ $row['net_qty'] > 0 ? '+' : '' }}{{ number_format($row['net_qty'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row['unit'] }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($row['total_value'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada data mutasi barang pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Tgl & Waktu</th>
                    <th style="width: 16%;">No. Referensi</th>
                    <th style="width: 12%;">SKU</th>
                    <th style="width: 24%;">Nama Produk & Variasi</th>
                    <th style="width: 12%;">Cabang</th>
                    <th style="width: 6%;" class="text-center">Tipe</th>
                    <th style="width: 6%;" class="text-center">Qty</th>
                    <th style="width: 10%;" class="text-right">Valuasi (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['date'] }}</td>
                        <td class="font-mono font-bold">{{ $row['ref_label'] }}</td>
                        <td class="font-mono">{{ $row['sku'] }}</td>
                        <td class="font-bold">{{ $row['full_name'] }}</td>
                        <td>{{ $row['branch_name'] }}</td>
                        <td class="text-center font-bold">{{ $row['type'] }}</td>
                        <td class="text-center font-mono font-bold">{{ $row['type'] === 'IN' ? '+' : '-' }}{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($row['total_value'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada transaksi mutasi barang pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN MUTASI: {{ number_format($data['total_transactions'], 0, ',', '.') }} Transaksi &bull; Total Masuk: +{{ number_format($data['total_in_qty'], 0, ',', '.') }} Unit &bull; Total Keluar: -{{ number_format($data['total_out_qty'], 0, ',', '.') }} Unit &bull; Net Qty: {{ $data['total_net_qty'] > 0 ? '+' : '' }}{{ number_format($data['total_net_qty'], 0, ',', '.') }} Unit</td>
                <td class="text-right font-mono">GRAND TOTAL VALUASI: Rp {{ number_format($data['grand_total_valuation'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Staf Logistics / Gudang)</div>
            </td>
            <td>
                <div class="sig-line">Diperiksa Oleh (Supervisor Inventori)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
