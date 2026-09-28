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
        @page {
            @if ($columns == 1)
            size: {{ $label_width }}mm {{ $label_height }}mm;
            @else
            size: auto;
            @endif
            margin: 2mm 0mm;
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
    <div class="no-print" style="margin-bottom: 20px; text-align: center; background: #f8fafc; padding: 12px; border-bottom: 1px solid #e2e8f0;">
        <button onclick="window.print()" style="padding: 8px 20px; font-weight: bold; font-size: 13px; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 6px;">CETAK PREVIEW BROWSER</button>
        <button id="directPrintBtn" onclick="directPrintViaAgent()" style="padding: 8px 20px; font-weight: bold; font-size: 13px; cursor: pointer; background: #059669; color: #fff; border: none; border-radius: 6px; margin-left: 8px;">⚡ DIRECT PRINT VIA AGENT</button>
        <button onclick="window.close()" style="padding: 8px 16px; font-size: 13px; cursor: pointer; margin-left: 8px; background: #64748b; color: #fff; border: none; border-radius: 6px;">TUTUP</button>
        <div id="agentStatusNotice" style="font-size: 11px; color: #64748b; margin-top: 6px;"></div>
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
        window.printQueueData = @json($queue);
        window.labelConfig = {
            width: {{ $label_width }},
            height: {{ $label_height }},
            showStore: {{ $show_store ? 'true' : 'false' }},
            showName: {{ $show_name ? 'true' : 'false' }},
            showCode: {{ $show_code ? 'true' : 'false' }},
            showPrice: {{ $show_price ? 'true' : 'false' }},
            storeName: @json($storeTitle)
        };

        async function directPrintViaAgent() {
            const btn = document.getElementById('directPrintBtn');
            const notice = document.getElementById('agentStatusNotice');
            const agentUrl = (localStorage.getItem('diego_pos_agent_url') || 'http://127.0.0.1:18920').replace(/\/+$/, '');
            let printer = localStorage.getItem('diego_pos_barcode_printer');

            // Auto-detect printer from agent if not explicitly saved yet
            if (!printer) {
                try {
                    const pRes = await fetch(`${agentUrl}/api/printers`);
                    const pData = await pRes.json();
                    if (pData.status === 'success' && pData.printers.length > 0) {
                        printer = pData.printers.find(p => /xprinter|zebra|barcode|tsc|label|panda/i.test(p)) || pData.printers[0];
                        localStorage.setItem('diego_pos_barcode_printer', printer);
                    }
                } catch (_) {}
            }

            if (!printer) {
                if (notice) notice.innerText = '⚠️ Printer barcode belum terdeteksi. Silakan pilih di Pengaturan POS.';
                window.print();
                return;
            }

            if (btn) btn.innerText = 'Mencetak via Agent...';
            if (notice) notice.innerText = `Mengirim perintah cetak langsung ke [${printer}]...`;

            // Build TSPL command format (Xprinter, TSC, Zebra)
            let tspl = `SIZE ${window.labelConfig.width} mm, ${window.labelConfig.height} mm\nGAP 2 mm, 0 mm\nDIRECTION 1\n`;
            for (const item of window.printQueueData) {
                const qty = parseInt(item.qty || 1);
                tspl += `CLS\n`;
                let y = 15;
                if (window.labelConfig.showStore) {
                    const st = (window.labelConfig.storeName || '').substring(0, 24);
                    tspl += `TEXT 20,${y},"2",0,1,1,"${st}"\n`;
                    y += 24;
                }
                if (window.labelConfig.showName) {
                    const nm = (item.name || '').substring(0, 26);
                    tspl += `TEXT 20,${y},"2",0,1,1,"${nm}"\n`;
                    y += 28;
                }
                const code = item.barcode || item.sku || '00000';
                tspl += `BARCODE 20,${y},"128",45,1,0,2,2,"${code}"\n`;
                y += 62;
                if (window.labelConfig.showCode) {
                    tspl += `TEXT 20,${y},"2",0,1,1,"${code}"\n`;
                    y += 24;
                }
                if (window.labelConfig.showPrice && item.price) {
                    tspl += `TEXT 20,${y},"3",0,1,1,"Rp ${parseInt(item.price).toLocaleString('id-ID')}"\n`;
                }
                tspl += `PRINT ${qty},1\n`;
            }

            try {
                const res = await fetch(`${agentUrl}/api/print/barcode`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        printer: printer,
                        raw_commands: tspl,
                        title: 'Cetak Barcode POS'
                    })
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    if (notice) notice.innerText = `✅ Berhasil dicetak langsung ke [${printer}]! Menutup halaman...`;
                    setTimeout(() => window.close(), 1000);
                    return;
                } else {
                    if (notice) notice.innerText = `❌ Gagal: ${data.message || 'Error driver printer'}`;
                }
            } catch (e) {
                console.warn('Agent direct print failed:', e);
                if (notice) notice.innerText = '🔴 Print Agent tidak aktif, beralih ke preview dialog browser...';
            }

            if (btn) btn.innerText = '⚡ DIRECT PRINT VIA AGENT';
            window.print();
        }

        window.onload = function() {
            const savedDirect = localStorage.getItem('diego_pos_direct_print_enabled');
            const isDirect = savedDirect !== null ? savedDirect === 'true' : true;
            if (isDirect) {
                directPrintViaAgent();
            } else {
                window.print();
            }
        };
    </script>
    @endif
</body>
</html>
