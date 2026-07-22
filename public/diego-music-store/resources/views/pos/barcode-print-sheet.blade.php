<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode Label Produk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat({{ $columns }}, 1fr);
            gap: {{ $gap_y }}mm {{ $gap_x }}mm;
        }
        .label-card {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
            min-height: {{ $label_height }}mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .store-name {
            font-size: {{ max($font_size - 2, 7) }}px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .product-name {
            font-size: {{ $font_size }}px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            margin-top: 1px;
            line-height: 1.1;
        }
        .barcode-container {
            width: 100%;
            height: {{ $barcode_height }}px;
            margin: 2px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .barcode-container svg {
            max-height: 100%;
            width: auto;
        }
        .sku-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ max($font_size - 2, 7) }}px;
            font-weight: bold;
            line-height: 1;
        }
        .price {
            font-size: {{ $font_size }}px;
            font-weight: 800;
            border-top: 1px solid #000;
            margin-top: 2px;
            padding-top: 1px;
            width: 100%;
            line-height: 1.1;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 20px; font-weight: bold; font-size: 14px; cursor: pointer;">CETAK BARCODE</button>
        <button onclick="window.close()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; margin-left: 8px;">TUTUP</button>
        <hr style="margin-top: 15px;">
    </div>

    @php
        $branch = auth()->user()?->branches()?->first();
        $storeTitle = $branch?->store_name ?: 'Diego Music Store';
    @endphp

    <div class="grid-container">
        @foreach ($queue as $item)
            @for ($i = 0; $i < ($item['qty'] ?? 1); $i++)
                <div class="label-card">
                    @if ($show_store)
                        <div class="store-name">{{ $storeTitle }}</div>
                    @endif
                    @if ($show_name)
                        <div class="product-name">{{ $item['name'] }}</div>
                    @endif

                    <div class="barcode-container">
                        {!! \App\Helpers\BarcodeHelper::generateCode128Svg($item['sku'] ?? '00000', 180, $barcode_height) !!}
                    </div>

                    @if ($show_code)
                        <div class="sku-code">{{ $item['sku'] }}</div>
                    @endif
                    @if ($show_price)
                        <div class="price">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</div>
                    @endif
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
