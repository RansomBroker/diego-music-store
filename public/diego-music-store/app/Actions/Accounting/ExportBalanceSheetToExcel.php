<?php

namespace App\Actions\Accounting;

use App\Exports\BalanceSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportBalanceSheetToExcel
{
    public function execute(array $reportData): BinaryFileResponse
    {
        $asOfDate = $reportData['as_of_date'] ?? now()->format('Y-m-d');
        $filename = "Laporan-Balance-Sheet-{$asOfDate}.xlsx";

        return Excel::download(
            new BalanceSheetExport($reportData),
            $filename
        );
    }
}

