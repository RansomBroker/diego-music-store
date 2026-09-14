<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;

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

        return view('pdf.payslip-pdf', [
            'item' => $item,
        ]);
    }
}
