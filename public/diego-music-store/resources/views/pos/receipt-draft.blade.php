<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($format === 'penawaran')
            Penawaran Harga #{{ $draft->invoice_number }}
        @elseif($format === 'tagihan')
            Draft Tagihan #{{ $draft->invoice_number }}
        @else
            Large Bill #{{ $draft->invoice_number }}
        @endif
    </title>
    <style>
        /* Shared and Bill Styles */
        @if ($format === 'bill')
            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 12px;
                line-height: 1.4;
                color: #000;
                background: #fff;
                margin: 0;
                padding: 20px;
                max-width: 300px;
                margin: 0 auto;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .bold { font-weight: bold; }
            .header { margin-bottom: 15px; }
            .store-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
            .divider { border-top: 1px dashed #000; margin: 8px 0; }
            .double-divider { border-top: 2px double #000; margin: 8px 0; }
            .grid { display: flex; justify-content: space-between; }
            .item-row { margin-bottom: 6px; }
            .footer { margin-top: 25px; text-align: center; font-size: 10px; }
            .draft-banner { background-color: #000; color: #fff; text-align: center; font-weight: bold; padding: 4px; margin-bottom: 10px; font-size: 13px; }
        @else
            /* Modern A4 styling */
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                font-size: 13px;
                line-height: 1.5;
                color: #334155;
                background: #f8fafc;
                margin: 0;
                padding: 40px;
            }
            .page-container {
                max-width: 800px;
                margin: 0 auto;
                background: #ffffff;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
                border: 1px solid #e2e8f0;
            }
            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                border-bottom: 2px solid #f1f5f9;
                padding-bottom: 24px;
                margin-bottom: 24px;
            }
            .store-info h1 {
                font-size: 24px;
                font-weight: 800;
                color: #1e293b;
                margin: 0 0 8px 0;
                letter-spacing: -0.025em;
            }
            .store-info p {
                margin: 2px 0;
                color: #64748b;
            }
            .document-title {
                text-align: right;
            }
            .document-title h2 {
                font-size: 20px;
                font-weight: 800;
                color: #3b82f6;
                margin: 0 0 8px 0;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .document-title p {
                margin: 2px 0;
                color: #475569;
                font-weight: 600;
            }
            .details-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 24px;
                margin-bottom: 32px;
            }
            .details-box {
                background: #f8fafc;
                padding: 16px;
                border-radius: 12px;
                border: 1px solid #f1f5f9;
            }
            .details-box h3 {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                margin: 0 0 8px 0;
                font-weight: 700;
            }
            .details-box p {
                margin: 4px 0;
                color: #1e293b;
            }
            .details-box .val {
                font-weight: 600;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 32px;
            }
            th {
                background: #f8fafc;
                color: #475569;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.05em;
                padding: 12px 16px;
                text-align: left;
                border-bottom: 2px solid #e2e8f0;
            }
            td {
                padding: 16px;
                border-bottom: 1px solid #f1f5f9;
                color: #334155;
            }
            .item-name {
                font-weight: 600;
                color: #1e293b;
            }
            .item-desc {
                font-size: 11px;
                color: #64748b;
                margin-top: 4px;
            }
            .summary-container {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 40px;
            }
            .summary-table {
                width: 320px;
                margin-bottom: 0;
            }
            .summary-table td {
                padding: 8px 0;
                border-bottom: 1px solid #f1f5f9;
            }
            .summary-table tr:last-child td {
                border-bottom: none;
                font-size: 16px;
                font-weight: 800;
                color: #1e293b;
                padding-top: 12px;
            }
            .signature-section {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 80px;
                margin-top: 60px;
                text-align: center;
            }
            .signature-box {
                border-top: 1px solid #cbd5e1;
                padding-top: 8px;
                width: 200px;
                margin: 0 auto;
                color: #64748b;
                font-weight: 600;
            }
            .watermark-banner {
                background-color: #fef2f2;
                border: 1px solid #fecaca;
                color: #dc2626;
                text-align: center;
                font-weight: 800;
                padding: 12px;
                border-radius: 12px;
                margin-bottom: 24px;
                font-size: 14px;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }
        @endif
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .page-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto; background: white; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; font-family: sans-serif;">CETAK DOKUMEN</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-weight: bold; background: #edf2f7; color: #4a5568; border: none; border-radius: 8px; cursor: pointer; margin-left: 12px; font-family: sans-serif;">TUTUP HALAMAN</button>
    </div>

    @if ($format === 'bill')
        <!-- Bill layout (same as old bill) -->
        <div class="draft-banner">
            DRAFT TAGIHAN
        </div>

        <div class="header text-center">
            <span class="store-name">{{ $draft->branch->store_name ?: 'Diego Music Store' }}</span><br>
            <span>{{ $draft->branch->name }}</span><br>
            <span>Telp: {{ $draft->branch->phone }}</span>
        </div>

        <div class="double-divider"></div>

        <div class="grid">
            <span>No. Ref:</span>
            <span class="bold">{{ $draft->invoice_number }}</span>
        </div>
        <div class="grid">
            <span>Tanggal:</span>
            <span>{{ $draft->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="grid">
            <span>Kasir:</span>
            <span>{{ $draft->salesRep->name }}</span>
        </div>
        <div class="grid">
            <span>Pelanggan:</span>
            <span>{{ $draft->customer_name }}</span>
        </div>

        <div class="divider"></div>

        <div class="bold" style="margin-bottom: 5px;">Rincian Belanja:</div>
        @foreach ($draft->items as $item)
            <div class="item-row">
                <div>{{ $item->variant->product->name }} {{ $item->variant->name ? '('.$item->variant->name.')' : '' }}</div>
                <div class="grid">
                    <span>  {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                </div>
                @if ($item->discount_amount > 0)
                    <div class="grid text-right" style="font-size: 11px; color: #555;">
                        <span>  (Diskon Item)</span>
                        <span>-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="divider"></div>

        <div class="grid">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($draft->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ($draft->discount_amount > 0)
            <div class="grid">
                <span>Diskon:</span>
                <span>-Rp {{ number_format($draft->discount_amount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="grid">
            <span>PPN (11%):</span>
            <span>Rp {{ number_format($draft->tax_amount, 0, ',', '.') }}</span>
        </div>
        <div class="double-divider"></div>
        <div class="grid bold" style="font-size: 13px;">
            <span>Total Akhir:</span>
            <span>Rp {{ number_format($draft->grand_total, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>
        <div class="grid text-center bold" style="justify-content: center; font-size: 11px; border: 1px solid #000; padding: 4px;">
            BUKAN BUKTI PEMBAYARAN RESMI
        </div>

        <div class="footer">
            Harap simpan lembar tagihan ini untuk kasir.<br><br>
            Diego Music Store ERP
        </div>
    @else
        <!-- Modern A4 layout for Large Bill, Penawaran, and Tagihan -->
        <div class="page-container">
            
            <div class="watermark-banner">
                @if($format === 'penawaran')
                    DOKUMEN PENAWARAN HARGA - BUKAN BUKTI TRANSAKSI RESMI
                @else
                    DRAFT TAGIHAN / INVOICE - BUKAN BUKTI PEMBAYARAN RESMI
                @endif
            </div>

            <div class="header-container">
                <div class="store-info">
                    <h1>{{ $draft->branch->store_name ?: 'Diego Music Store' }}</h1>
                    <p class="bold">{{ $draft->branch->name }}</p>
                    <p>Telepon: {{ $draft->branch->phone }}</p>
                </div>
                <div class="document-title">
                    <h2>
                        @if($format === 'penawaran')
                            PENAWARAN HARGA
                        @elseif($format === 'tagihan')
                            DRAFT INVOICE
                        @else
                            LARGE BILL
                        @endif
                    </h2>
                    <p>No. Ref: <span style="font-family: monospace;">{{ $draft->invoice_number }}</span></p>
                    <p>Tanggal: {{ $draft->created_at->format('d F Y H:i') }}</p>
                </div>
            </div>

            <div class="details-container">
                <div class="details-box">
                    <h3>Disiapkan Oleh</h3>
                    <p class="val">{{ $draft->salesRep->name }}</p>
                    <p>Staff Penjualan / Kasir</p>
                    <p>Cabang: {{ $draft->branch->name }}</p>
                </div>
                <div class="details-box">
                    <h3>Ditujukan Kepada</h3>
                    <p class="val">{{ $draft->customer_name }}</p>
                    <p>Pelanggan</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No.</th>
                        <th>Deskripsi Produk</th>
                        <th style="width: 80px; text-align: center;">Jumlah</th>
                        <th style="width: 120px; text-align: right;">Harga Satuan</th>
                        <th style="width: 100px; text-align: right;">Diskon</th>
                        <th style="width: 140px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($draft->items as $idx => $item)
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="item-name">{{ $item->variant->product->name }}</div>
                                @if ($item->variant->name)
                                    <div class="item-desc">Varian: {{ $item->variant->name }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td style="text-align: right; color: #dc2626;">
                                @if ($item->discount_amount > 0)
                                    -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right;" class="bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary-container">
                <table class="summary-table">
                    <tbody>
                        <tr>
                            <td style="color: #64748b;">Subtotal</td>
                            <td style="text-align: right; font-weight: 600;">Rp {{ number_format($draft->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if ($draft->discount_amount > 0)
                            <tr>
                                <td style="color: #64748b;">Diskon Global</td>
                                <td style="text-align: right; font-weight: 600; color: #dc2626;">-Rp {{ number_format($draft->discount_amount, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="color: #64748b;">PPN (11%)</td>
                            <td style="text-align: right; font-weight: 600;">Rp {{ number_format($draft->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Total Akhir</td>
                            <td style="text-align: right;">Rp {{ number_format($draft->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="signature-section">
                <div>
                    <p style="margin-bottom: 60px; color: #64748b;">Disiapkan Oleh,</p>
                    <div class="signature-box">
                        {{ $draft->salesRep->name }}
                    </div>
                </div>
                <div>
                    <p style="margin-bottom: 60px; color: #64748b;">Diterima Oleh,</p>
                    <div class="signature-box">
                        {{ $draft->customer_name }}
                    </div>
                </div>
            </div>

        </div>
    @endif

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
