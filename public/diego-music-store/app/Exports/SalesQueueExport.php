<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesQueueExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected ?Branch $branch;
    protected string $date;
    protected array $summaryStats;
    protected Collection $leaderboard;
    protected Collection $logs;
    protected ?User $exportedBy;

    protected int $leaderboardHeaderRow = 11;
    protected int $leaderboardTotalRow = 0;
    protected int $logsHeaderRow = 0;
    protected int $logsTotalRow = 0;

    public function __construct(
        ?Branch $branch,
        string $date,
        array $summaryStats,
        Collection $leaderboard,
        Collection $logs,
        ?User $exportedBy = null
    ) {
        $this->branch = $branch;
        $this->date = $date;
        $this->summaryStats = $summaryStats;
        $this->leaderboard = $leaderboard;
        $this->logs = $logs;
        $this->exportedBy = $exportedBy;
    }

    public function title(): string
    {
        return 'Antrian Sales ' . $this->date;
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function array(): array
    {
        $data = [];

        // 1. Header Metadata
        $data[] = ['DIEGO MUSIC STORE - LAPORAN ANTRIAN & GILIRAN SALES (ROUND-ROBIN DUTY)'];
        $data[] = [
            'Tanggal Laporan:',
            \Carbon\Carbon::parse($this->date)->translatedFormat('d F Y'),
            '',
            'Cabang:',
            $this->branch?->name ?: 'Semua Cabang',
        ];
        $data[] = [
            'Diexport Oleh:',
            $this->exportedBy?->name ?: 'Staf Kasir',
            '',
            'Waktu Export:',
            now()->format('d/m/Y H:i') . ' WIB',
        ];
        $data[] = []; // Row 4: Separator

        // 2. Ringkasan KPI Harian
        $data[] = ['RINGKASAN KPI HARIAN', '', '', ''];
        $data[] = [
            'Total Giliran Melayani:',
            $this->summaryStats['total_servings'] . ' Sesi',
            'Closing Sukses:',
            $this->summaryStats['total_closings'] . ' Nota',
        ];
        $data[] = [
            'Konversi Closing:',
            $this->summaryStats['conversion_rate'] . '%',
            'Total Omset Closing:',
            $this->summaryStats['total_revenue'],
        ];
        $data[] = [
            'Rata-rata Durasi:',
            $this->summaryStats['avg_duration_minutes'] . ' Menit',
            'Sales Aktif Melayani:',
            $this->summaryStats['active_on_duty_count'] . ' Sales',
        ];
        $data[] = []; // Row 9: Separator

        // 3. Section I: Leaderboard / Rekapitulasi Per Sales
        $data[] = ['I. REKAPITULASI PERFORMA GILIRAN SALES']; // Row 10
        $data[] = [
            'Rank',
            'Nama Sales',
            'NIK',
            'Total Giliran',
            'Closing Nota',
            'Rasio Closing (%)',
            'Total Omset (Rp)',
            'Rata-rata Durasi (Menit)',
        ]; // Row 11: Header Leaderboard

        $rank = 1;
        $totalServings = 0;
        $totalClosings = 0;
        $totalRevenue = 0;

        foreach ($this->leaderboard as $row) {
            $data[] = [
                $rank++,
                $row['name'],
                $row['employee_nik'],
                $row['total_servings'],
                $row['total_closings'],
                $row['closing_rate'] . '%',
                $row['total_revenue'],
                $row['avg_duration_minutes'],
            ];

            $totalServings += (int) $row['total_servings'];
            $totalClosings += (int) $row['total_closings'];
            $totalRevenue += (float) $row['total_revenue'];
        }

        // Leaderboard Summary Row
        $avgLeaderboardDuration = $this->leaderboard->count() > 0
            ? round($this->leaderboard->avg('avg_duration_minutes'), 1)
            : 0;
        $overallConversion = $totalServings > 0
            ? round(($totalClosings / $totalServings) * 100, 1) . '%'
            : '0%';

        $data[] = [
            'TOTAL',
            '',
            '',
            $totalServings,
            $totalClosings,
            $overallConversion,
            $totalRevenue,
            $avgLeaderboardDuration,
        ];
        $this->leaderboardTotalRow = count($data);

        $data[] = []; // Separator
        $data[] = []; // Separator

        // 4. Section II: Riwayat Detail Sesi Giliran Melayani
        $data[] = ['II. RIWAYAT DETAIL SESI GILIRAN MELAYANI'];
        $data[] = [
            'No',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi',
            'Sales Rep',
            'NIK',
            'Status',
            'No. Faktur (Closing)',
            'Nilai Belanja (Rp)',
            'Kasir Pencatat',
        ];
        $this->logsHeaderRow = count($data);

        $no = 1;
        $totalLogsRevenue = 0;

        foreach ($this->logs as $log) {
            $data[] = [
                $no++,
                $log->started_at->format('H:i:s'),
                $log->ended_at ? $log->ended_at->format('H:i:s') : 'Sedang Aktif',
                $log->duration_text,
                $log->user->name ?? '-',
                $log->user->employee->nik ?? '-',
                ucfirst($log->status),
                $log->sale?->invoice_number ?? '-',
                $log->closing_amount > 0 ? $log->closing_amount : 0,
                $log->creator->name ?? 'Kasir',
            ];

            $totalLogsRevenue += (float) ($log->closing_amount ?? 0);
        }

        // Logs Summary Row
        $data[] = [
            'TOTAL NILAI CLOSING',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalLogsRevenue,
            '',
        ];
        $this->logsTotalRow = count($data);

        return $data;
    }

    public function styles(Worksheet $sheet): ?array
    {
        // 1. Judul Laporan Utama
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 14,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Metadata Header
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        $sheet->getStyle('D2:D3')->getFont()->setBold(true);

        // 2. Blok Ringkasan KPI
        $sheet->mergeCells('A5:D5');
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
        ]);
        $sheet->getStyle('A6:A8')->getFont()->setBold(true);
        $sheet->getStyle('C6:C8')->getFont()->setBold(true);
        $sheet->getStyle('A5:D8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 3. Section I Title & Header
        $section1TitleRow = $this->leaderboardHeaderRow - 1;
        $sheet->getStyle("A{$section1TitleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E293B']],
        ]);

        $sheet->getStyle("A{$this->leaderboardHeaderRow}:H{$this->leaderboardHeaderRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Leaderboard Rows Border & Alignments
        $leaderboardDataStart = $this->leaderboardHeaderRow + 1;
        if ($this->leaderboardTotalRow > $this->leaderboardHeaderRow) {
            $sheet->getStyle("A{$this->leaderboardHeaderRow}:H{$this->leaderboardTotalRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $sheet->getStyle("A{$leaderboardDataStart}:A{$this->leaderboardTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$leaderboardDataStart}:F{$this->leaderboardTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$leaderboardDataStart}:H{$this->leaderboardTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Leaderboard Total Row
            $sheet->mergeCells("A{$this->leaderboardTotalRow}:C{$this->leaderboardTotalRow}");
            $sheet->getStyle("A{$this->leaderboardTotalRow}:H{$this->leaderboardTotalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF1F5F9'],
                ],
            ]);
            $sheet->getStyle("A{$this->leaderboardTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // 4. Section II Title & Header
        $section2TitleRow = $this->logsHeaderRow - 1;
        $sheet->getStyle("A{$section2TitleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E293B']],
        ]);

        $sheet->getStyle("A{$this->logsHeaderRow}:J{$this->logsHeaderRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Logs Rows Border & Alignments
        $logsDataStart = $this->logsHeaderRow + 1;
        if ($this->logsTotalRow > $this->logsHeaderRow) {
            $sheet->getStyle("A{$this->logsHeaderRow}:J{$this->logsTotalRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $sheet->getStyle("A{$logsDataStart}:A{$this->logsTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$logsDataStart}:D{$this->logsTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$logsDataStart}:G{$this->logsTotalRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Logs Total Row
            $sheet->mergeCells("A{$this->logsTotalRow}:H{$this->logsTotalRow}");
            $sheet->getStyle("A{$this->logsTotalRow}:J{$this->logsTotalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF1F5F9'],
                ],
            ]);
            $sheet->getStyle("A{$this->logsTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        return null;
    }
}
