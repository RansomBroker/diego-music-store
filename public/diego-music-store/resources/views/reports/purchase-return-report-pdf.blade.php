<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Retur Pembelian Supplier (Buku Besar)</title>
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
            margin-bottom: 8px;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            font-size: 8.5px;
        }
        .table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
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
            page-break-inside: avoid;
        }
        .font-bold {
            font-weight: bold;
        }
        .supplier-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .supplier-title {
            background-color: #e2e8f0;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 10.5px;
            border: 1px solid #cbd5e1;
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>DIEGO MUSIC STORE</h2>
        <p>LAPORAN RETUR PEMBELIAN (BUKU BESAR SUPPLIER)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Periode</strong></td>
            <td width="35%">: {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</td>
            <td width="18%"><strong>Metode Penyelesaian</strong></td>
            <td width="32%">: {{ $data['return_type_label'] }}</td>
        </tr>
        <tr>
            <td><strong>Cabang</strong></td>
            <td>: {{ $data['branch_name'] }}</td>
            <td><strong>Status Retur</strong></td>
            <td>: {{ strtoupper($data['status']) }}</td>
        </tr>
        <tr>
            <td><strong>Filter Supplier</strong></td>
            <td>: {{ $data['supplier_name'] }}</td>
            <td><strong>Mode Laporan</strong></td>
            <td>: {{ strtoupper($data['mode']) }}</td>
        </tr>
    </table>

    @if ($data['mode'] === 'ledger' || $data['mode'] === 'by_supplier')
        {{-- Mode Buku Besar: Setiap Supplier Memiliki Tabel Sendiri --}}
        @forelse ($data['supplier_ledgers'] as $supplier)
            <div class="supplier-block">
                <div class="supplier-title">
                    BUKU RETUR SUPPLIER: {{ strtoupper($supplier['supplier_name']) }} &nbsp;|&nbsp; 
                    <span style="font-size: 9px; font-weight: normal;">
                        Telp: {{ $supplier['phone'] }} &bull; Total: {{ $supplier['total_transactions'] }} Transaksi &bull; {{ $supplier['total_qty_returned'] }} Unit Diretur &bull; Total Nilai: Rp {{ number_format($supplier['total_amount'], 0, ',', '.') }}
                    </span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th width="10%">Tanggal</th>
                            <th width="12%">No. Retur</th>
                            <th width="12%">Ref PT</th>
                            <th width="14%">Metode Retur</th>
                            <th width="7%">Status</th>
                            <th width="11%">SKU</th>
                            <th width="18%">Nama Produk / Barang</th>
                            <th width="5%" class="text-right">Qty</th>
                            <th width="11%" class="text-right">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($supplier['entries'] as $entry)
                            @php $itemCount = count($entry['items']); @endphp
                            @foreach ($entry['items'] as $idx => $item)
                                <tr>
                                    @if ($idx === 0)
                                        <td rowspan="{{ $itemCount }}">{{ $entry['return_date'] }}</td>
                                        <td rowspan="{{ $itemCount }}" class="font-bold">{{ $entry['return_no'] }}</td>
                                        <td rowspan="{{ $itemCount }}">{{ $entry['transaction_no'] }}</td>
                                        <td rowspan="{{ $itemCount }}">
                                            {{ $entry['return_type_label'] }}
                                            @if ($entry['refund_account_name'])
                                                <br><small style="color: #64748b;">({{ $entry['refund_account_name'] }})</small>
                                            @endif
                                        </td>
                                        <td rowspan="{{ $itemCount }}" class="text-center">{{ $entry['status_label'] }}</td>
                                    @endif
                                    <td>{{ $item['sku'] }}</td>
                                    <td>{{ $item['product_name'] }}</td>
                                    <td class="text-right font-bold">{{ $item['qty'] }}</td>
                                    <td class="text-right font-bold">Rp {{ number_format($item['total_price'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="text-center italic">Tidak ada transaksi retur untuk supplier ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f1f5f9; font-weight: bold;">
                            <td colspan="7" class="text-right uppercase">
                                TOTAL RETUR {{ strtoupper($supplier['supplier_name']) }}:
                            </td>
                            <td class="text-right">{{ $supplier['total_qty_returned'] }} Unit</td>
                            <td class="text-right">Rp {{ number_format($supplier['total_amount'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
                <div style="font-size: 8.5px; color: #475569; margin-bottom: 5px;">
                    <em>Rincian Metode:</em>
                    Tukar Guling: {{ $supplier['by_type']['replacement']['qty'] }} Unit &bull;
                    Potong Faktur: Rp {{ number_format($supplier['by_type']['invoice_deduction']['amount'], 0, ',', '.') }} &bull;
                    Refund Kas/Bank: Rp {{ number_format($supplier['by_type']['refund']['amount'], 0, ',', '.') }} &bull;
                    Saldo Deposit: Rp {{ number_format($supplier['by_type']['supplier_credit']['amount'], 0, ',', '.') }}
                </div>
            </div>
        @empty
            <p class="text-center italic">Tidak ada data retur pembelian supplier untuk periode ini.</p>
        @endforelse

    @elseif ($data['mode'] === 'detail')
        {{-- Mode Detail Gabungan --}}
        <table class="table">
            <thead>
                <tr>
                    <th width="11%">No. Retur</th>
                    <th width="10%">Tanggal</th>
                    <th width="14%">Supplier</th>
                    <th width="13%">Metode Retur</th>
                    <th width="7%">Status</th>
                    <th width="10%">SKU</th>
                    <th width="17%">Nama Produk</th>
                    <th width="6%" class="text-right">Qty</th>
                    <th width="12%" class="text-right">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['returns'] as $ret)
                    @foreach ($ret['items'] as $index => $item)
                        <tr>
                            @if ($index === 0)
                                <td rowspan="{{ count($ret['items']) }}" class="font-bold">{{ $ret['return_no'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['return_date'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['supplier_name'] }}</td>
                                <td rowspan="{{ count($ret['items']) }}">
                                    {{ $ret['return_type_label'] }}
                                    @if ($ret['refund_account_name'])
                                        <br><small style="color: #64748b;">({{ $ret['refund_account_name'] }})</small>
                                    @endif
                                </td>
                                <td rowspan="{{ count($ret['items']) }}">{{ $ret['status_label'] }}</td>
                            @endif
                            <td>{{ $item['sku'] }}</td>
                            <td>{{ $item['product_name'] }}</td>
                            <td class="text-right font-bold">{{ $item['qty'] }}</td>
                            @if ($index === 0)
                                <td rowspan="{{ count($ret['items']) }}" class="text-right font-bold">
                                    Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="9" class="text-center italic">Tidak ada data retur pembelian supplier untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        {{-- Mode Summary Gabungan --}}
        <table class="table">
            <thead>
                <tr>
                    <th width="13%">No. Retur</th>
                    <th width="13%">Ref Transaksi PT</th>
                    <th width="10%">Tanggal</th>
                    <th width="18%">Supplier</th>
                    <th width="16%">Metode Penyelesaian</th>
                    <th width="10%">Status</th>
                    <th width="8%" class="text-right">Qty</th>
                    <th width="12%" class="text-right">Total Retur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['returns'] as $ret)
                    <tr>
                        <td class="font-bold">{{ $ret['return_no'] }}</td>
                        <td>{{ $ret['transaction_no'] }}</td>
                        <td>{{ $ret['return_date'] }}</td>
                        <td>{{ $ret['supplier_name'] }}</td>
                        <td>
                            {{ $ret['return_type_label'] }}
                            @if ($ret['refund_account_name'])
                                <br><small style="color: #64748b;">({{ $ret['refund_account_name'] }})</small>
                            @endif
                        </td>
                        <td>{{ $ret['status_label'] }}</td>
                        <td class="text-right font-bold">{{ $ret['total_qty'] }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($ret['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center italic">Tidak ada data retur pembelian supplier untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="summary-box">
        <strong>RINGKASAN TOTAL RETUR PEMBELIAN (KONSOLIDASI):</strong><br>
        <table width="100%" style="margin-top: 5px;">
            <tr>
                <td width="33%">• Total Dokumen Retur: <strong>{{ number_format($data['total_transactions'], 0, ',', '.') }} Dokumen</strong></td>
                <td width="33%">• Total Unit Barang: <strong>{{ number_format($data['total_qty_returned'], 0, ',', '.') }} Unit</strong></td>
                <td width="34%">• Total Nilai Retur: <strong>Rp {{ number_format($data['total_return_amount'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="padding-top: 4px;">&bull; Tukar Guling: <strong>{{ $data['breakdown_by_type']['replacement']['qty'] }} Unit</strong> ({{ $data['breakdown_by_type']['replacement']['count'] }} Dok)</td>
                <td style="padding-top: 4px;">&bull; Penyesuaian Faktur: <strong>Rp {{ number_format($data['breakdown_by_type']['invoice_deduction']['amount'], 0, ',', '.') }}</strong></td>
                <td style="padding-top: 4px;">&bull; Refund Kas/Bank: <strong>Rp {{ number_format($data['breakdown_by_type']['refund']['amount'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top: 4px;">&bull; Saldo Deposit Supplier: <strong>Rp {{ number_format($data['breakdown_by_type']['supplier_credit']['amount'], 0, ',', '.') }}</strong> ({{ $data['breakdown_by_type']['supplier_credit']['count'] }} Dok)</td>
            </tr>
        </table>
    </div>
</body>
</html>
