<?php

namespace App\Exports;

use App\Helpers\FinancialReportHelper;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceSheetExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    /** @var array<int, array{type: string, code?: string, name?: string, balance?: float|int, is_header?: bool, level?: int, label?: string}> */
    protected array $leftRows = [];

    /** @var array<int, array{type: string, code?: string, name?: string, balance?: float|int, is_header?: bool, level?: int, label?: string}> */
    protected array $rightRows = [];

    protected int $bodyStartRow = 6;

    protected int $totalRow = 6;

    protected int $signatureRow = 6;

    public function __construct(
        protected array $data
    ) {
        $this->buildSideRows();
    }

    public function title(): string
    {
        return 'Balance Sheet';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 38,
            'C' => 20,
            'D' => 4,
            'E' => 15,
            'F' => 38,
            'G' => 20,
        ];
    }

    protected function buildSideRows(): void
    {
        // LEFT COLUMN: ASET (AKTIVA)
        $this->leftRows = [
            ['type' => 'section_header', 'label' => 'ASET (AKTIVA)'],
            ['type' => 'sub_header', 'label' => 'Aset Lancar'],
            ['type' => 'table_header'],
        ];

        $currentAssets = $this->data['assets']['current_assets'] ?? [];
        if (empty($currentAssets)) {
            $this->leftRows[] = ['type' => 'empty', 'label' => 'Tidak ada Aset Lancar'];
        } else {
            foreach ($currentAssets as $item) {
                $this->leftRows[] = [
                    'type' => 'account',
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'balance' => (float) ($item['balance'] ?? 0),
                    'is_header' => (bool) ($item['is_header'] ?? false),
                    'level' => (int) ($item['level'] ?? 1),
                ];
            }
        }

        $this->leftRows[] = [
            'type' => 'subtotal',
            'label' => 'Total Aset Lancar',
            'balance' => (float) ($this->data['assets']['total_current_assets'] ?? 0),
        ];

        $fixedAssets = $this->data['assets']['fixed_assets'] ?? [];
        if (! empty($fixedAssets)) {
            $this->leftRows[] = ['type' => 'blank'];
            $this->leftRows[] = ['type' => 'sub_header', 'label' => 'Aset Tetap'];
            $this->leftRows[] = ['type' => 'table_header'];

            foreach ($fixedAssets as $item) {
                $this->leftRows[] = [
                    'type' => 'account',
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'balance' => (float) ($item['balance'] ?? 0),
                    'is_header' => (bool) ($item['is_header'] ?? false),
                    'level' => (int) ($item['level'] ?? 1),
                ];
            }

            $this->leftRows[] = [
                'type' => 'subtotal',
                'label' => 'Total Aset Tetap',
                'balance' => (float) ($this->data['assets']['total_fixed_assets'] ?? 0),
            ];
        }

        // RIGHT COLUMN: LIABILITAS & EKUITAS (PASIVA)
        $this->rightRows = [
            ['type' => 'section_header', 'label' => 'LIABILITAS & EKUITAS (PASIVA)'],
            ['type' => 'sub_header', 'label' => 'Kewajiban Jangka Pendek'],
            ['type' => 'table_header'],
        ];

        $currentLiabilities = $this->data['liabilities']['current_liabilities'] ?? [];
        if (empty($currentLiabilities)) {
            $this->rightRows[] = ['type' => 'empty', 'label' => 'Tidak ada Kewajiban Jangka Pendek'];
        } else {
            foreach ($currentLiabilities as $item) {
                $this->rightRows[] = [
                    'type' => 'account',
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'balance' => (float) ($item['balance'] ?? 0),
                    'is_header' => (bool) ($item['is_header'] ?? false),
                    'level' => (int) ($item['level'] ?? 1),
                ];
            }
        }

        $this->rightRows[] = [
            'type' => 'subtotal',
            'label' => 'Total Kewajiban Jangka Pendek',
            'balance' => (float) ($this->data['liabilities']['total_current_liabilities'] ?? 0),
        ];

        $longTermLiabilities = $this->data['liabilities']['long_term_liabilities'] ?? [];
        if (! empty($longTermLiabilities)) {
            $this->rightRows[] = ['type' => 'blank'];
            $this->rightRows[] = ['type' => 'sub_header', 'label' => 'Kewajiban Jangka Panjang'];
            $this->rightRows[] = ['type' => 'table_header'];

            foreach ($longTermLiabilities as $item) {
                $this->rightRows[] = [
                    'type' => 'account',
                    'code' => (string) ($item['code'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'balance' => (float) ($item['balance'] ?? 0),
                    'is_header' => (bool) ($item['is_header'] ?? false),
                    'level' => (int) ($item['level'] ?? 1),
                ];
            }

            $this->rightRows[] = [
                'type' => 'subtotal',
                'label' => 'Total Kewajiban Jangka Panjang',
                'balance' => (float) ($this->data['liabilities']['total_long_term_liabilities'] ?? 0),
            ];
        }

        $this->rightRows[] = [
            'type' => 'group_total',
            'label' => 'TOTAL KEWAJIBAN',
            'balance' => (float) ($this->data['total_liabilities'] ?? 0),
        ];

        $this->rightRows[] = ['type' => 'blank'];
        $this->rightRows[] = ['type' => 'sub_header', 'label' => 'Ekuitas'];
        $this->rightRows[] = ['type' => 'table_header'];

        foreach ($this->data['equity']['items'] ?? [] as $item) {
            $this->rightRows[] = [
                'type' => 'account',
                'code' => (string) ($item['code'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'balance' => (float) ($item['balance'] ?? 0),
                'is_header' => (bool) ($item['is_header'] ?? false),
                'level' => (int) ($item['level'] ?? 1),
            ];
        }

        $this->rightRows[] = [
            'type' => 'net_income',
            'code' => '-',
            'name' => 'Laba / (Rugi) Periode Berjalan',
            'balance' => (float) ($this->data['equity']['current_net_income'] ?? 0),
        ];

        $this->rightRows[] = [
            'type' => 'group_total',
            'label' => 'TOTAL EKUITAS',
            'balance' => (float) ($this->data['total_equity'] ?? 0),
        ];
    }

    public function array(): array
    {
        $asOfFormatted = Carbon::parse($this->data['as_of_date'] ?? now()->format('Y-m-d'))->format('d/m/Y');
        $branchName = $this->data['branch_name'] ?? 'Semua Cabang';
        $isBalanced = (bool) ($this->data['is_balanced'] ?? false);
        $difference = (float) ($this->data['difference'] ?? 0);

        $statusText = $isBalanced
            ? 'STATUS: NERACA SEIMBANG (BALANCED) — Total Aset = Total Liabilitas + Ekuitas'
            : 'STATUS: Terdapat Selisih (' . FinancialReportHelper::formatRupiah(abs($difference)) . ')';

        $rows = [
            ['DIEGO MUSIC STORE', '', '', '', '', '', ''],
            ['LAPORAN BALANCE SHEET (NERACA - SKONTRO)', '', '', '', '', '', ''],
            ["Per Tanggal: {$asOfFormatted} • Cabang: {$branchName} • Dicetak Pada: " . now()->format('d/m/Y H:i'), '', '', '', '', '', ''],
            [$statusText, '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
        ];

        $maxSideCount = max(count($this->leftRows), count($this->rightRows));

        for ($i = 0; $i < $maxSideCount; $i++) {
            $leftCells = $this->renderSideRowCells($this->leftRows[$i] ?? null);
            $rightCells = $this->renderSideRowCells($this->rightRows[$i] ?? null);

            $rows[] = array_merge($leftCells, [''], $rightCells);
        }

        // Blank spacer before final total row
        $rows[] = ['', '', '', '', '', '', ''];

        // Aligned Final Total Box Row (Skontro bottom)
        $this->totalRow = count($rows) + 1;
        $rows[] = [
            'TOTAL ASET (AKTIVA)',
            '',
            (float) ($this->data['total_assets'] ?? 0),
            '',
            'TOTAL LIABILITAS & EKUITAS',
            '',
            (float) ($this->data['total_liabilities_and_equity'] ?? 0),
        ];

        // Blank rows for signature area
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];

        $this->signatureRow = count($rows) + 1;
        $rows[] = [
            'Disiapkan Oleh (Staf Akuntansi)',
            '',
            'Diperiksa Oleh (Manager Finance)',
            '',
            '',
            'Disetujui Oleh (Owner)',
            '',
        ];

        return $rows;
    }

    /**
     * @param  array<string, mixed>|null  $row
     * @return array{0: mixed, 1: mixed, 2: mixed}
     */
    protected function renderSideRowCells(?array $row): array
    {
        if (! $row || ($row['type'] ?? 'blank') === 'blank') {
            return ['', '', ''];
        }

        return match ($row['type']) {
            'section_header', 'sub_header', 'empty' => [$row['label'] ?? '', '', ''],
            'table_header' => ['Kode', 'Nama Akun', 'Saldo (Rp)'],
            'account' => [
                $row['code'] ?? '',
                $this->formatAccountName((string) ($row['name'] ?? ''), (int) ($row['level'] ?? 1), (bool) ($row['is_header'] ?? false)),
                (float) ($row['balance'] ?? 0),
            ],
            'net_income' => [
                $row['code'] ?? '-',
                '  ' . ($row['name'] ?? 'Laba / (Rugi) Periode Berjalan'),
                (float) ($row['balance'] ?? 0),
            ],
            'subtotal', 'group_total' => [
                $row['label'] ?? '',
                '',
                (float) ($row['balance'] ?? 0),
            ],
            default => ['', '', ''],
        };
    }

    protected function formatAccountName(string $name, int $level, bool $isHeader): string
    {
        if ($isHeader) {
            return str_repeat('  ', max(0, $level - 1)) . $name;
        }

        return str_repeat('  ', max(1, $level - 1)) . $name;
    }

    public function styles(Worksheet $sheet): array
    {
        // Page Setup for crisp printing matching PDF
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->setShowGridlines(true);

        // Header merges & styling (Rows 1-4)
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->mergeCells('A4:G4');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF111827']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF111827']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(19);

        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['argb' => 'FF4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF111827']],
            ],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $isBalanced = (bool) ($this->data['is_balanced'] ?? false);
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 9.5,
                'color' => ['argb' => $isBalanced ? 'FF111827' : 'FFDC2626'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $isBalanced ? 'FFF3F4F6' : 'FFFEE2E2'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => $isBalanced ? 'FF9CA3AF' : 'FFDC2626'],
                ],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(8);

        // Apply side-by-side styles
        $maxSideCount = max(count($this->leftRows), count($this->rightRows));
        for ($i = 0; $i < $maxSideCount; $i++) {
            $excelRow = $this->bodyStartRow + $i;
            $this->styleSideRow($sheet, $excelRow, $this->leftRows[$i] ?? null, 'A', 'B', 'C');
            $this->styleSideRow($sheet, $excelRow, $this->rightRows[$i] ?? null, 'E', 'F', 'G');
        }

        // Style aligned final Total Boxes
        $this->styleFinalTotalBox($sheet, $this->totalRow, 'A', 'B', 'C');
        $this->styleFinalTotalBox($sheet, $this->totalRow, 'E', 'F', 'G');
        $sheet->getRowDimension($this->totalRow)->setRowHeight(22);

        // Style Signature Block
        $sigRow = $this->signatureRow;
        $sheet->mergeCells("A{$sigRow}:B{$sigRow}");
        $sheet->mergeCells("C{$sigRow}:E{$sigRow}");
        $sheet->mergeCells("F{$sigRow}:G{$sigRow}");

        foreach (["A{$sigRow}:B{$sigRow}", "C{$sigRow}:E{$sigRow}", "F{$sigRow}:G{$sigRow}"] as $sigRange) {
            $sheet->getStyle($sigRange)->applyFromArray([
                'font' => ['size' => 9, 'color' => ['argb' => 'FF111827']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                ],
            ]);
        }

        return [];
    }

    protected function styleSideRow(
        Worksheet $sheet,
        int $rowNum,
        ?array $row,
        string $colCode,
        string $colName,
        string $colBal
    ): void {
        if (! $row || ($row['type'] ?? 'blank') === 'blank') {
            return;
        }

        $range = "{$colCode}{$rowNum}:{$colBal}{$rowNum}";

        switch ($row['type']) {
            case 'section_header':
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($rowNum)->setRowHeight(20);
                break;

            case 'sub_header':
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => 'FF111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9CA3AF']],
                    ],
                ]);
                $sheet->getRowDimension($rowNum)->setRowHeight(18);
                break;

            case 'table_header':
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                    ],
                ]);
                $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'empty':
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                break;

            case 'account':
                $isHeader = (bool) ($row['is_header'] ?? false);
                $sheet->setCellValueExplicit("{$colCode}{$rowNum}", (string) ($row['code'] ?? ''), DataType::TYPE_STRING);
                $sheet->getStyle($range)->applyFromArray([
                    'font' => [
                        'bold' => $isHeader,
                        'size' => 9,
                        'color' => ['argb' => 'FF111827'],
                    ],
                    'fill' => $isHeader
                        ? ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']]
                        : ['fillType' => Fill::FILL_NONE],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFD1D5DB']],
                    ],
                ]);
                $sheet->getStyle("{$colBal}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;[Red](#,##0);0');
                $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'net_income':
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']],
                    ],
                ]);
                $sheet->getStyle("{$colBal}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;[Red](#,##0);0');
                $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'subtotal':
                $sheet->mergeCells("{$colCode}{$rowNum}:{$colName}{$rowNum}");
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                    ],
                ]);
                $sheet->getStyle("{$colBal}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp "#,##0;[Red]("Rp "#,##0);"Rp "0');
                $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;

            case 'group_total':
                $sheet->mergeCells("{$colCode}{$rowNum}:{$colName}{$rowNum}");
                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9.5, 'color' => ['argb' => 'FF111827']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF111827']],
                    ],
                ]);
                $sheet->getStyle("{$colBal}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp "#,##0;[Red]("Rp "#,##0);"Rp "0');
                $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;
        }
    }

    protected function styleFinalTotalBox(
        Worksheet $sheet,
        int $rowNum,
        string $colCode,
        string $colName,
        string $colBal
    ): void {
        $range = "{$colCode}{$rowNum}:{$colBal}{$rowNum}";
        $sheet->mergeCells("{$colCode}{$rowNum}:{$colName}{$rowNum}");
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF111827']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF111827']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF111827']],
            ],
        ]);
        $sheet->getStyle("{$colBal}{$rowNum}")
            ->getNumberFormat()
            ->setFormatCode('"Rp "#,##0;[Red]("Rp "#,##0);"Rp "0');
        $sheet->getStyle("{$colBal}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}

