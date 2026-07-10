<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Tagihan #{{ $draft->invoice_number }}</title>
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
        .draft-banner {
            background-color: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 4px;
            margin-bottom: 10px;
            font-size: 13px;
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

    <!-- Items Section -->
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

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
