<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Payroll;

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
        $payroll = Payroll::with(['items.employee.branch', 'branch', 'items.payroll'])->findOrFail($payrollId);

        return view('pdf.bulk-payslip-pdf', [
            'payroll' => $payroll,
        ]);
    }
}
