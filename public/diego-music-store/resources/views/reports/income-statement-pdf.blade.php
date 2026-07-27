<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi (Income Statement) - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #111827;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
            color: #111827;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 9px;
            color: #374151;
        }
        .status-badge {
            display: block;
            padding: 6px 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            background-color: #f3f4f6;
            color: #111827;
            border: 1px solid #111827;
        }
        .section-header {
            background-color: #111827;
            color: #ffffff;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.data-table th {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #111827;
            border-bottom: 1.5px solid #111827;
            padding: 3px 4px;
            text-align: left;
        }
        table.data-table td {
            padding: 3px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .pl-detail {
            padding-left: 12px !important;
        }
        .subtotal-row {
            font-weight: bold;
            font-size: 9.5px;
            border-top: 1px dashed #9ca3af;
            background-color: #f9fafb;
            padding: 4px;
            margin-bottom: 6px;
        }
        .summary-box {
            background-color: #f3f4f6;
            border: 1px solid #111827;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .grand-total-box {
            background-color: #f9fafb;
            border: 2px solid #111827;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            color: #111827;
        }
        .signatures {
            margin-top: 35px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 60px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 9px;
            color: #111827;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">LAPORAN LABA RUGI (INCOME STATEMENT - MULTI STEP)</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="status-badge">
        HASIL PERIODE: {{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }} — Rp {{ number_format($data['net_income'], 0, ',', '.') }}
    </div>

    {{-- 1. PENDAPATAN OPERASIONAL --}}
    <div class="section-header">PENDAPATAN OPERASIONAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Kode</th>
                <th style="width: 57%;">Nama Akun / Kategori</th>
                <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['revenue']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                    <td class="font-mono">{{ $item['code'] }}</td>
                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                    <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-right" style="color: #6b7280;">Tidak ada Pendapatan Operasional</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="subtotal-row">
        <table style="width: 100%;">
            <tr>
                <td>TOTAL PENDAPATAN OPERASIONAL</td>
                <td class="text-right font-mono">Rp {{ number_format($data['revenue']['total'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- 2. HARGA POKOK PENJUALAN (COGS) --}}
    <div class="section-header">HARGA POKOK PENJUALAN (COGS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Kode</th>
                <th style="width: 57%;">Nama Akun / Kategori</th>
                <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['cogs']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                    <td class="font-mono">{{ $item['code'] }}</td>
                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                    <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-right" style="color: #6b7280;">Tidak ada HPP</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="subtotal-row">
        <table style="width: 100%;">
            <tr>
                <td>TOTAL HARGA POKOK PENJUALAN</td>
                <td class="text-right font-mono">Rp {{ number_format($data['cogs']['total'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- SUMMARY: LABA KOTOR --}}
    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td>LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right font-mono">Rp {{ number_format($data['gross_profit'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- 3. BEBAN OPERASIONAL --}}
    <div class="section-header">BEBAN OPERASIONAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Kode</th>
                <th style="width: 57%;">Nama Akun / Kategori</th>
                <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['operating_expenses']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                    <td class="font-mono">{{ $item['code'] }}</td>
                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                    <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-right" style="color: #6b7280;">Tidak ada Beban Operasional</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="subtotal-row">
        <table style="width: 100%;">
            <tr>
                <td>TOTAL BEBAN OPERASIONAL</td>
                <td class="text-right font-mono">Rp {{ number_format($data['operating_expenses']['total'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- SUMMARY: LABA OPERASIONAL --}}
    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td>LABA OPERASIONAL (OPERATING INCOME)</td>
                <td class="text-right font-mono">Rp {{ number_format($data['operating_income'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- 4. NON-OPERASIONAL (JIKA ADA) --}}
    @if(count($data['other_revenue']['items']) > 0 || count($data['other_expenses']['items']) > 0)
        <div class="section-header">PENDAPATAN & BEBAN NON-OPERASIONAL</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Kode</th>
                    <th style="width: 57%;">Nama Akun / Kategori</th>
                    <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['other_revenue']['items'] as $item)
                    <tr>
                        <td class="font-mono">{{ $item['code'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @foreach($data['other_expenses']['items'] as $item)
                    <tr>
                        <td class="font-mono">{{ $item['code'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-right font-mono">({{ number_format($item['balance'], 0, ',', '.') }})</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="subtotal-row">
            <table style="width: 100%;">
                <tr>
                    <td>NET NON-OPERATIONAL</td>
                    <td class="text-right font-mono">Rp {{ number_format($data['net_other'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- GRAND TOTAL: LABA BERSIH --}}
    <div class="grand-total-box">
        <table style="width: 100%;">
            <tr>
                <td>{{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}</td>
                <td class="text-right font-mono" style="font-size: 12px;">Rp {{ number_format($data['net_income'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">Disiapkan Oleh (Staf Akuntansi)</div>
            </td>
            <td>
                <div class="sig-line">Diperiksa Oleh (Manager Finance)</div>
            </td>
            <td>
                <div class="sig-line">Disetujui Oleh (Owner)</div>
            </td>
        </tr>
    </table>

</body>
</html>
