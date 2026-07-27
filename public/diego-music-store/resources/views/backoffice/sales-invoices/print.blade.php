<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Penjualan - {{ $invoice->invoice_number }}</title>
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
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 16px;
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
            width: 110px;
        }
        .table-meta td.colon {
            width: 10px;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .footer {
            width: 100%;
            margin-top: 20px;
        }
        .footer-left {
            width: 55%;
            float: left;
        }
        .footer-right {
            width: 40%;
            float: right;
        }
        .clear {
            clear: both;
        }
        .spelled-out {
            font-style: italic;
            font-weight: bold;
            margin-bottom: 20px;
            border: 1px solid #000;
            padding: 10px;
            background-color: #fafafa;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }
        .signature-box.right {
            float: right;
        }
        .signature-space {
            height: 60px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .total-summary {
            width: 100%;
            border-collapse: collapse;
        }
        .total-summary td {
            padding: 4px;
        }
        .total-summary tr.grand-total td {
            border-top: 1px solid #000;
            border-bottom: 2px double #000;
            font-weight: bold;
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
        <button onclick="window.print()" style="padding: 8px 15px; font-size: 14px; font-weight: bold; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;">Cetak Faktur</button>
    </div>

    <table class="header">
        <tr>
            <td class="header-title">FAKTUR PENJUALAN</td>
            <td class="header-subtitle">Diego Music Store</td>
        </tr>
    </table>

    <table class="table-meta">
        <tr>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">No. Faktur</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Tgl Faktur</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                    </tr>
                    @if($invoice->due_date)
                    <tr>
                        <td class="label">Tgl Jatuh Tempo</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Jenis Bayar</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $invoice->payment_type }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Cabang</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->branch?->name ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">Kepada Yth.</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $invoice->customer?->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Telepon</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->customer?->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->customer?->address ?? '-' }}</td>
                    </tr>
                    @if($invoice->salesQuotation)
                    <tr>
                        <td class="label">Rujukan SQ</td>
                        <td class="colon">:</td>
                        <td>{{ $invoice->salesQuotation->quotation_number }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th>Deskripsi Barang</th>
                <th class="text-right" style="width: 70px;">Qty</th>
                <th style="width: 60px;">Satuan</th>
                <th class="text-right" style="width: 120px;">Harga @</th>
                <th class="text-right" style="width: 110px;">Diskon</th>
                <th class="text-right" style="width: 130px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>[{{ $item->productVariant?->sku }}]</strong>
                        {{ $item->productVariant?->product?->name }}
                        @if($item->productVariant?->name)
                            - {{ $item->productVariant->name }}
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td>{{ $item->productVariant?->product?->unit?->name ?? 'Pcs' }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-left">
            <div style="margin-bottom: 5px; font-weight: bold;">Terbilang:</div>
            <div class="spelled-out">
                {{ App\Helpers\TerbilangHelper::convert($invoice->grand_total) }} Rupiah
            </div>

            <div class="signatures">
                <div class="signature-box">
                    <div>Penerima / Pelanggan,</div>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    <div>( {{ $invoice->customer?->name }} )</div>
                </div>
                <div class="signature-box right">
                    <div>Hormat Kami,</div>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    <div>Diego Music Store</div>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="footer-right">
            <table class="total-summary">
                <tr>
                    <td style="font-size: 11px;">Subtotal:</td>
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td style="font-size: 11px;">Diskon Global:</td>
                    <td class="text-right" style="font-size: 11px; color: red;">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td style="font-size: 11px;">PPN ({{ $invoice->tax_rate }}%):</td>
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->shipping_cost > 0)
                <tr>
                    <td style="font-size: 11px;">Ongkos Kirim:</td>
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($invoice->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>GRAND TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
