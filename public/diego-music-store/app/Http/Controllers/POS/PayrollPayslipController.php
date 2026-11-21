<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Auth;

class PayrollPayslipController extends Controller
{
    /**
     * Display printable PDF payslip for a specific payroll item.
     *
     * @param int $payrollItemId
     * @return \Illuminate\View\View
     */
    public function show(int $payrollItemId)
    {
        $item = PayrollItem::with(['payroll', 'employee.branch'])->findOrFail($payrollItemId);
        $user = Auth::user();
        $isPayrollViewer = \App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, 'fo.payroll.view');
        abort_unless($isPayrollViewer || ($user?->employee?->id && (int) $user->employee->id === (int) $item->employee_id), 403);
        if ($isPayrollViewer) {
            abort_unless((int) $item->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);
        }

        return view('pdf.payslip-pdf', [
            'item' => $item,
        ]);
    }
}
