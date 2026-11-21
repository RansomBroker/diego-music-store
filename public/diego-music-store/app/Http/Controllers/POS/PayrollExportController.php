<?php

namespace App\Http\Controllers\POS;

use App\Actions\Payroll\ExportPayrollItemToExcel;
use App\Actions\Payroll\ExportPayrollToExcel;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\PayrollItem;
use App\Models\Payroll;
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
        $user = Auth::user();
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, 'fo.payroll.view'), 403);
        $payroll = Payroll::findOrFail($payrollId);
        abort_unless((int) $payroll->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);

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
        $isPayrollViewer = \App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, 'fo.payroll.view');
        abort_unless($isPayrollViewer || ($user?->employee?->id && (int) $user->employee->id === (int) $item->employee_id), 403);
        if ($isPayrollViewer) {
            abort_unless((int) $item->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);
        }

        return $action->execute($item);
    }
}
