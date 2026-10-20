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
        abort_unless($user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']), 403);
        $payroll = Payroll::with(['items.employee.branch', 'branch', 'items.payroll'])->findOrFail($payrollId);
        abort_unless((int) $payroll->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);

        return view('pdf.bulk-payslip-pdf', [
            'payroll' => $payroll,
        ]);
    }
}
