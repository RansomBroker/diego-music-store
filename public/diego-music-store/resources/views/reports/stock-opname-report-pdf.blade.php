<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Opname & Audit - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #111827;
            margin: 0;
            padding: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #111827;
            padding-bottom: 6px;
        }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .report-meta {
            font-size: 9px;
            color: #374151;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            font-size: 8.5px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #111827;
            padding: 4px 5px;
            text-align: left;
            background-color: #f3f4f6;
        }
        table.data-table td {
            padding: 4px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .subtotal-box {
            background-color: #f3f4f6;
            border: 1.5px solid #111827;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 9.5px;
            margin-top: 10px;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 55px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 3px;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">
            LAPORAN & AUDIT STOK OPNAME {{ strtoupper($data['mode']) === 'DETAIL' ? '(RINCIAN DETAIL BARANG)' : '(RINGKASAN SESI)' }}
        </div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Status: <strong>{{ strtoupper($data['status_filter']) }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['mode'] === 'detail')
        @foreach($data['opnames'] as $op)
            <div style="background-color: #111827; color: #ffffff; padding: 4px 8px; font-weight: bold; font-size: 9.5px; margin-top: 8px;">
                OPNAME: {{ $op['opname_number'] }} &bull; Cabang: {{ $op['branch_name'] }} &bull; Tgl: {{ \Illuminate\Support\Carbon::parse($op['opname_date'])->format('d/m/Y') }} &bull; Status: {{ $op['status'] }} &bull; Nilai Adjustment: Rp {{ number_format($op['session_adjustment_value'], 0, ',', '.') }}
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 25%;">Nama Produk & Variasi</th>
                        <th style="width: 9%;" class="text-center">Sistem</th>
                        <th style="width: 9%;" class="text-center">Fisik</th>
                        <th style="width: 9%;" class="text-center">Selisih</th>
                        <th style="width: 11%;" class="text-center">Status Audit</th>
                        <th style="width: 11%;" class="text-right">Harga Beli (Rp)</th>
                        <th style="width: 11%;" class="text-right">Nilai Adj (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($op['items'] as $item)
                        <tr>
                            <td class="font-mono font-bold">{{ $item['sku'] }}</td>
                            <td class="font-bold">{{ $item['full_name'] }}</td>
                            <td class="text-center font-mono">{{ number_format($item['system_qty'], 0, ',', '.') }}</td>
                            <td class="text-center font-mono font-bold">{{ number_format($item['physical_qty'], 0, ',', '.') }}</td>
                            <td class="text-center font-mono font-bold">{{ $item['difference'] > 0 ? '+' : '' }}{{ number_format($item['difference'], 0, ',', '.') }}</td>
                            <td class="text-center font-bold">{{ $item['item_status_label'] }}</td>
                            <td class="text-right font-mono">{{ number_format($item['cost_price'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold">{{ number_format($item['adjustment_value'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">No. Opname</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 18%;">Cabang</th>
                    <th style="width: 10%;" class="text-center">Status</th>
                    <th style="width: 9%;" class="text-center">Item</th>
                    <th style="width: 9%;" class="text-center">Qty Sistem</th>
                    <th style="width: 9%;" class="text-center">Qty Fisik</th>
                    <th style="width: 8%;" class="text-center">Selisih</th>
                    <th style="width: 12%;" class="text-right">Total Adj (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['opnames'] as $op)
                    <tr>
                        <td class="font-mono font-bold">{{ $op['opname_number'] }}</td>
                        <td class="font-mono">{{ \Illuminate\Support\Carbon::parse($op['opname_date'])->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $op['branch_name'] }}</td>
                        <td class="text-center font-bold">{{ $op['status'] }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($op['items_count'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono">{{ number_format($op['session_system_qty'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($op['session_physical_qty'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ $op['session_diff_qty'] > 0 ? '+' : '' }}{{ number_format($op['session_diff_qty'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($op['session_adjustment_value'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="color: #6b7280; font-style: italic;">Tidak ada data stok opname yang sesuai dengan filter</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="subtotal-box">
        <table style="width: 100%;">
            <tr>
                <td>RINGKASAN AUDIT: {{ number_format($data['total_opname_sessions'], 0, ',', '.') }} Sesi Opname &bull; {{ number_format($data['total_items_audited'], 0, ',', '.') }} Item Audited &bull; Net Selisih Qty: {{ $data['total_net_variance_qty'] > 0 ? '+' : '' }}{{ number_format($data['total_net_variance_qty'], 0, ',', '.') }} Unit</td>
                <td class="text-right font-mono">TOTAL ADJUSTMENT: Rp {{ number_format($data['grand_total_adjustment_value'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Auditor Stok / Petugas)</div>
            </td>
            <td>
                <div class="sig-line">Diperiksa Oleh (Manager Logistics)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
