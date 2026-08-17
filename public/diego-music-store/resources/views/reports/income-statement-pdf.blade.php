<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi (Income Statement) - {{ $data['from_date'] }} s.d. {{ $data['to_date'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
            padding: 10px;
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
            color: #111827;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 3px;
        }
        .report-meta {
            font-size: 8.5px;
            color: #374151;
        }
        table.statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.statement-table th {
            font-size: 8px;
            text-transform: uppercase;
            color: #111827;
            border: 1px solid #111827;
            padding: 4px 3px;
            background-color: #e5e7eb;
            text-align: center;
        }
        table.statement-table td {
            padding: 3.5px 4px;
            border: 1px solid #d1d5db;
            font-size: 8.5px;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            font-weight: bold;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-category {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .bg-subtotal {
            background-color: #e5e7eb;
            font-weight: bold;
        }
        .bg-gross {
            background-color: #fef3c7;
            font-weight: bold;
            border-top: 1.5px solid #111827 !important;
            border-bottom: 1.5px solid #111827 !important;
        }
        .bg-net {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 9.5px;
            border-top: 2px solid #111827 !important;
            border-bottom: 2px solid #111827 !important;
        }
        .signatures {
            margin-top: 25px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 33%;
            vertical-align: bottom;
            height: 50px;
        }
        .sig-line {
            border-top: 1px solid #111827;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 8.5px;
            color: #111827;
        }
    </style>
</head>
<body>

    @php
        $fmt = function ($val) {
            return \App\Helpers\FinancialReportHelper::formatNumber($val);
        };
        $mLabel = strtoupper($data['period_month_label'] ?? 'PERIODE');
    @endphp

    <div class="header">
        <div class="company-name">DIEGO MUSIC STORE</div>
        <div class="report-title">LAPORAN LABA RUGI (INCOME STATEMENT - COMPARATIVE)</div>
        <div class="report-meta">
            Periode: <strong>{{ \Illuminate\Support\Carbon::parse($data['from_date'])->format('d/m/Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($data['to_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 34%; text-align: left; padding-left: 6px;">DESCRIPTION</th>
                <th colspan="3" style="width: 33%;">{{ $mLabel }}</th>
                <th colspan="3" style="width: 33%;">YTD (YEAR TO DATE)</th>
            </tr>
            <tr>
                <th style="width: 11%;">TOKO</th>
                <th style="width: 11%;">GUDANG</th>
                <th style="width: 11%;">TOTAL</th>
                <th style="width: 11%;">TOKO</th>
                <th style="width: 11%;">GUDANG</th>
                <th style="width: 11%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. PENJUALAN --}}
            <tr class="bg-category">
                <td colspan="7">PENJUALAN (REVENUE)</td>
            </tr>
            @forelse($data['revenue']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold bg-category' : '' }}">
                    <td style="padding-left: {{ max(4, ($item['level'] - 1) * 10) }}px;">
                        <span class="font-mono" style="color: #4b5563;">{{ $item['code'] }}</span> {{ $item['name'] }}
                    </td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['balance']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['ytd_total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-right" style="color: #6b7280;">Tidak ada Penjualan</td>
                </tr>
            @endforelse
            <tr class="bg-subtotal">
                <td>TOTAL PENJUALAN</td>
                <td class="text-right font-mono">{{ $fmt($data['revenue']['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['revenue']['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['revenue']['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['revenue']['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['revenue']['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['revenue']['ytd_total']) }}</td>
            </tr>

            {{-- 2. PEMBELIAN DAN HPP --}}
            <tr class="bg-category">
                <td colspan="7">PEMBELIAN DAN HPP (COGS)</td>
            </tr>
            @forelse($data['cogs']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold bg-category' : '' }}">
                    <td style="padding-left: {{ max(4, ($item['level'] - 1) * 10) }}px;">
                        <span class="font-mono" style="color: #4b5563;">{{ $item['code'] }}</span> {{ $item['name'] }}
                    </td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['balance']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['ytd_total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-right" style="color: #6b7280;">Tidak ada HPP</td>
                </tr>
            @endforelse
            <tr class="bg-subtotal">
                <td>TOTAL PEMBELIAN DAN HPP</td>
                <td class="text-right font-mono">{{ $fmt($data['cogs']['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['cogs']['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['cogs']['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['cogs']['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['cogs']['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['cogs']['ytd_total']) }}</td>
            </tr>

            {{-- SUMMARY: LABA KOTOR --}}
            @php $gp = $data['gross_profit_details']; @endphp
            <tr class="bg-gross">
                <td>LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right font-mono">{{ $fmt($gp['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($gp['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($gp['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($gp['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($gp['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($gp['ytd_total']) }}</td>
            </tr>

            {{-- 3. BEBAN OPERASIONAL --}}
            <tr class="bg-category">
                <td colspan="7">BEBAN OPERASIONAL (OPERATING EXPENSES)</td>
            </tr>
            @forelse($data['operating_expenses']['items'] as $item)
                <tr class="{{ $item['is_header'] ? 'font-bold bg-category' : '' }}">
                    <td style="padding-left: {{ max(4, ($item['level'] - 1) * 10) }}px;">
                        <span class="font-mono" style="color: #4b5563;">{{ $item['code'] }}</span> {{ $item['name'] }}
                    </td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['balance_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['balance']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_toko']) }}</td>
                    <td class="text-right font-mono">{{ $fmt($item['ytd_gudang']) }}</td>
                    <td class="text-right font-mono font-bold">{{ $fmt($item['ytd_total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-right" style="color: #6b7280;">Tidak ada Beban Operasional</td>
                </tr>
            @endforelse
            <tr class="bg-subtotal">
                <td>TOTAL BEBAN OPERASIONAL</td>
                <td class="text-right font-mono">{{ $fmt($data['operating_expenses']['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['operating_expenses']['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['operating_expenses']['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['operating_expenses']['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($data['operating_expenses']['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($data['operating_expenses']['ytd_total']) }}</td>
            </tr>

            {{-- SUMMARY: LABA / (RUGI) OPERASIONAL --}}
            @php $opInc = $data['operating_income_details']; @endphp
            <tr class="bg-subtotal" style="border-top: 1.5px solid #111827;">
                <td>LABA / (RUGI) OPERASIONAL</td>
                <td class="text-right font-mono">{{ $fmt($opInc['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($opInc['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($opInc['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($opInc['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($opInc['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($opInc['ytd_total']) }}</td>
            </tr>

            {{-- FINAL GRAND TOTAL: LABA BERSIH --}}
            @php $netInc = $data['net_income_details']; @endphp
            <tr class="bg-net">
                <td>{{ $data['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}</td>
                <td class="text-right font-mono">{{ $fmt($netInc['toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($netInc['gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($netInc['total']) }}</td>
                <td class="text-right font-mono">{{ $fmt($netInc['ytd_toko']) }}</td>
                <td class="text-right font-mono">{{ $fmt($netInc['ytd_gudang']) }}</td>
                <td class="text-right font-mono font-bold">{{ $fmt($netInc['ytd_total']) }}</td>
            </tr>
        </tbody>
    </table>

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
