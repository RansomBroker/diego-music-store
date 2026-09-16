<?php

namespace App\Actions\Payroll;

use App\Exports\PayrollExport;
use App\Models\Payroll;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPayrollToExcel
{
    /**
     * Export payroll records to Excel (.xlsx) using Maatwebsite Excel.
     *
     * @param int|Payroll $payroll
     * @return BinaryFileResponse
     */
    public function execute(int|Payroll $payroll): BinaryFileResponse
    {
        $payrollModel = $payroll instanceof Payroll
            ? $payroll->loadMissing(['items.employee', 'branch', 'creator', 'approver'])
            : Payroll::with(['items.employee', 'branch', 'creator', 'approver'])->findOrFail($payroll);

        $filename = "Payroll_Gaji_{$payrollModel->period}_{$payrollModel->payroll_code}.xlsx";

        return Excel::download(new PayrollExport($payrollModel), $filename);
    }
}
