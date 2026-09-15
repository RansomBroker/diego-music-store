<!-- FORMAL BLACK & WHITE ERP PRINT TEMPLATE -->
<div class="hidden print:block font-serif text-black bg-white p-0 m-0 leading-tight w-full">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 12mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif;
            }
            .erp-print-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                margin-bottom: 15px;
            }
            .erp-print-table th, .erp-print-table td {
                border: 1px solid #000000;
                padding: 4px 5px;
                font-size: 8.5pt;
            }
            .erp-print-table th {
                background-color: #e5e7eb !important;
                font-weight: bold;
                text-transform: uppercase;
                text-align: left;
            }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            . { font-family: 'Courier New', Courier, monospace; }
        }
    </style>

    <!-- Company Header -->
    <div style="border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 style="font-size: 16pt; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $currentBranch?->store_name ?: 'DIEGO MUSIC STORE' }}
                </h1>
                <div style="font-size: 9pt; margin-top: 3px; font-weight: bold;">
                    CABANG: {{ strtoupper($currentBranch?->name ?: 'KANTOR PUSAT') }}
                </div>
                <div style="font-size: 9pt; color: #333;">
                    {{ $currentBranch?->address ?: 'Jl. Utama Music Store ERP' }} | Telp: {{ $currentBranch?->phone ?: '-' }}
                </div>
            </div>
            <div style="text-align: right; font-size: 8pt;" class="">
                <div>TGL CETAK: {{ now()->format('d/m/Y H:i:s') }}</div>
                <div>PETUGAS: {{ strtoupper(auth()->user()?->name ?: 'ADMIN') }}</div>
                <div>STATUS: DOKUMEN RESMI ERP</div>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div style="text-align: center; margin-bottom: 15px;">
        <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; text-decoration: underline;">
            LAPORAN PENJUALAN (MODE: {{ strtoupper(str_replace('_', ' ', $viewMode)) }})
        </h2>
        <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
            PERIODE: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'AWAL' }} S/D {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'SEKARANG' }}
        </div>
    </div>

    <!-- Print Grid -->
    <table class="erp-print-table">
        @if ($viewMode === 'detail')
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th>TGL</th>
                    <th>KATEGORI</th>
                    <th>NO INVOICE</th>
                    <th>PELANGGAN</th>
                    <th>CATATAN</th>
                    <th>KASIR</th>
                    <th>SALES</th>
                    <th>BAYAR</th>
                    <th>KODE</th>
                    <th>NAMA BARANG</th>
                    <th class="text-center">QTY</th>
                    <th class="text-right">HARGA</th>
                    <th class="text-right">DISKON</th>
                    <th class="text-right">PPN</th>
                    <th class="text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $idx => $item)
                    <tr>
                        @if (!empty($item['is_first_item']))
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top; text-align: center;">{{ $idx + 1 }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['invoice_date'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['sale_category'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;" class=" font-bold">{{ $item['invoice_number'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['customer_name'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top; font-style: italic;">{{ $item['notes'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['cashier_name'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['sales_rep_name'] }}</td>
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;">{{ $item['payment_method'] }}</td>
                        @endif
                        <td class="">{{ $item['sku'] }}</td>
                        <td style="font-weight: bold;">{{ $item['product_name'] }}</td>
                        <td class="text-center font-bold">{{ $item['quantity'] }}</td>
                        <td class="text-right ">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                        <td class="text-right ">Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</td>
                        @if (!empty($item['is_first_item']))
                            <td rowspan="{{ $item['rowspan'] }}" style="vertical-align: top;" class="text-right ">Rp {{ number_format($item['tax_amount'], 0, ',', '.') }}</td>
                        @endif
                        <td class="text-right  font-bold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data penjualan harian pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        @elseif ($viewMode === 'per_day')
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th>TANGGAL</th>
                    <th class="text-center">JUMLAH NOTA</th>
                    <th class="text-right">TOTAL SUBTOTAL</th>
                    <th class="text-right">TOTAL DISKON</th>
                    <th class="text-right">TOTAL PPN</th>
                    <th class="text-right">GRAND TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold;">{{ $row['date'] }}</td>
                        <td class="text-center font-bold">{{ $row['invoice_count'] }} Nota</td>
                        <td class="text-right ">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                        <td class="text-right ">Rp {{ number_format($row['discount_amount'], 0, ',', '.') }}</td>
                        <td class="text-right ">Rp {{ number_format($row['tax_amount'], 0, ',', '.') }}</td>
                        <td class="text-right  font-bold">Rp {{ number_format($row['grand_total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data penjualan per hari pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        @elseif ($viewMode === 'per_nota')
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">NO</th>
                    <th>NO INVOICE</th>
                    <th>TANGGAL</th>
                    <th>KATEGORI</th>
                    <th>PELANGGAN</th>
                    <th>KASIR</th>
                    <th>SALES</th>
                    <th>JENIS BAYAR</th>
                    <th class="text-center">JLH ITEM</th>
                    <th class="text-right">SUBTOTAL</th>
                    <th class="text-right">DISKON</th>
                    <th class="text-right">PPN</th>
                    <th class="text-right">GRAND TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $idx => $nota)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class=" font-bold">{{ $nota['invoice_number'] }}</td>
                        <td>{{ $nota['date'] }}</td>
                        <td>{{ $nota['sale_category'] }}</td>
                        <td style="font-weight: bold;">{{ $nota['customer_name'] }}</td>
                        <td>{{ $nota['cashier_name'] }}</td>
                        <td>{{ $nota['sales_rep_name'] }}</td>
                        <td>{{ $nota['payment_method'] }}</td>
                        <td class="text-center font-bold">{{ $nota['item_count'] }}</td>
                        <td class="text-right ">Rp {{ number_format($nota['subtotal'], 0, ',', '.') }}</td>
                        <td class="text-right ">Rp {{ number_format($nota['discount_amount'], 0, ',', '.') }}</td>
                        <td class="text-right ">Rp {{ number_format($nota['tax_amount'], 0, ',', '.') }}</td>
                        <td class="text-right  font-bold">Rp {{ number_format($nota['grand_total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 15px; font-style: italic;">Belum ada transaksi nota penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        @elseif ($viewMode === 'top_selling')
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">RANK</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th>KATEGORI PRODUK</th>
                    <th class="text-center">TOTAL QTY TERJUAL</th>
                    <th class="text-right">TOTAL OMZET</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reportData['items'] ?? [] as $rank => $prod)
                    <tr>
                        <td class="text-center font-bold">#{{ $rank + 1 }}</td>
                        <td class="">{{ $prod['sku'] }}</td>
                        <td style="font-weight: bold;">{{ $prod['product_name'] }}</td>
                        <td>{{ $prod['category_name'] }}</td>
                        <td class="text-center font-bold">{{ number_format($prod['total_qty'], 0, ',', '.') }}</td>
                        <td class="text-right  font-bold">Rp {{ number_format($prod['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data produk terlaris pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        @endif
    </table>

    <!-- Signatures -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; font-size: 9pt;">
            <tr style="text-align: center;">
                <td style="width: 33%; border: none;">
                    Dibuat oleh,<br><br><br><br>
                    <strong>( {{ auth()->user()?->name ?: 'Admin Kasir' }} )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Staf Operasional POS</span>
                </td>
                <td style="width: 33%; border: none;">
                    Diperiksa oleh,<br><br><br><br>
                    <strong>( __________________ )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Supervisor / Finance</span>
                </td>
                <td style="width: 33%; border: none;">
                    Disetujui oleh,<br><br><br><br>
                    <strong>( __________________ )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Manager / Owner</span>
                </td>
            </tr>
        </table>
    </div>
</div>
