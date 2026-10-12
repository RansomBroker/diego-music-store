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

        $totalSalesCount = $sales->count();
        $totalSalesSum = $sales->sum('grand_total');

        return [
            'session' => $cashSession,
            'cashSalesCount' => $cashSalesCount,
            'cashSalesSum' => $cashSalesSum,
            'nonCashSalesCount' => $nonCashSalesCount,
            'nonCashSalesSum' => $nonCashSalesSum,
            'totalSalesCount' => $totalSalesCount,
            'totalSalesSum' => $totalSalesSum,
        ];
    }

    /**
     * Show the Z-Report for a specific cash session.
     */
    public function show(Request $request, CashSession $cashSession)
    {
        // Enforce that only authorized users or the cashier themselves can view the report
        if (Auth::id() !== $cashSession->user_id && !Auth::user()->hasAnyRole(['owner', 'admin'])) {
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
        // Enforce that only authorized users or the cashier themselves can view the report
        if (Auth::id() !== $cashSession->user_id && !Auth::user()->hasAnyRole(['owner', 'admin'])) {
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
