<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected Payroll $payroll;
    protected int $headerRow = 6;
    protected int $totalRow = 0;

    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    public function title(): string
    {
        return 'Payroll ' . ($this->payroll->period ?: 'Report');
    }

    public function array(): array
    {
        $data = [];

        // Header Metadata
        $data[] = ['DIEGO MUSIC STORE - LAPORAN PAYROLL & GAJI KARYAWAN'];
        $data[] = ['Kode Payroll:', $this->payroll->payroll_code, '', 'Periode:', $this->payroll->period];
        $data[] = ['Cabang:', $this->payroll->branch?->name ?? 'Semua Cabang', '', 'Status:', strtoupper($this->payroll->status)];
        $data[] = ['Total Karyawan:', $this->payroll->total_employees, '', 'Tanggal Cetak:', now()->format('d/m/Y H:i') . ' WIB'];
        $data[] = []; // Empty row separator

        // Column Headers (Row 6)
        $data[] = [
            'No',
            'NIK',
            'Nama Karyawan',
            'Bank',
            'No. Rekening',
            'Atas Nama',
            'Gaji Pokok (Rp)',
            'Tunjangan Tetap (Rp)',
            'Tunj. Lembur (Rp)',
            'Komisi Sales (Rp)',
            'Bonus KPI (Rp)',
            'Denda Presensi (Rp)',
            'Potongan Lain (Rp)',
            'Gaji Bersih (THP) (Rp)',
            'Catatan Penyesuaian',
        ];

        $no = 1;
        foreach ($this->payroll->items as $item) {
            $data[] = [
                $no++,
                $item->employee?->nik ?? '-',
                $item->employee?->name ?? '-',
                $item->bank_name ?: ($item->employee?->bank_name ?? '-'),
                $item->bank_account_number ?: ($item->employee?->bank_account_number ?? '-'),
                $item->bank_account_holder ?: ($item->employee?->bank_account_holder ?? '-'),
                (float) $item->basic_salary,
                (float) $item->allowance_amount,
                (float) $item->overtime_amount,
                (float) $item->commission_amount,
                (float) $item->kpi_bonus_amount,
                (float) $item->violation_deduction_amount,
                (float) $item->other_deduction_amount,
                (float) $item->net_salary,
                $item->notes ?? '',
            ];
        }

        $data[] = []; // Empty row before total

        $this->totalRow = count($data) + 1;

        // Total Row
        $data[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            (float) $this->payroll->total_basic_salary,
            (float) $this->payroll->items->sum('allowance_amount'),
            (float) $this->payroll->items->sum('overtime_amount'),
            (float) $this->payroll->total_commissions,
            (float) $this->payroll->total_kpi_bonuses,
            (float) $this->payroll->items->sum('violation_deduction_amount'),
            (float) $this->payroll->items->sum('other_deduction_amount'),
            (float) $this->payroll->total_net_salary,
            '',
        ];

        return $data;
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        // Judul Utama
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3A8A');

        // Label Metadata
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('D2:D4')->getFont()->setBold(true);

        // Header Tabel Kolom (Row 6)
        $sheet->getStyle("A{$this->headerRow}:O{$this->headerRow}")->applyFromArray([
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
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Alignment kolom data dan border tipis
        $lastItemRow = $this->totalRow > $this->headerRow + 2 ? $this->totalRow - 2 : $this->headerRow;
        if ($lastItemRow > $this->headerRow) {
            $sheet->getStyle("A7:A{$lastItemRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B7:B{$lastItemRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D7:F{$lastItemRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("A{$this->headerRow}:O{$lastItemRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Baris Total
        if ($this->totalRow > $this->headerRow) {
            $sheet->mergeCells("A{$this->totalRow}:F{$this->totalRow}");
            $sheet->getStyle("A{$this->totalRow}:O{$this->totalRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF1F5F9'],
                ],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                ],
            ]);
            $sheet->getStyle("A{$this->totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return null;
    }
}
