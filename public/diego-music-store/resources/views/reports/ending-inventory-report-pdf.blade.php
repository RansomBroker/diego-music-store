<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Persediaan Akhir - Per {{ $data['as_of_date'] }}</title>
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
            LAPORAN PERSEDIAAN AKHIR {{ strtoupper($data['mode']) === 'SUMMARY_CATEGORY' ? '(REKAPITULASI KATEGORI)' : '(RINCIAN DETAIL PRODUK)' }}
        </div>
        <div class="report-meta">
            Per Tanggal Cut-Off: <strong>{{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Kategori: <strong>{{ $data['category'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'summary_category')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Kategori Produk</th>
                    <th style="width: 15%;" class="text-center">Jumlah SKU</th>
                    <th style="width: 20%;" class="text-center">Total Qty Persediaan Akhir</th>
                    <th style="width: 20%;" class="text-right">Total Nilai Aset (HPP) (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['categories'] as $cat)
                    <tr>
                        <td class="font-bold">{{ $cat['category_name'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($cat['variant_count'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($cat['total_qty'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($cat['total_valuation'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada data persediaan akhir pada tanggal cut-off ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">SKU / Barcode</th>
                    <th style="width: 26%;">Nama Produk & Variasi</th>
                    <th style="width: 14%;">Kategori</th>
                    <th style="width: 10%;">Merk</th>
                    <th style="width: 9%;" class="text-center">Qty Akhir</th>
                    <th style="width: 7%;" class="text-center">Satuan</th>
                    <th style="width: 10%;" class="text-right">Harga Beli (Rp)</th>
                    <th style="width: 10%;" class="text-right">Nilai Aset (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $row)
                    <tr>
                        <td class="font-mono font-bold">{{ $row['sku'] }}</td>
                        <td class="font-bold">{{ $row['full_name'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['brand'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($row['ending_qty'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row['unit'] }}</td>
                        <td class="text-right font-mono">{{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($row['valuation'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada data persediaan akhir yang sesuai dengan filter</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN PERSEDIAAN AKHIR: {{ number_format($data['total_variants'], 0, ',', '.') }} SKU &bull; Total Qty Akhir: {{ number_format($data['total_ending_qty'], 0, ',', '.') }} Unit</td>
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
                <div class="sig-line">Diperiksa Oleh (Manager Logistics/Finance)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
