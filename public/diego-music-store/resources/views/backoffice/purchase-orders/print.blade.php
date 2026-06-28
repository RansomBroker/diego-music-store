<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
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
            width: 100px;
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
            margin-top: 30px;
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
            margin-top: 50px;
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
            height: 70px;
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
            padding: 5px;
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
        <button onclick="window.print()" style="padding: 8px 15px; font-size: 14px; font-weight: bold; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;">Cetak Dokumen</button>
    </div>

    <table class="header">
        <tr>
            <td class="header-title">Purchase Order</td>
            <td class="header-subtitle">Diego Music Store</td>
        </tr>
    </table>

    <table class="table-meta">
        <tr>
            <!-- Left Column -->
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">Nomor PO</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $purchaseOrder->po_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal PO</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->order_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Cabang Pemesan</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->branch?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Estimasi Kirim</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->eta_date ? $purchaseOrder->eta_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kepada Supplier</td>
                        <td class="colon">:</td>
                        <td><strong>{{ $purchaseOrder->supplier->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Contact Person</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->supplier->contact_person ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->supplier->address ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <!-- Right Column -->
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="label">Mata Uang</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->currency }}</td>
                    </tr>
                    <tr>
                        <td class="label">Termin Bayar</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->payment_term ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Purchasing / Buyer</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->createdBy?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td class="colon">:</td>
                        <td>
                            <strong style="text-transform: uppercase;">
                                @if($purchaseOrder->status === 'draft')
                                    Draft
                                @elseif($purchaseOrder->status === 'approved')
                                    Approved
                                @elseif($purchaseOrder->status === 'closed')
                                    Closed
                                @else
                                    {{ $purchaseOrder->status }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                    @if($purchaseOrder->notes)
                    <tr>
                        <td class="label">Catatan</td>
                        <td class="colon">:</td>
                        <td>{{ $purchaseOrder->notes }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th>Deskripsi Produk</th>
                <th class="text-right" style="width: 70px;">Jumlah</th>
                <th style="width: 60px;">Unit</th>
                <th class="text-right" style="width: 110px;">Harga @</th>
                <th class="text-right" style="width: 100px;">Diskon Item</th>
                <th class="text-right" style="width: 80px;">Pajak</th>
                <th class="text-right" style="width: 125px;">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $item)
                <tr>
                    <td>
                        <strong>[{{ $item->productVariant->sku }}]</strong> 
                        {{ $item->productVariant->product->name }}
                        @if($item->productVariant->name)
                            - {{ $item->productVariant->name }}
                        @endif
                        @if($item->notes)
                            <br><span style="font-size: 10px; color: #555; font-style: italic;">* {{ $item->notes }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td>{{ $item->productVariant->product->unit?->name ?? 'Pcs' }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->tax_rate }}%</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-left">
            <div style="margin-bottom: 5px; font-weight: bold;">Terbilang:</div>
            <div class="spelled-out">
                {{ App\Helpers\TerbilangHelper::convert($purchaseOrder->grand_total) }} Rupiah
            </div>

            <div class="signatures">
                <div class="signature-box">
                    <div>Penerima / Supplier,</div>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    <div>( {{ $purchaseOrder->supplier->contact_person ?? '........................' }} )</div>
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
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                </tr>
                @if($purchaseOrder->discount_amount > 0)
                <tr>
                    <td style="font-size: 11px;">Diskon Global:</td>
                    <td class="text-right" style="font-size: 11px; color: red;">- Rp {{ number_format($purchaseOrder->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($purchaseOrder->tax_amount > 0)
                <tr>
                    <td style="font-size: 11px;">Total Pajak (PPN):</td>
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($purchaseOrder->other_cost > 0)
                <tr>
                    <td style="font-size: 11px;">Biaya Kirim (Ongkir):</td>
                    <td class="text-right" style="font-size: 11px;">Rp {{ number_format($purchaseOrder->other_cost, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>GRAND TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
