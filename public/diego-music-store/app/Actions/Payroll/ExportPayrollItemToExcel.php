<?php

namespace App\Actions\Payroll;

use App\Exports\PayrollItemExport;
use App\Models\PayrollItem;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPayrollItemToExcel
{
    /**
     * Export a single employee payroll item to Excel (.xlsx).
     *
     * @param int|PayrollItem $payrollItem
     * @return BinaryFileResponse
     */
    public function execute(int|PayrollItem $payrollItem): BinaryFileResponse
    {
        $item = $payrollItem instanceof PayrollItem
            ? $payrollItem->loadMissing(['employee', 'payroll.branch', 'branch'])
            : PayrollItem::with(['employee', 'payroll.branch', 'branch'])->findOrFail($payrollItem);

        $employeeSlug = Str::slug($item->employee?->name ?? 'karyawan', '_');
        $period = $item->payroll?->period ?? now()->format('Y-m');
        $filename = "Slip_Gaji_{$employeeSlug}_{$period}.xlsx";

        return Excel::download(new PayrollItemExport($item), $filename);
    }
}
