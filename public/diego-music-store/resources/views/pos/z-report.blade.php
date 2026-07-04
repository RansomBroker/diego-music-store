<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Z-Report - Sesi #{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
            max-width: 300px; /* 80mm standard width */
            margin: 0 auto;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 15px;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .double-divider {
            border-top: 2px double #000;
            margin: 10px 0;
        }
        .grid {
            display: flex;
            justify-content: space-between;
        }
        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 45%;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 5px;
        }
        @media print {
            body {
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer;">CETAK</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; margin-left: 8px;">TUTUP</button>
        <hr>
    </div>

    <div class="header text-center">
        <span class="store-name">{{ $session->branch->store_name ?: 'Diego Music Store' }}</span><br>
        <span>{{ $session->branch->name }}</span><br>
        <span>Telp: {{ $session->branch->phone }}</span>
    </div>

    <div class="double-divider"></div>

    <div class="text-center bold" style="font-size: 14px; margin-bottom: 10px;">
        Z-REPORT SHIFT KASIR
    </div>

    <div class="grid">
        <span>No. Sesi:</span>
        <span class="bold">#{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="grid">
        <span>Kasir:</span>
        <span>{{ $session->user->name }}</span>
    </div>
    <div class="grid">
        <span>Mulai:</span>
        <span>{{ $session->opened_at->format('d/m/y H:i') }}</span>
    </div>
    <div class="grid">
        <span>Selesai:</span>
        <span>{{ $session->closed_at ? $session->closed_at->format('d/m/y H:i') : '-' }}</span>
    </div>

    <div class="divider"></div>

    <div class="section-title">REKONSILIASI KAS LACI</div>
    
    <div class="grid">
        <span>Modal Awal:</span>
        <span>Rp {{ number_format($session->opening_cash, 0, ',', '.') }}</span>
    </div>
    <div class="grid">
        <span>Penjualan Tunai:</span>
        <span>Rp {{ number_format($cashSalesSum, 0, ',', '.') }}</span>
    </div>
    <div class="double-divider"></div>
    <div class="grid bold">
        <span>Total Teoritis:</span>
        <span>Rp {{ number_format($session->expected_cash, 0, ',', '.') }}</span>
    </div>
    <div class="grid bold">
        <span>Uang Fisik Laci:</span>
        <span>Rp {{ number_format($session->actual_cash, 0, ',', '.') }}</span>
    </div>
    <div class="divider"></div>
    <div class="grid bold" style="font-size: 13px;">
        <span>Selisih:</span>
        <span>
            @if($session->difference === 0)
                Rp 0 (Pas)
            @elseif($session->difference < 0)
                -Rp {{ number_format(abs($session->difference), 0, ',', '.') }}
            @else
                +Rp {{ number_format($session->difference, 0, ',', '.') }}
            @endif
        </span>
    </div>

    <div class="divider"></div>

    <div class="section-title">RINGKASAN TRANSAKSI POS</div>
    
    <div class="grid">
        <span>Penjualan Tunai:</span>
        <span>{{ $cashSalesCount }} tx (Rp {{ number_format($cashSalesSum, 0, ',', '.') }})</span>
    </div>
    <div class="grid">
        <span>Penjualan Non-Tunai:</span>
        <span>{{ $nonCashSalesCount }} tx (Rp {{ number_format($nonCashSalesSum, 0, ',', '.') }})</span>
    </div>
    <div class="divider"></div>
    <div class="grid bold">
        <span>Total Penjualan:</span>
        <span>{{ $totalSalesCount }} tx (Rp {{ number_format($totalSalesSum, 0, ',', '.') }})</span>
    </div>

    @if($session->notes)
        <div class="divider"></div>
        <div class="bold">Catatan Kasir:</div>
        <div style="font-style: italic; margin-top: 3px;">
            "{{ $session->notes }}"
        </div>
    @endif

    @if($session->closed_by_user_id && $session->closed_by_user_id !== $session->user_id)
        <div class="divider"></div>
        <div class="grid">
            <span>Disetujui Oleh:</span>
            <span class="bold">{{ $session->closedBy->name }}</span>
        </div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            <span>Kasir</span>
            <div class="sig-line"></div>
            <span>{{ $session->user->name }}</span>
        </div>
        <div class="sig-box">
            <span>Supervisor</span>
            <div class="sig-line"></div>
            <span>{{ $session->closedBy ? $session->closedBy->name : '________________' }}</span>
        </div>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }}<br>
        Diego Music Store ERP
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
