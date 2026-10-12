<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Z-Report - Sesi #{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Action Bar (Hanya tampil di browser, sembunyi saat cetak) */
        .top-action-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .top-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }
        .top-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            border: none;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #0369a1;
        }
        .btn-pdf {
            background: #059669;
            color: #ffffff;
        }
        .btn-pdf:hover {
            background: #047857;
        }
        .btn-close {
            background: #e2e8f0;
            color: #475569;
        }
        .btn-close:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        /* Receipt Card Container */
        .receipt-container {
            max-width: 340px; /* Lebar standar kertas thermal 80mm */
            margin: 24px auto;
            background: #ffffff;
            padding: 24px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 12px; }
        .store-name {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px double #000;
            margin: 8px 0;
        }
        .grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
            text-decoration: underline;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
        }
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 45%;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-top: 35px;
            margin-bottom: 4px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                border-radius: 0;
                margin: 0 auto;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Hanya muncul di browser / tab baru) -->
    <div class="top-action-bar no-print">
        <div class="top-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Z-Report Sesi #{{ str_pad($session->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="top-buttons">
            <button onclick="window.print()" class="btn btn-print" title="Cetak ke Printer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Struk</span>
            </button>
            <a href="{{ route('pos.z-report.pdf', $session->id) }}" class="btn btn-pdf" title="Unduh File PDF">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Download as PDF</span>
            </a>
            <button onclick="window.close()" class="btn btn-close" title="Tutup Tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <span>Tutup</span>
            </button>
        </div>
    </div>

    <!-- Receipt Paper View -->
    <div class="receipt-container">
        <div class="header text-center">
            <span class="store-name">{{ $session->branch->store_name ?: 'Diego Music Store' }}</span><br>
            <span>{{ $session->branch->name }}</span><br>
            @if ($session->branch->phone)
                <span>Telp: {{ $session->branch->phone }}</span>
            @endif
        </div>

        <div class="double-divider"></div>

        <div class="text-center bold" style="font-size: 13px; margin-bottom: 8px;">
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
            <span>{{ $session->opened_at ? $session->opened_at->format('d/m/y H:i') : '-' }}</span>
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
                <span class="bold">{{ $session->closedBy?->name ?? 'Supervisor' }}</span>
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
    </div>

</body>
</html>
