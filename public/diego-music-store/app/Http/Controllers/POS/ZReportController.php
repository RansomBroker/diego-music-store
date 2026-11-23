<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZReportController extends Controller
{
    /**
     * Prepare data for Z-Report calculations.
     */
    protected function getReportData(CashSession $cashSession): array
    {
        $sales = $cashSession->sales()->where('status', 'completed')->get();

        $cashSalesCount = 0;
        $cashSalesSum = 0;
        $nonCashSalesCount = 0;
        $nonCashSalesSum = 0;

        foreach ($sales as $sale) {
            $cashAmt = \App\Helpers\SaleHelper::getCashAmount($sale);
            $nonCashAmt = max(0, $sale->grand_total - $cashAmt);

            if ($cashAmt > 0) {
                $cashSalesCount++;
                $cashSalesSum += $cashAmt;
            }
            if ($nonCashAmt > 0) {
                $nonCashSalesCount++;
                $nonCashSalesSum += $nonCashAmt;
            }
        }

        $nonCashBreakdown = \App\Helpers\SaleHelper::getSalesNonCashBreakdown($sales);
        $totalSalesCount = $sales->count();
        $totalSalesSum = $sales->sum('grand_total');

        $isOpen = $cashSession->status === 'open';
        $cashTotals = \App\Helpers\CashSessionHelper::getSessionCashTotals($cashSession);
        if ($isOpen) {
            $expectedCash = (int) $cashSession->opening_cash + $cashSalesSum + $cashTotals['cash_in'] - $cashTotals['total_out'];
            $actualCash = null;
            $difference = null;
        } else {
            $expectedCash = (int) $cashSession->expected_cash;
            $actualCash = $cashSession->actual_cash;
            $difference = $cashSession->difference;
        }

        return [
            'session' => $cashSession,
            'isOpen' => $isOpen,
            'expectedCash' => $expectedCash,
            'actualCash' => $actualCash,
            'difference' => $difference,
            'cashInSum' => $cashTotals['cash_in'],
            'cashOutSum' => $cashTotals['cash_out'],
            'cashTransferOutSum' => $cashTotals['cash_transfer_out'],
            'cashSalesCount' => $cashSalesCount,
            'cashSalesSum' => $cashSalesSum,
            'nonCashSalesCount' => $nonCashSalesCount,
            'nonCashSalesSum' => $nonCashSalesSum,
            'nonCashBreakdown' => $nonCashBreakdown,
            'totalSalesCount' => $totalSalesCount,
            'totalSalesSum' => $totalSalesSum,
        ];
    }

    /**
     * Show the Z-Report for a specific cash session.
     */
    public function show(Request $request, CashSession $cashSession)
    {
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature(Auth::user(), ['fo.cash_session.view', 'fo.cash_session.close']), 403);
        abort_unless((int) $cashSession->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);
        // Enforce that only authorized managers or the cashier themselves can view the report
        if (Auth::id() !== $cashSession->user_id && !\App\Helpers\PermissionHelper::can(Auth::user(), ['fo.cash_session.close', 'fo.cash_session.reopen'])) {
            abort(403, 'Anda tidak diizinkan untuk melihat Z-Report ini.');
        }

        if ($request->query('format') === 'pdf' || $request->has('download')) {
            return $this->downloadPdf($cashSession);
        }

        $data = $this->getReportData($cashSession);

        return view('pos.z-report', $data);
    }

    /**
     * Download Z-Report as PDF.
     */
    public function downloadPdf(CashSession $cashSession)
    {
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature(Auth::user(), ['fo.cash_session.view', 'fo.cash_session.close']), 403);
        abort_unless((int) $cashSession->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);
        // Enforce that only authorized managers or the cashier themselves can view the report
        if (Auth::id() !== $cashSession->user_id && !\App\Helpers\PermissionHelper::can(Auth::user(), ['fo.cash_session.close', 'fo.cash_session.reopen'])) {
            abort(403, 'Anda tidak diizinkan untuk melihat Z-Report ini.');
        }

        $data = $this->getReportData($cashSession);

        // 80mm roll width: 80mm = ~226.77 pt (227 x 700 pt)
        $pdf = Pdf::loadView('pos.z-report-pdf', $data);
        $pdf->setPaper([0, 0, 226.77, 700], 'portrait');

        $filename = 'Z-Report-Sesi-' . str_pad($cashSession->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }
}
