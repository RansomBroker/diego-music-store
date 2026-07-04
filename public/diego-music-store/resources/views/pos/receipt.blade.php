<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
            max-width: 300px; /* 80mm standard width */
            margin: 0 auto;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 15px;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px double #000;
            margin: 8px 0;
        }
        .grid {
            display: flex;
            justify-content: space-between;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
        }
        @media print {
            body {
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

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer;">CETAK</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; margin-left: 8px;">TUTUP</button>
        <hr>
    </div>

    <div class="header text-center">
        <span class="store-name">{{ $sale->branch->store_name ?: 'Diego Music Store' }}</span><br>
        <span>{{ $sale->branch->name }}</span><br>
        <span>Telp: {{ $sale->branch->phone }}</span>
    </div>

    <div class="double-divider"></div>

    <div class="grid">
        <span>No. Faktur:</span>
        <span class="bold">{{ $sale->invoice_number }}</span>
    </div>
    <div class="grid">
        <span>Tanggal:</span>
        <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="grid">
        <span>Kasir:</span>
        <span>{{ $sale->salesRep->name }}</span>
    </div>
    <div class="grid">
        <span>Pelanggan:</span>
        <span>{{ $sale->customer ? $sale->customer->name : 'Umum / Walk-in' }}</span>
    </div>

    <div class="divider"></div>

    <!-- Items Section -->
    <div class="bold" style="margin-bottom: 5px;">Rincian Belanja:</div>
    @foreach ($sale->items as $item)
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
        <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
    </div>
    @if ($sale->discount_amount > 0)
        <div class="grid">
            <span>Diskon:</span>
            <span>-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
        </div>
    @endif
    <div class="grid">
        <span>PPN (11%):</span>
        <span>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
    </div>
    <div class="double-divider"></div>
    <div class="grid bold" style="font-size: 13px;">
        <span>Total Akhir:</span>
        <span>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>
    <div class="grid">
        <span>Metode Pembayaran:</span>
        <span class="bold">{{ strtoupper($sale->payment_method) }}</span>
    </div>

    <div class="footer">
        Terima Kasih atas Kunjungan Anda<br>
        Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.<br><br>
        Diego Music Store ERP
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
