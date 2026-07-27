<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Balance Sheet (Neraca) - {{ $data['as_of_date'] }}</title>
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
        }
        .status-balanced {
            background-color: #f3f4f6;
            color: #111827;
            border: 1px solid #111827;
        }
        .status-unbalanced {
            background-color: #e5e7eb;
            color: #111827;
            border: 1px dashed #111827;
        }
        .skontro-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .col-skontro {
            width: 48%;
            vertical-align: top;
        }
        .col-spacer {
            width: 4%;
        }
        .section-header {
            background-color: #111827;
            color: #ffffff;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .sub-header {
            background-color: #f3f4f6;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 9.5px;
            border-top: 1px solid #9ca3af;
            border-bottom: 1px solid #9ca3af;
            margin-top: 6px;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #111827;
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
            padding-top: 3px;
            padding-bottom: 3px;
        }
        .total-box {
            background-color: #f9fafb;
            border: 1.5px solid #111827;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 10.5px;
            margin-top: 12px;
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
        <div class="report-title">LAPORAN BALANCE SHEET (NERACA - SKONTRO)</div>
        <div class="report-meta">
            Per Tanggal: <strong>{{ \Illuminate\Support\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong>
            &bull; Cabang: <strong>{{ $data['branch_name'] }}</strong>
            &bull; Dicetak Pada: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if($data['is_balanced'])
        <div class="status-badge status-balanced">
            STATUS: NERACA SEIMBANG (BALANCED) — Total Aset = Total Liabilitas + Ekuitas
        </div>
    @else
        <div class="status-badge status-unbalanced">
            STATUS: Terdapat Selisih (Rp {{ number_format(abs($data['difference']), 0, ',', '.') }})
        </div>
    @endif

    {{-- STANDARD SKONTRO FORMAT (2 EQUAL COLUMNS - PRINTER FRIENDLY MONOCHROME) --}}
    <table class="skontro-table">
        <tr>
            {{-- LEFT COLUMN: ASET / AKTIVA --}}
            <td class="col-skontro">
                <div class="section-header">ASET (AKTIVA)</div>

                {{-- ASET LANCAR --}}
                <div class="sub-header">Aset Lancar</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Kode</th>
                            <th style="width: 55%;">Nama Akun</th>
                            <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['assets']['current_assets'] as $item)
                            <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                                <td class="font-mono">{{ $item['code'] }}</td>
                                <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                                <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-right" style="color: #6b7280;">Tidak ada Aset Lancar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <table style="width: 100%;" class="subtotal-row">
                    <tr>
                        <td>Total Aset Lancar</td>
                        <td class="text-right font-mono">Rp {{ number_format($data['assets']['total_current_assets'], 0, ',', '.') }}</td>
                    </tr>
                </table>

                {{-- ASET TETAP --}}
                @if(count($data['assets']['fixed_assets']) > 0)
                    <div class="sub-header" style="margin-top: 10px;">Aset Tetap</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Kode</th>
                                <th style="width: 55%;">Nama Akun</th>
                                <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['assets']['fixed_assets'] as $item)
                                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                                    <td class="font-mono">{{ $item['code'] }}</td>
                                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                                    <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table style="width: 100%;" class="subtotal-row">
                        <tr>
                            <td>Total Aset Tetap</td>
                            <td class="text-right font-mono">Rp {{ number_format($data['assets']['total_fixed_assets'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif

                <div class="total-box">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL ASET (AKTIVA)</td>
                            <td class="text-right font-mono" style="font-size: 11px;">Rp {{ number_format($data['total_assets'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>

            <td class="col-spacer"></td>

            {{-- RIGHT COLUMN: LIABILITIES & EQUITY / PASIVA --}}
            <td class="col-skontro">
                <div class="section-header">LIABILITAS & EKUITAS (PASIVA)</div>

                {{-- KEWAJIBAN JANGKA PENDEK --}}
                <div class="sub-header">Kewajiban Jangka Pendek</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Kode</th>
                            <th style="width: 55%;">Nama Akun</th>
                            <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['liabilities']['current_liabilities'] as $item)
                            <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                                <td class="font-mono">{{ $item['code'] }}</td>
                                <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                                <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-right" style="color: #6b7280;">Tidak ada Kewajiban Jangka Pendek</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <table style="width: 100%;" class="subtotal-row">
                    <tr>
                        <td>Total Kewajiban Jangka Pendek</td>
                        <td class="text-right font-mono">Rp {{ number_format($data['liabilities']['total_current_liabilities'], 0, ',', '.') }}</td>
                    </tr>
                </table>

                {{-- KEWAJIBAN JANGKA PANJANG --}}
                @if(count($data['liabilities']['long_term_liabilities']) > 0)
                    <div class="sub-header" style="margin-top: 10px;">Kewajiban Jangka Panjang</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Kode</th>
                                <th style="width: 55%;">Nama Akun</th>
                                <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['liabilities']['long_term_liabilities'] as $item)
                                <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                                    <td class="font-mono">{{ $item['code'] }}</td>
                                    <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                                    <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table style="width: 100%;" class="subtotal-row">
                        <tr>
                            <td>Total Kewajiban Jangka Panjang</td>
                            <td class="text-right font-mono">Rp {{ number_format($data['liabilities']['total_long_term_liabilities'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                @endif

                <div style="font-weight: bold; font-size: 9.5px; padding: 4px 6px; background-color: #f3f4f6; margin-top: 6px; border: 1px solid #111827;">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL KEWAJIBAN</td>
                            <td class="text-right font-mono">Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                {{-- EKUITAS --}}
                <div class="sub-header" style="margin-top: 10px;">Ekuitas</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Kode</th>
                            <th style="width: 55%;">Nama Akun</th>
                            <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['equity']['items'] as $item)
                            <tr class="{{ $item['is_header'] ? 'font-bold' : '' }}">
                                <td class="font-mono">{{ $item['code'] }}</td>
                                <td class="{{ $item['is_header'] ? '' : 'pl-detail' }}">{{ $item['name'] }}</td>
                                <td class="text-right font-mono">{{ number_format($item['balance'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #f3f4f6; font-weight: bold;">
                            <td class="font-mono">-</td>
                            <td class="pl-detail">Laba / (Rugi) Periode Berjalan</td>
                            <td class="text-right font-mono">{{ number_format($data['equity']['current_net_income'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="font-weight: bold; font-size: 9.5px; padding: 4px 6px; background-color: #f3f4f6; margin-top: 6px; border: 1px solid #111827;">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL EKUITAS</td>
                            <td class="text-right font-mono">Rp {{ number_format($data['total_equity'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="total-box">
                    <table style="width: 100%;">
                        <tr>
                            <td>TOTAL LIABILITAS & EKUITAS</td>
                            <td class="text-right font-mono" style="font-size: 11px;">Rp {{ number_format($data['total_liabilities_and_equity'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
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
