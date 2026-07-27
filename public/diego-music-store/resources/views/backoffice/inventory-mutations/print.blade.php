<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Mutasi Barang - {{ $mutation->mutation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            font-style: italic;
        }
        .table-meta {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-meta td {
            vertical-align: top;
            padding: 3px 0;
        }
        .table-meta td.label {
            width: 120px;
        }
        .table-meta td.colon {
            width: 10px;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table-items th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
        }
        .table-items td {
            padding: 8px 5px;
            border-bottom: 1px dashed #ccc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            width: 30%;
            float: left;
            text-align: center;
        }
        .signature-space {
            height: 60px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 85%;
            margin: 0 auto 5px auto;
        }
        .clear {
            clear: both;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 15px; font-size: 14px; font-weight: bold; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;">Cetak Dokumen</button>
    </div>

    <table class="header">
        <tr>
            <td class="header-title">SURAT MUTASI / TRANSFER BARANG</td>
            <td class="header-subtitle">Diego Music Store</td>
        </tr>
    </table>

    <table class="table-meta">
        <tr>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">Nomor Mutasi</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $mutation->mutation_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Mutasi</td>
                        <td class="colon">:</td>
                        <td>{{ \Carbon\Carbon::parse($mutation->mutation_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status Mutasi</td>
                        <td class="colon">:</td>
                        <td>
                            <strong style="text-transform: uppercase;">
                                @if($mutation->status === 'draft')
                                    Draft
                                @elseif($mutation->status === 'transit')
                                    In-Transit (Pengiriman)
                                @elseif($mutation->status === 'received')
                                    Received (Selesai/Diterima)
                                @else
                                    {{ $mutation->status }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">Cabang Pengirim</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $mutation->senderBranch?->name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Cabang Penerima</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $mutation->receiverBranch?->name ?? '-' }}</strong></td>
                    </tr>
                    @if($mutation->notes)
                    <tr>
                        <td class="label">Catatan / Memo</td>
                        <td class="colon">:</td>
                        <td>{{ $mutation->notes }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">No</th>
                <th style="width: 150px;">SKU</th>
                <th>Nama Produk / Varian</th>
                <th style="width: 80px;" class="text-center">Unit</th>
                <th style="width: 90px;" class="text-right">Jumlah Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mutation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->productVariant?->sku ?? '-' }}</strong></td>
                    <td>
                        {{ $item->productVariant?->product?->name ?? 'Produk' }}
                        @if($item->productVariant?->name)
                            - {{ $item->productVariant->name }}
                        @endif
                    </td>
                    <td class="text-center">{{ $item->productVariant?->product?->unit?->name ?? 'Pcs' }}</td>
                    <td class="text-right"><strong>{{ number_format($item->quantity, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-box">
            <div>Pengirim (Cabang Asal)</div>
            <div class="signature-space"></div>
            <div class="signature-line"></div>
            <div>( ..................................... )</div>
        </div>
        <div class="signature-box">
            <div>Petugas Pengirim / Driver</div>
            <div class="signature-space"></div>
            <div class="signature-line"></div>
            <div>( ..................................... )</div>
        </div>
        <div class="signature-box" style="float: right;">
            <div>Penerima (Cabang Tujuan)</div>
            <div class="signature-space"></div>
            <div class="signature-line"></div>
            <div>( ..................................... )</div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
