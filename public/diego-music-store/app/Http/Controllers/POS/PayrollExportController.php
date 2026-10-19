<?php

namespace App\Http\Controllers\POS;

use App\Actions\Payroll\ExportPayrollItemToExcel;
use App\Actions\Payroll\ExportPayrollToExcel;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Auth;

class PayrollExportController extends Controller
{
    /**
     * Download Excel (.xlsx) rekap export for a specific payroll record (all employees).
     *
     * @param int $payrollId
     * @param ExportPayrollToExcel $action
     * @return BinaryFileResponse
     */
    public function export(int $payrollId, ExportPayrollToExcel $action): BinaryFileResponse
    {
        return $action->execute($payrollId);
    }

    /**
     * Download individual employee slip Excel (.xlsx) export.
     *
     * @param int $itemId
     * @param ExportPayrollItemToExcel $action
     * @return BinaryFileResponse
     */
    public function exportItem(int $itemId, ExportPayrollItemToExcel $action): BinaryFileResponse
    {
        $item = PayrollItem::query()->with('employee')->findOrFail($itemId);
        $user = Auth::user();
        $isManagement = $user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']);
        abort_unless($isManagement || ($user?->employee?->id && (int) $user->employee->id === (int) $item->employee_id), 403);

        return $action->execute($item);
    }
}
