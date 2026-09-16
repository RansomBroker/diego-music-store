<?php

namespace App\Exports;

use App\Models\PayrollItem;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollItemExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected PayrollItem $item;
    protected int $earningsStartRow = 11;
    protected int $earningsSubtotalRow = 0;
    protected int $deductionsStartRow = 0;
    protected int $deductionsSubtotalRow = 0;
    protected int $thpRow = 0;

    public function __construct(PayrollItem $item)
    {
        $this->item = $item;
    }

    public function title(): string
    {
        $name = $this->item->employee?->name ?? 'Karyawan';
        return 'Slip ' . mb_substr($name, 0, 20);
    }

    public function array(): array
    {
        $item = $this->item;
        $employee = $item->employee;
        $payroll = $item->payroll;
        $branch = $item->branch ?? $payroll?->branch;

        $data = [];

        // 1. Title
        $data[] = ['DIEGO MUSIC STORE - SLIP GAJI KARYAWAN'];
        $data[] = ['Kode Payroll:', $payroll?->payroll_code ?? '-', '', 'Periode:', $payroll?->period ?? '-'];
        $data[] = ['Cabang:', $branch?->name ?? 'Semua Cabang', '', 'Tanggal Cetak:', now()->format('d/m/Y H:i') . ' WIB'];
        $data[] = []; // Empty row (Row 4)

        // 2. Info Karyawan
        $data[] = ['INFORMASI KARYAWAN', '', '', 'INFORMASI REKENING'];
        $data[] = ['NIK:', $employee?->nik ?? '-', '', 'Bank:', $item->bank_name ?: ($employee?->bank_name ?? '-')];
        $data[] = ['Nama Karyawan:', $employee?->name ?? '-', '', 'No. Rekening:', $item->bank_account_number ?: ($employee?->bank_account_number ?? '-')];
        $data[] = ['Status Payroll:', strtoupper($payroll?->status ?? 'DRAFT'), '', 'Atas Nama:', $item->bank_account_holder ?: ($employee?->bank_account_holder ?? '-')];
        $data[] = []; // Row 9
        $data[] = []; // Row 10

        // 3. Rincian Pendapatan (Earnings)
        $data[] = ['NO', 'RINCIAN PENDAPATAN', 'TIPE', 'JUMLAH (RP)']; // Row 11
        $data[] = [1, 'Gaji Pokok', 'Pendapatan', (float) $item->basic_salary];
        $data[] = [2, 'Tunjangan Tetap', 'Pendapatan', (float) $item->allowance_amount];
        $data[] = [3, 'Tunjangan Lembur', 'Pendapatan', (float) $item->overtime_amount];
        $data[] = [4, 'Komisi Sales', 'Pendapatan', (float) $item->commission_amount];
        $data[] = [5, 'Bonus KPI', 'Pendapatan', (float) $item->kpi_bonus_amount];

        $totalEarnings = (float) ($item->basic_salary + $item->allowance_amount + $item->overtime_amount + $item->commission_amount + $item->kpi_bonus_amount);
        $this->earningsSubtotalRow = count($data) + 1;
        $data[] = ['', 'SUBTOTAL PENDAPATAN', '', $totalEarnings]; // Row 17

        $data[] = []; // Empty row (Row 18)

        // 4. Rincian Potongan (Deductions)
        $this->deductionsStartRow = count($data) + 1;
        $data[] = ['NO', 'RINCIAN POTONGAN', 'TIPE', 'JUMLAH (RP)']; // Row 19
        $data[] = [1, 'Denda Presensi / Terlambat', 'Potongan', (float) $item->violation_deduction_amount];
        $data[] = [2, 'Potongan Lain-lain / Kasbon', 'Potongan', (float) $item->other_deduction_amount];

        $totalDeductions = (float) ($item->violation_deduction_amount + $item->other_deduction_amount);
        $this->deductionsSubtotalRow = count($data) + 1;
        $data[] = ['', 'SUBTOTAL POTONGAN', '', $totalDeductions]; // Row 22

        $data[] = []; // Row 23

        // 5. Total Gaji Bersih (Take Home Pay)
        $this->thpRow = count($data) + 1;
        $data[] = ['', 'GAJI BERSIH (TAKE HOME PAY)', '', (float) $item->net_salary]; // Row 24

        // 6. Catatan
        $data[] = [];
        $data[] = ['Catatan:', $item->notes ?: 'Tidak ada catatan penyesuaian khusus.'];

        return $data;
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        // Judul Utama
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3A8A');

        // Label Metadata & Info
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        $sheet->getStyle('D2:D3')->getFont()->setBold(true);

        // Header Info Karyawan
        $sheet->mergeCells('A5:B5');
        $sheet->mergeCells('D5:E5');
        $sheet->getStyle('A5')->getFont()->setBold(true)->getColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('D5')->getFont()->setBold(true)->getColor()->setARGB('FF1E3A8A');

        $sheet->getStyle('A6:A8')->getFont()->setBold(true);
        $sheet->getStyle('D6:D8')->getFont()->setBold(true);

        // Header Tabel Pendapatan (Row 11)
        $sheet->getStyle("A{$this->earningsStartRow}:D{$this->earningsStartRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Subtotal Pendapatan
        if ($this->earningsSubtotalRow > 0) {
            $sheet->getStyle("A{$this->earningsSubtotalRow}:D{$this->earningsSubtotalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Header Tabel Potongan
        if ($this->deductionsStartRow > 0) {
            $sheet->getStyle("A{$this->deductionsStartRow}:D{$this->deductionsStartRow}")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE11D48'], // Rose
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        // Subtotal Potongan
        if ($this->deductionsSubtotalRow > 0) {
            $sheet->getStyle("A{$this->deductionsSubtotalRow}:D{$this->deductionsSubtotalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Baris THP (Take Home Pay)
        if ($this->thpRow > 0) {
            $sheet->getStyle("A{$this->thpRow}:D{$this->thpRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FF065F46'], // Dark Emerald
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD1FAE5'], // Light Emerald
                ],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                ],
            ]);
        }

        // Catatan
        $notesRow = $this->thpRow + 2;
        $sheet->getStyle("A{$notesRow}")->getFont()->setBold(true);

        return null;
    }
}
