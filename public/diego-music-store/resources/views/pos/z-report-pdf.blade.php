<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Z-Report Sesi #{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 10px 12px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .store-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .store-sub {
            font-size: 8px;
            color: #475569;
            margin-bottom: 1px;
        }
        .divider-solid {
            border-top: 1px solid #94a3b8;
            margin: 6px 0;
        }
        .divider-dashed {
            border-top: 1px dashed #64748b;
            margin: 6px 0;
        }
        .divider-double {
            border-top: 2px double #334155;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .section-header {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            padding-top: 4px;
            padding-bottom: 2px;
        }
        .total-row td {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            padding: 3px 0;
        }
        .signatures {
            margin-top: 25px;
            width: 100%;
        }
        .sig-box {
            text-align: center;
            width: 48%;
            font-size: 8.5px;
        }
        .sig-line {
            border-bottom: 1px solid #0f172a;
            margin: 30px auto 4px auto;
            width: 80%;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7.5px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Store Header -->
    <div class="text-center" style="margin-bottom: 6px;">
        <div class="store-title">{{ $session->branch?->store_name ?: ($session->branch?->name ?: 'Diego Music Store') }}</div>
        <div class="store-sub bold">{{ $session->branch?->name }}</div>
        @if ($session->branch?->address)
            <div class="store-sub">{{ $session->branch?->address }}</div>
        @endif
        @if ($session->branch?->phone)
            <div class="store-sub">Telp: {{ $session->branch?->phone }}</div>
        @endif
    </div>

    <div class="divider-double"></div>

    <div class="text-center bold" style="font-size: 11px; margin: 4px 0;">
        Z-REPORT SHIFT KASIR
    </div>

    <!-- Metadata Sesi -->
    <table>
        <tr>
            <td class="text-left" style="width: 35%; color: #475569;">No. Sesi:</td>
            <td class="text-right bold">#{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #475569;">Kasir:</td>
            <td class="text-right bold">{{ $session->user?->name }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #475569;">Waktu Buka:</td>
            <td class="text-right">{{ $session->opened_at ? $session->opened_at->format('d/m/Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #475569;">Waktu Tutup:</td>
            <td class="text-right">{{ $session->closed_at ? $session->closed_at->format('d/m/Y H:i') : '-' }}</td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <!-- Rekonsiliasi Kas Laci -->
    <div class="section-header">REKONSILIASI KAS LACI</div>
    <table>
        <tr>
            <td class="text-left" style="color: #475569;">Modal Awal:</td>
            <td class="text-right">Rp {{ number_format($session->opening_cash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #475569;">Penjualan Tunai:</td>
            <td class="text-right">Rp {{ number_format($cashSalesSum, 0, ',', '.') }}</td>
        </tr>
        <tr style="border-top: 1px dashed #cbd5e1;">
            <td class="text-left bold">Total Teoritis:</td>
            <td class="text-right bold">Rp {{ number_format($session->expected_cash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left bold">Uang Fisik Laci:</td>
            <td class="text-right bold">Rp {{ number_format($session->actual_cash, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row" style="border-top: 1px solid #94a3b8;">
            <td class="text-left">SELISIH:</td>
            <td class="text-right">
                @if($session->difference === 0)
                    Rp 0 (Pas)
                @elseif($session->difference < 0)
                    -Rp {{ number_format(abs($session->difference), 0, ',', '.') }}
                @else
                    +Rp {{ number_format($session->difference, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <!-- Ringkasan Transaksi POS -->
    <div class="section-header">RINGKASAN TRANSAKSI POS</div>
    <table>
        <tr>
            <td class="text-left" style="color: #475569;">Penjualan Tunai:</td>
            <td class="text-right">{{ $cashSalesCount }} tx (Rp {{ number_format($cashSalesSum, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td class="text-left" style="color: #475569;">Penjualan Non-Tunai:</td>
            <td class="text-right">{{ $nonCashSalesCount }} tx (Rp {{ number_format($nonCashSalesSum, 0, ',', '.') }})</td>
        </tr>
        <tr class="total-row" style="border-top: 1px dashed #cbd5e1;">
            <td class="text-left">TOTAL PENJUALAN:</td>
            <td class="text-right">{{ $totalSalesCount }} tx (Rp {{ number_format($totalSalesSum, 0, ',', '.') }})</td>
        </tr>
    </table>

    @if($session->notes)
        <div class="divider-dashed"></div>
        <div class="bold" style="font-size: 8.5px; color: #475569;">Catatan Kasir:</div>
        <div style="font-style: italic; font-size: 8px; margin-top: 2px;">
            "{{ $session->notes }}"
        </div>
    @endif

    @if($session->closed_by_user_id && $session->closed_by_user_id !== $session->user_id)
        <div class="divider-dashed"></div>
        <table>
            <tr>
                <td class="text-left" style="color: #475569;">Disetujui Oleh:</td>
                <td class="text-right bold">{{ $session->closedBy?->name ?? 'Supervisor' }}</td>
            </tr>
        </table>
    @endif

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div>Kasir</div>
                <div class="sig-line"></div>
                <div class="bold">{{ $session->user?->name }}</div>
            </td>
            <td class="sig-box">
                <div>Supervisor</div>
                <div class="sig-line"></div>
                <div class="bold">{{ $session->closedBy ? $session->closedBy->name : '________________' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }}<br>
        Diego Music Store ERP
    </div>
</body>
</html>
