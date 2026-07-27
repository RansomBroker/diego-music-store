<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Daftar Stok Barang - Per {{ now()->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
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
            font-size: 8px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #111827;
            padding: 4px 4px;
            text-align: left;
            background-color: #f3f4f6;
        }
        table.data-table td {
            padding: 3.5px 4px;
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
        <div class="report-title">LAPORAN DAFTAR STOK BARANG & VALUASI PERSEDIAAN</div>
        <div class="report-meta">
            Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Kategori: <strong>{{ $data['category'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 11%;">SKU / Barcode</th>
                <th style="width: 20%;">Nama Produk & Variasi</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 6%;" class="text-center">Stok</th>
                <th style="width: 5%;" class="text-center">Min</th>
                <th style="width: 5%;" class="text-center">Satuan</th>
                <th style="width: 9%;" class="text-center">Status</th>
                <th style="width: 6%;" class="text-center">Diskon</th>
                <th style="width: 5%;" class="text-center">PPN</th>
                <th style="width: 8%;" class="text-right">Harga Beli (Rp)</th>
                <th style="width: 8%;" class="text-right">Harga Jual (Rp)</th>
                <th style="width: 7%;" class="text-right">Nilai Aset (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="font-mono font-bold">{{ $row['sku'] }}</td>
                    <td class="font-bold">{{ $row['full_name'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td class="text-center font-mono font-bold">{{ number_format($row['stock'], 0, ',', '.') }}</td>
                    <td class="text-center font-mono" style="color: #6b7280;">{{ number_format($row['min_stock'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['unit'] }}</td>
                    <td class="text-center font-bold">{{ $row['status_label'] }}</td>
                    <td class="text-center font-mono">{{ $row['discount'] }}</td>
                    <td class="text-center font-mono">{{ $row['tax'] }}</td>
                    <td class="text-right font-mono">{{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($row['retail_price'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($row['valuation'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada data stok barang yang sesuai dengan filter</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN STOK: {{ number_format($data['total_variants'], 0, ',', '.') }} SKU &bull; Total Qty: {{ number_format($data['total_physical_qty'], 0, ',', '.') }} Unit &bull; Stok Rendah: {{ number_format($data['total_low_stock_count'], 0, ',', '.') }} &bull; Stok Habis: {{ number_format($data['total_out_of_stock_count'], 0, ',', '.') }}</td>
                <td class="text-right font-mono">GRAND TOTAL NILAI ASET: Rp {{ number_format($data['grand_total_valuation'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Staf Gudang / Persediaan)</div>
            </td>
            <td>
                <div class="sig-line">Diperiksa Oleh (Manager Logistics/Inventory)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
