<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Retur Pembelian Supplier</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #1e293b;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            margin-top: 15px;
            border: 1px solid #0f172a;
            padding: 10px;
            background-color: #f8fafc;
        }
        .font-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>DIEGO MUSIC STORE</h2>
        <p>LAPORAN RETUR PEMBELIAN BARANG KE SUPPLIER</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Periode</strong></td>
            <td width="35%">: {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</td>
            <td width="15%"><strong>Status Retur</strong></td>
            <td width="35%">: {{ strtoupper($data['status']) }}</td>
        </tr>
        <tr>
            <td><strong>Cabang</strong></td>
            <td>: {{ $data['branch_name'] }}</td>
            <td><strong>Supplier</strong></td>
            <td>: {{ $data['supplier_name'] }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="12%">No. Retur</th>
                <th width="14%">Ref Transaksi</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Supplier</th>
                <th width="10%">Status</th>
                @if ($data['mode'] === 'detail')
                    <th width="20%">Nama Produk</th>
                    <th width="7%" class="text-right">Qty</th>
                @else
                    <th width="10%" class="text-right">Total Qty</th>
                @endif
                <th width="12%" class="text-right">Total Retur</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['returns'] as $ret)
                @if ($data['mode'] === 'detail')
                    @foreach ($ret['items'] as $index => $item)
                        <tr>
                            @if ($index === 0)
                                <td rowspan="{{ count($ret['items']) }}" class="font-bold">{{ $ret['return_no'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['transaction_no'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['return_date'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['supplier_name'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['status_label'] }}</td>
                            @endif
                            <td>{{ $item['product_name'] }}</td>
                            <td class="text-right font-bold">{{ $item['qty'] }}</td>
                            @if ($index === 0)
                                <td rowspan="{{ count($ret['items']) }}" class="text-right font-bold">
                                    Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="font-bold">{{ $ret['return_no'] }}</td>
                        <td>{{ $ret['transaction_no'] }}</td>
                        <td>{{ $ret['return_date'] }}</td>
                        <td>{{ $ret['supplier_name'] }}</td>
                        <td>{{ $ret['status_label'] }}</td>
                        <td class="text-right font-bold">{{ $ret['total_qty'] }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" class="text-center italic">Tidak ada data retur pembelian supplier untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <strong>RINGKASAN TOTAL:</strong><br>
        • Total Dokumen Retur: <strong>{{ number_format($data['total_transactions'], 0, ',', '.') }} Transaksi</strong><br>
        • Total Unit Barang Diretur: <strong>{{ number_format($data['total_qty_returned'], 0, ',', '.') }} Unit</strong><br>
        • Total Nilai Retur / Refund: <strong>Rp {{ number_format($data['total_return_amount'], 0, ',', '.') }}</strong>
    </div>
</body>
</html>
