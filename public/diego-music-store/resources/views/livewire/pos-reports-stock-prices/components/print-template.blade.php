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
                font-size: 8pt;
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
            LAPORAN DAFTAR STOK & NILAI PERSEDIAAN
        </h2>
        <div style="font-size: 9.5pt; margin-top: 4px; font-weight: bold;">
            CABANG: {{ strtoupper($reportData['branch_name'] ?? 'SEMUA CABANG') }} &bull; KATEGORI: {{ strtoupper($reportData['category'] ?? 'SEMUA KATEGORI') }}
        </div>
    </div>

    <!-- Summary -->
    <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt;">
        <tr>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Total SKU / Varian</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class=" text-right">{{ number_format($reportData['total_variants'] ?? 0, 0, ',', '.') }} SKU ({{ number_format($reportData['total_physical_qty'] ?? 0, 0, ',', '.') }} pcs)</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6; width: 25%;">Grand Total Nilai HPP</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class=" text-right">Rp {{ number_format($reportData['grand_total_valuation'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Status Stok Kritis</td>
            <td style="border: 1px solid #000; padding: 5px;" class=" text-right">{{ number_format($reportData['total_out_of_stock_count'] ?? 0, 0, ',', '.') }} Habis | {{ number_format($reportData['total_low_stock_count'] ?? 0, 0, ',', '.') }} Rendah</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold; background: #f3f4f6;">Potensi Nilai Jual</td>
            <td style="border: 1px solid #000; padding: 5px; font-weight: bold;" class=" text-right">Rp {{ number_format($reportData['grand_total_retail_value'] ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Grid -->
    <table class="erp-print-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">NO</th>
                <th style="width: 100px;">SKU / BARCODE</th>
                <th>NAMA PRODUK & VARIASI</th>
                <th>KATEGORI</th>
                <th>MERK</th>
                <th style="width: 45px;" class="text-center">STOK</th>
                <th style="width: 45px;" class="text-center">MIN</th>
                <th style="width: 45px;" class="text-center">SATUAN</th>
                <th style="width: 70px;" class="text-center">STATUS</th>
                <th style="width: 75px;" class="text-right">HARGA HPP</th>
                <th style="width: 75px;" class="text-right">HARGA JUAL</th>
                <th style="width: 90px;" class="text-right">NILAI HPP</th>
                <th style="width: 90px;" class="text-right">NILAI JUAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData['rows'] ?? [] as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class=" font-bold">{{ $row['sku'] }}<br><span style="font-size: 7.5pt; font-weight: normal; color: #444;">BC: {{ $row['barcode'] }}</span></td>
                    <td style="font-weight: bold;">{{ $row['full_name'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['brand'] }}</td>
                    <td class="text-center  font-bold">{{ number_format($row['stock'], 0, ',', '.') }}</td>
                    <td class="text-center ">{{ number_format($row['min_stock'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['unit'] }}</td>
                    <td class="text-center font-bold" style="font-size: 7.5pt;">{{ $row['status_label'] }}</td>
                    <td class="text-right ">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                    <td class="text-right ">Rp {{ number_format($row['retail_price'], 0, ',', '.') }}</td>
                    <td class="text-right  font-bold">Rp {{ number_format($row['valuation'], 0, ',', '.') }}</td>
                    <td class="text-right  font-bold">Rp {{ number_format($row['retail_value'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 15px; font-style: italic;">Belum ada data stok produk ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures -->
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; font-size: 9pt;">
            <tr style="text-align: center;">
                <td style="width: 33%; border: none;">
                    Dibuat oleh,<br><br><br><br>
                    <strong>( {{ auth()->user()?->name ?: 'Admin Kasir' }} )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Staf Logistik / POS</span>
                </td>
                <td style="width: 33%; border: none;">
                    Diperiksa oleh,<br><br><br><br>
                    <strong>( __________________ )</strong><br>
                    <span style="font-size: 8pt; color: #444;">Kepala Gudang / Supervisor</span>
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
