<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

class BulkPayrollPayslipController extends Controller
{
    /**
     * Display printable PDF payslips for all employees in a specific payroll period.
     *
     * @param int $payrollId
     * @return \Illuminate\View\View
     */
    public function show(int $payrollId)
    {
        $user = Auth::user();
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, 'fo.payroll.view'), 403);
        $payroll = Payroll::with(['items.employee.branch', 'branch', 'items.payroll'])->findOrFail($payrollId);
        abort_unless((int) $payroll->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);

        return view('pdf.bulk-payslip-pdf', [
            'payroll' => $payroll,
        ]);
    }
}
