@php
    $storeName = $setting?->store_display_name ?: ($sale->branch?->store_name ?: ($sale->branch?->name ?: 'Diego Music Store'));
    $branchName = $sale->branch?->name ?? 'Cabang Pusat';
    $storeAddress = $sale->branch?->address ?? '';
    $storePhone = $sale->branch?->phone ?: ($sale->branch?->fonnte_whatsapp_number ?? '');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 10px 12px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .store-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .store-sub {
            font-size: 8.5px;
            color: #64748b;
            margin-bottom: 1px;
        }
        .divider-solid {
            border-top: 1px solid #cbd5e1;
            margin: 6px 0;
        }
        .divider-dashed {
            border-top: 1px dashed #94a3b8;
            margin: 6px 0;
        }
        .divider-double {
            border-top: 2px double #64748b;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 9.5px;
        }
        .item-calc {
            font-size: 8.5px;
            color: #64748b;
        }
        .total-row td {
            padding: 3px 0;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Store Header -->
    <div class="text-center" style="margin-bottom: 6px;">
        <div class="store-title">{{ $storeName }}</div>
        @if ($branchName !== $storeName)
            <div class="store-sub font-bold">{{ $branchName }}</div>
        @endif
        @if (!empty($storeAddress))
            <div class="store-sub">{{ $storeAddress }}</div>
        @endif
        @if (!empty($storePhone))
            <div class="store-sub">Telp/WA: {{ $storePhone }}</div>
        @endif
    </div>

    <div class="divider-double"></div>

    <!-- Meta Info -->
    <table>
        <tr>
            <td class="text-left" style="width: 32%; color: #64748b;">No. Faktur</td>
            <td class="text-right font-bold">#{{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #64748b;">Tanggal</td>
            <td class="text-right">{{ $sale->created_at ? $sale->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #64748b;">Kasir</td>
            <td class="text-right">{{ $sale->salesRep?->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #64748b;">Pelanggan</td>
            <td class="text-right font-bold">{{ $sale->customer?->name ?? 'Umum / Walk-in' }}</td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th class="text-left" style="font-size: 8.5px; color: #64748b; padding-bottom: 4px;">ITEM</th>
                <th class="text-right" style="font-size: 8.5px; color: #64748b; padding-bottom: 4px;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                @php
                    $prodName = $item->variant?->product?->name ?? 'Produk';
                    $varName = $item->variant?->name ?? '';
                    $fullName = $prodName . ($varName && $varName !== 'Default' ? " ({$varName})" : '');
                @endphp
                <tr>
                    <td class="item-name" colspan="2">{{ $fullName }}</td>
                </tr>
                <tr>
                    <td class="item-calc">
                        {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        @if ($item->discount_amount > 0)
                            <span style="color: #dc2626;">(Disc -Rp {{ number_format($item->discount_amount, 0, ',', '.') }})</span>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        Rp {{ number_format($item->total_price, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider-dashed"></div>

    <!-- Totals -->
    <table>
        <tr>
            <td class="text-left" style="color: #64748b;">Subtotal</td>
            <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if ($sale->discount_amount > 0)
            <tr>
                <td class="text-left" style="color: #16a34a;">Diskon Faktur</td>
                <td class="text-right" style="color: #16a34a;">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ($sale->tax_amount > 0)
            <tr>
                <td class="text-left" style="color: #64748b;">PPN (11%)</td>
                <td class="text-right">+Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td class="text-left divider-double">TOTAL AKHIR</td>
            <td class="text-right divider-double">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #64748b; padding-top: 4px;">Metode Bayar</td>
            <td class="text-right font-bold" style="padding-top: 4px;">{{ strtoupper($sale->payment_method ?: 'TUNAI') }}</td>
        </tr>
    </table>

    <div class="divider-solid"></div>

    <!-- Footer -->
    <div class="footer">
        @if (!empty($setting?->footer_text))
            <div>{{ $setting->footer_text }}</div>
        @else
            <div class="font-bold">Terima kasih atas kunjungan & pembelian Anda!</div>
            <div style="margin-top: 2px;">Barang yang telah dibeli tidak dapat ditukar/dikembalikan tanpa bukti struk ini.</div>
        @endif
        <div style="margin-top: 6px; font-size: 7.5px; color: #94a3b8;">
            Dicetak secara digital oleh Diego Music Store POS
        </div>
    </div>
</body>
</html>
