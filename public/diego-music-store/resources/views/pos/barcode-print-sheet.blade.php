<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode Label Produk</title>
    @php
        $__isPdf = $isPdf ?? false;
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }
        .label-card {
            border: 1.5px solid #111;
            padding: 5px 6px;
            text-align: center;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
            width: {{ $label_width }}mm;
            max-width: {{ $label_width }}mm;
            overflow: hidden;
            display: inline-block;
            vertical-align: top;
        }
        .store-name {
            font-size: {{ max($font_size - 2, 7) }}px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
            @if (!$__isPdf)
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            @else
            word-wrap: break-word;
            overflow-wrap: break-word;
            @endif
        }
        .product-name {
            font-size: {{ $font_size }}px;
            font-weight: bold;
            max-width: 100%;
            margin-top: 1px;
            line-height: 1.2;
            @if (!$__isPdf)
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            @else
            word-wrap: break-word;
            overflow-wrap: break-word;
            @endif
        }
        .barcode-wrapper {
            width: 100%;
            text-align: center;
            margin: 3px 0;
        }
        .sku-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ max($font_size - 2, 7) }}px;
            font-weight: bold;
            letter-spacing: 1.5px;
            line-height: 1.2;
        }
        .price {
            font-size: {{ $font_size }}px;
            font-weight: 800;
            border-top: 1px solid #333;
            margin-top: 2px;
            padding-top: 2px;
            line-height: 1.2;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    @if (!$__isPdf)
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 20px; font-weight: bold; font-size: 14px; cursor: pointer;">CETAK BARCODE</button>
        <button onclick="window.close()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; margin-left: 8px;">TUTUP</button>
        <hr style="margin-top: 15px;">
    </div>
    @endif

    @php
        $branch = auth()->user()?->branches()?->first();
        $storeTitle = $branch?->store_name ?: 'Diego Music Store';

        $flatQueue = [];
        foreach ($queue as $item) {
            $qty = isset($item['qty']) ? intval($item['qty']) : 1;
            for ($i = 0; $i < $qty; $i++) {
                $flatQueue[] = $item;
            }
        }
    @endphp

    <table style="width: 100%; border-collapse: separate; border-spacing: {{ $gap_x }}mm {{ $gap_y }}mm; border: none; margin: 0; padding: 0;">
        @foreach (array_chunk($flatQueue, $columns) as $rowItems)
            <tr>
                @foreach ($rowItems as $item)
                    <td style="width: {{ 100 / $columns }}%; padding: 0; border: none; vertical-align: top; text-align: center;">
                        <div class="label-card">
                            @if ($show_store)
                                <div class="store-name">{{ $storeTitle }}</div>
                            @endif
                            @if ($show_name)
                                <div class="product-name">{{ $item['name'] }}</div>
                            @endif

                            <div class="barcode-wrapper">
                                @php
                                    $barcodeCode = $item['sku'] ?? '00000';
                                @endphp
                                @if ($__isPdf)
                                    @php
                                        // PDF: large SVG viewBox for thick, clear bars
                                        $svgContent = \App\Helpers\BarcodeHelper::generateCode128Svg($barcodeCode, 300, 120);
                                        $imgW = $pdfBarcodeWidthMm ?? round($label_width * 0.85, 1);
                                        $imgH = $pdfBarcodeHeightMm ?? round($label_height * 0.42, 1);
                                    @endphp
                                    <img src="data:image/svg+xml;base64,{{ base64_encode($svgContent) }}"
                                         style="width: {{ $imgW }}mm; height: {{ $imgH }}mm; display: block; margin: 0 auto;" />
                                @else
                                    @php
                                        $svgContent = \App\Helpers\BarcodeHelper::generateCode128Svg($barcodeCode, 200, $barcode_height);
                                    @endphp
                                    <img src="data:image/svg+xml;base64,{{ base64_encode($svgContent) }}"
                                         style="height: {{ $barcode_height }}px; width: 90%; display: inline-block;" />
                                @endif
                            </div>

                            @if ($show_code)
                                <div class="sku-code">{{ $item['sku'] }}</div>
                            @endif
                            @if ($show_price)
                                <div class="price">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    </td>
                @endforeach
                @if (count($rowItems) < $columns)
                    @for ($i = 0; $i < ($columns - count($rowItems)); $i++)
                        <td style="width: {{ 100 / $columns }}%; border: none;"></td>
                    @endfor
                @endif
            </tr>
        @endforeach
    </table>

    @if (!$__isPdf)
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>
