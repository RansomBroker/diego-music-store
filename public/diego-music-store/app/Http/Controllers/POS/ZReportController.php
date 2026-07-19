<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZReportController extends Controller
{
    /**
     * Show the Z-Report for a specific cash session.
     */
    public function show(CashSession $cashSession)
    {
        // Enforce that only authorized users or the cashier themselves can view the report
        if (Auth::id() !== $cashSession->user_id && !Auth::user()->hasAnyRole(['owner', 'admin'])) {
            abort(403, 'Anda tidak diizinkan untuk melihat Z-Report ini.');
        }

        // Calculate sales summaries
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

        return view('pos.z-report', [
            'session' => $cashSession,
            'cashSalesCount' => $cashSalesCount,
            'cashSalesSum' => $cashSalesSum,
            'nonCashSalesCount' => $nonCashSalesCount,
            'nonCashSalesSum' => $nonCashSalesSum,
            'totalSalesCount' => $totalSalesCount,
            'totalSalesSum' => $totalSalesSum,
        ]);
    }
}
