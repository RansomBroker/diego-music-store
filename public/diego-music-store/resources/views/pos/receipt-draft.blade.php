@php
    $setting = \App\Models\ReceiptSetting::where('branch_id', $draft->branch?->id)->first();
    $paperWidth = $setting?->paper_width ?: '80mm';
    $billMaxWidth = match ($paperWidth) {
        '58mm' => '54mm',
        'A4' => '186mm',
        default => '74mm',
    };
    $billPageSize = match ($paperWidth) {
        '58mm' => '58mm auto',
        'A4' => 'A4 portrait',
        default => '80mm auto',
    };
    $billPageMargin = match ($paperWidth) {
        '58mm' => '2mm',
        'A4' => '12mm',
        default => '3mm',
    };
    $billFontSize = match ($paperWidth) {
        '58mm' => '10.5px',
        'A4' => '13px',
        default => '12px',
    };
    $storeDisplayName = $setting?->store_display_name ?: ($draft->branch?->store_name ?: 'Diego Music Store');
    $headerNote = filled($setting?->header_text) ? $setting->header_text : ($draft->branch?->receipt_header ?: null);
    $footerNote = filled($setting?->footer_text) ? $setting->footer_text : ($draft->branch?->receipt_footer ?: 'Harap simpan lembar tagihan ini untuk kasir.');
    $invoiceFooterNotes = filled($setting?->invoice_footer_notes) ? $setting->invoice_footer_notes : null;
    $showLogo = ($setting->show_logo ?? true) && ! empty($draft->branch?->logo_path);
    $showCashier = $setting->show_cashier ?? true;
    $showCustomer = $setting->show_customer ?? true;
    $showTaxDetails = $setting->show_tax_details ?? true;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($format === 'penawaran')
            Penawaran Harga #{{ $draft->invoice_number }}
        @else
            Invoice #{{ $draft->invoice_number }}
        @endif
    </title>
    <style>
        /* Shared and Bill Styles */
        @if ($format === 'bill')
            @page {
                size: {{ $billPageSize }};
                margin: {{ $billPageMargin }};
            }
            * { box-sizing: border-box; }
            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: {{ $billFontSize }};
                line-height: 1.4;
                color: #000;
                background: #fff;
                padding: 16px;
                width: 100%;
                max-width: {{ $billMaxWidth }};
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
        @elseif ($format === 'invoice')
            /* Simple Landscape styling for dot-matrix */
            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 14px;
                color: #000;
                background: #fff;
                margin: 0;
                padding: 20px;
            }
            .large-container { width: 100%; }
            .large-header { text-align: center; margin-bottom: 20px; }
            .large-info { display: flex; justify-content: space-between; margin-bottom: 10px; }
            table.large-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            table.large-table th, table.large-table td { border: 1px solid #000; padding: 6px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .bold { font-weight: bold; }
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
            @if ($format === 'invoice')
                @page { size: landscape; margin: 10mm; }
            @elseif ($format !== 'bill')
                @page { size: A4 portrait; margin: 10mm; }
            @endif
            body {
                background: #ffffff;
                padding: 0;
                @if ($format === 'bill')
                    width: {{ $billMaxWidth }};
                    max-width: {{ $billMaxWidth }};
                    margin: 0 auto;
                @endif
            }
            .page-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
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
            @if ($showLogo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($draft->branch->logo_path) }}" alt="Logo" style="max-height: 50px; margin-bottom: 8px;"><br>
            @endif
            <span class="store-name">{{ $storeDisplayName }}</span><br>
            @if (! empty($headerNote))
                <div style="font-size: 11px; font-style: italic; margin-bottom: 4px;">{!! nl2br(e($headerNote)) !!}</div>
            @endif
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
        @if ($showCashier)
            <div class="grid">
                <span>Kasir:</span>
                <span>{{ $draft->cashier_name ?? $draft->salesRep->name }}</span>
            </div>
        @endif
        @if ($showCustomer)
            <div class="grid">
                <span>Pelanggan:</span>
                <span>{{ $draft->customer_name }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <div class="bold" style="margin-bottom: 5px;">Rincian Belanja:</div>
        @php $totalItemsQtyBill = 0; @endphp
        @foreach ($draft->items as $item)
            @php $totalItemsQtyBill += $item->quantity; @endphp
            <div class="item-row">
                <div>[{{ $item->variant->sku ?? '-' }}] {{ $item->variant->product->name }} {{ $item->variant->name ? '('.$item->variant->name.')' : '' }}</div>
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
        @if ($showTaxDetails && $draft->tax_amount > 0)
        <div class="grid">
            <span>PPN (11%):</span>
            <span>Rp {{ number_format($draft->tax_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="double-divider"></div>
        <div class="grid bold" style="font-size: 13px;">
            <span>Total Akhir:</span>
            <span>Rp {{ number_format($draft->grand_total, 0, ',', '.') }}</span>
        </div>
        <div class="divider"></div>
        <div class="grid">
            <span>Total Qty Barang:</span>
            <span class="bold">{{ $totalItemsQtyBill }} Item</span>
        </div>

        <div class="divider"></div>
        <div class="grid text-center bold" style="justify-content: center; font-size: 11px; border: 1px solid #000; padding: 4px;">
            BUKAN BUKTI PEMBAYARAN RESMI
        </div>

        <div class="footer">
            {!! nl2br(e($footerNote)) !!}<br>
            @if (! empty($invoiceFooterNotes))
                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #000;">
                    {!! nl2br(e($invoiceFooterNotes)) !!}
                </div>
            @endif
        </div>
    @elseif ($format === 'invoice')
        <div class="large-container">
            <div class="large-header">
                @if ($showLogo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($draft->branch->logo_path) }}" alt="Logo" style="max-height: 55px; margin-bottom: 6px;"><br>
                @endif
                <div class="bold" style="font-size:20px;">{{ $storeDisplayName }}</div>
                @if (! empty($headerNote))
                    <div style="font-size: 12px; font-style: italic; margin-top: 2px;">{!! nl2br(e($headerNote)) !!}</div>
                @endif
                <div>{{ $draft->branch->name }} | Telp: {{ $draft->branch->phone }}</div>
                <h2 style="margin: 10px 0 0 0; text-transform: uppercase;">INVOICE</h2>
                <div style="display: inline-block; padding: 4px 12px; border: 2px solid #000; font-weight: bold; font-size: 18px; margin-top: 10px; border-radius: 4px;">
                    LUNAS / PAID
                </div>
            </div>
            
            <div class="large-info">
                <div>
                    <div><span class="bold">No. Ref:</span> {{ $draft->invoice_number }}</div>
                    <div><span class="bold">Tanggal:</span> {{ $draft->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="text-right">
                    @if ($showCashier)
                        <div><span class="bold">Kasir:</span> {{ $draft->cashier_name ?? $draft->salesRep->name }}</div>
                    @endif
                    @if ($showCustomer)
                        <div><span class="bold">Pelanggan:</span> {{ $draft->customer_name }}</div>
                    @endif
                </div>
            </div>

            <table class="large-table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">No.</th>
                        <th style="width: 120px;">Kode Item</th>
                        <th>Deskripsi Produk</th>
                        <th style="width: 60px;" class="text-center">Qty</th>
                        <th style="width: 120px;" class="text-right">Harga Satuan</th>
                        <th style="width: 100px;" class="text-right">Diskon</th>
                        <th style="width: 150px;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalItemsQtyInvoice = 0; @endphp
                    @foreach ($draft->items as $idx => $item)
                        @php $totalItemsQtyInvoice += $item->quantity; @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $item->variant->sku ?? '-' }}</td>
                            <td>
                                {{ $item->variant->product->name }}
                                @if ($item->variant->name)
                                    ({{ $item->variant->name }})
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right">
                                @if ($item->discount_amount > 0)
                                    -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between;">
                <div>
                    <div style="margin-bottom: 5px; font-weight: bold;">Total Qty Barang: {{ $totalItemsQtyInvoice }} Item</div>
                </div>
                <table style="width: 350px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px;">Subtotal</td>
                        <td class="text-right bold" style="padding: 4px;">Rp {{ number_format($draft->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if ($draft->discount_amount > 0)
                        <tr>
                            <td style="padding: 4px;">Diskon Global</td>
                            <td class="text-right bold" style="padding: 4px; color: #000;">-Rp {{ number_format($draft->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if ($showTaxDetails && $draft->tax_amount > 0)
                        <tr>
                            <td style="padding: 4px;">PPN (11%)</td>
                            <td class="text-right bold" style="padding: 4px;">Rp {{ number_format($draft->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 4px; font-size: 16px;" class="bold">Total Akhir</td>
                        <td class="text-right bold" style="padding: 4px; font-size: 16px;">Rp {{ number_format($draft->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            @if (! empty($invoiceFooterNotes) || ! empty($footerNote))
                <div style="margin-top: 16px; padding: 10px; border: 1px dashed #000; font-size: 12px;">
                    @if (! empty($invoiceFooterNotes))
                        <div class="bold" style="margin-bottom: 4px;">Catatan Pembayaran / Invoice:</div>
                        <div>{!! nl2br(e($invoiceFooterNotes)) !!}</div>
                    @endif
                    @if (! empty($footerNote))
                        <div style="margin-top: 6px; font-style: italic;">{!! nl2br(e($footerNote)) !!}</div>
                    @endif
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; margin-top: 40px; padding: 0 50px;">
                <div class="text-center">
                    <div style="margin-bottom: 70px;">Penerima</div>
                    <div style="border-bottom: 1px solid #000; width: 150px;"></div>
                </div>
                <div class="text-center">
                    <div style="margin-bottom: 70px;">Hormat Kami</div>
                    <div style="border-bottom: 1px solid #000; width: 150px;"></div>
                </div>
            </div>
        </div>
    @else
        <!-- Modern A4 layout for Penawaran and Tagihan -->
        <div class="page-container">
            
            @if($format === 'penawaran')
                <div class="watermark-banner">
                    DOKUMEN PENAWARAN HARGA
                </div>
            @endif

            <div class="header-container">
                <div class="store-info">
                    @if ($showLogo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($draft->branch->logo_path) }}" alt="Logo" style="max-height: 52px; margin-bottom: 8px;">
                    @endif
                    <h1>{{ $storeDisplayName }}</h1>
                    @if (! empty($headerNote))
                        <p style="font-style: italic;">{!! nl2br(e($headerNote)) !!}</p>
                    @endif
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
                            INVOICE
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
                        <th style="width: 120px;">Kode Item</th>
                        <th>Deskripsi Produk</th>
                        <th style="width: 80px; text-align: center;">Jumlah</th>
                        <th style="width: 120px; text-align: right;">Harga Satuan</th>
                        <th style="width: 100px; text-align: right;">Diskon</th>
                        <th style="width: 140px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalItemsQtyModern = 0; @endphp
                    @foreach ($draft->items as $idx => $item)
                        @php $totalItemsQtyModern += $item->quantity; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>{{ $item->variant->sku ?? '-' }}</td>
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

            <div class="summary-container" style="justify-content: space-between; align-items: flex-end;">
                <div>
                    <div style="font-weight: 600; color: #475569; margin-bottom: 8px;">Total Qty Barang:</div>
                    <div style="font-size: 18px; font-weight: 800; color: #1e293b;">{{ $totalItemsQtyModern }} Item</div>
                </div>
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
                        @if ($showTaxDetails && $draft->tax_amount > 0)
                        <tr>
                            <td style="color: #64748b;">PPN (11%)</td>
                            <td style="text-align: right; font-weight: 600;">Rp {{ number_format($draft->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Total Akhir</td>
                            <td style="text-align: right;">Rp {{ number_format($draft->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if (! empty($invoiceFooterNotes) || ! empty($footerNote))
                <div style="margin-top: 24px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 12px; color: #475569;">
                    @if (! empty($invoiceFooterNotes))
                        <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">Catatan Pembayaran / Tagihan</div>
                        <div>{!! nl2br(e($invoiceFooterNotes)) !!}</div>
                    @endif
                    @if (! empty($footerNote))
                        <div style="margin-top: 8px; font-style: italic; color: #64748b;">{!! nl2br(e($footerNote)) !!}</div>
                    @endif
                </div>
            @endif

        </div>
    @endif


</body>
</html>
