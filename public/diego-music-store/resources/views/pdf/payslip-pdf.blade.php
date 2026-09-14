<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SLIP GAJI - {{ $item->employee->name ?? 'Karyawan' }} ({{ $item->payroll->period ?? '' }})</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
        }
        .wrapper {
            max-width: 750px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            padding: 25px;
            border-radius: 12px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .store-name {
            font-size: 20px;
            font-weight: 900;
            color: #0369a1;
            text-transform: uppercase;
        }
        .store-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .title {
            text-align: right;
        }
        .title h2 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
            font-weight: 800;
        }
        .title p {
            margin: 2px 0 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .info-col p {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-col p strong {
            color: #334155;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 800;
            padding: 8px 12px;
            border-bottom: 1px solid #cbd5e1;
        }
        .details-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .section-header {
            font-size: 12px;
            font-weight: 800;
            color: #0369a1;
            margin: 20px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .overtime-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        .overtime-table th {
            background-color: #f0f9ff;
            color: #0369a1;
            font-size: 10px;
            font-weight: 700;
            padding: 6px 10px;
            border-bottom: 1px solid #bae6fd;
            text-align: left;
        }
        .overtime-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .badge-approved {
            background-color: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .total-label {
            font-size: 13px;
            font-weight: 800;
            color: #166534;
        }
        .total-val {
            font-size: 20px;
            font-weight: 900;
            color: #15803d;
        }
        .footer-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-line {
            margin-top: 50px;
            border-bottom: 1px solid #64748b;
        }
        @media print {
            body { padding: 0; }
            .wrapper { border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Cetak / Download PDF
        </button>
    </div>

    <div class="wrapper">
        <!-- Header Toko / Cabang -->
        <div class="header">
            <div>
                <div class="store-name">{{ $item->branch->store_name ?? 'DIEGO MUSIC STORE' }}</div>
                <div class="store-sub">{{ $item->branch->name ?? 'Cabang Utama' }} • {{ $item->branch->address ?? 'Kalimantan Barat' }}</div>
            </div>
            <div class="title">
                <h2>SLIP GAJI KARYAWAN</h2>
                <p>Kode Ref: {{ $item->payroll->payroll_code ?? '-' }}</p>
                <p>Periode: {{ $item->payroll->period ?? '' }}</p>
            </div>
        </div>

        <!-- Info Karyawan -->
        <div class="info-grid">
            <div class="info-col">
                <p><strong>NIK:</strong> {{ $item->employee->nik ?? '-' }}</p>
                <p><strong>Nama Karyawan:</strong> {{ $item->employee->name ?? '-' }}</p>
                <p><strong>Jabatan:</strong> {{ $item->employee->position ?? 'Staff Operasional' }}</p>
            </div>
            <div class="info-col text-right">
                <p><strong>Bank Transfer:</strong> {{ $item->bank_name ?? 'BCA' }}</p>
                <p><strong>No. Rekening:</strong> {{ $item->bank_account_number ?? '-' }}</p>
                <p><strong>Atas Nama:</strong> {{ $item->bank_account_holder ?? '-' }}</p>
            </div>
        </div>

        <!-- Detail Pendapatan & Potongan -->
        <table class="details-table">
            <thead>
                <tr>
                    <th>Komponen Penerimaan (Earnings)</th>
                    <th class="text-right">Jumlah (Rp)</th>
                    <th>Komponen Potongan (Deductions)</th>
                    <th class="text-right">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right">{{ number_format($item->basic_salary, 0, ',', '.') }}</td>
                    <td>Denda / Potongan Presensi</td>
                    <td class="text-right">{{ number_format($item->violation_deduction_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Tetap / Tambahan</td>
                    <td class="text-right">{{ number_format($item->allowance_amount, 0, ',', '.') }}</td>
                    <td>Potongan Lainnya / Kasbon</td>
                    <td class="text-right">{{ number_format($item->other_deduction_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Lembur (Overtime)</td>
                    <td class="text-right">{{ number_format($item->overtime_amount, 0, ',', '.') }}</td>
                    <td>-</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Komisi Penjualan Sales</td>
                    <td class="text-right">{{ number_format($item->commission_amount, 0, ',', '.') }}</td>
                    <td>-</td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Bonus Performa KPI</td>
                    <td class="text-right">{{ number_format($item->kpi_bonus_amount, 0, ',', '.') }}</td>
                    <td>-</td>
                    <td class="text-right">-</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: bold;">
                    <td>TOTAL PENDAPATAN</td>
                    <td class="text-right">{{ number_format($item->basic_salary + $item->allowance_amount + $item->overtime_amount + $item->commission_amount + $item->kpi_bonus_amount, 0, ',', '.') }}</td>
                    <td>TOTAL POTONGAN</td>
                    <td class="text-right">{{ number_format($item->violation_deduction_amount + $item->other_deduction_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Rincian Lembur (Overtime Breakdown) -->
        @if(!empty($item->overtime_details) && count($item->overtime_details) > 0)
            <div class="section-header">Rincian Jam Kerja Lembur (Approved Overtime)</div>
            <table class="overtime-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th class="text-center">Durasi (Jam)</th>
                        <th class="text-center">Status Approval</th>
                        <th class="text-right">Upah Lembur (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->overtime_details as $ot)
                        <tr>
                            <td>{{ $ot['date'] ?? '-' }}</td>
                            <td>{{ $ot['start_time'] ?? '-' }}</td>
                            <td>{{ $ot['end_time'] ?? '-' }}</td>
                            <td class="text-center">{{ number_format($ot['hours'] ?? 0, 1) }} jam</td>
                            <td class="text-center">
                                <span class="badge-approved">{{ strtoupper($ot['status'] ?? 'APPROVED') }}</span>
                            </td>
                            <td class="text-right">{{ number_format($ot['overtime_amount'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Gaji Bersih (Take Home Pay) -->
        <div class="total-box">
            <div class="total-label">GAJI BERSIH DITERIMA (TAKE HOME PAY)</div>
            <div class="total-val">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</div>
        </div>

        <!-- Tanda Tangan -->
        <div class="footer-signatures">
            <div class="sig-box">
                <p>Penerima,</p>
                <div class="sig-line"></div>
                <p><strong>{{ $item->employee->name ?? 'Karyawan' }}</strong></p>
            </div>
            <div class="sig-box">
                <p>Manajer Operasional / HR,</p>
                <div class="sig-line"></div>
                <p><strong>Diego Music Store</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
