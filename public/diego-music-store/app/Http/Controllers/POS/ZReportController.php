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
        $salesQuery = $cashSession->sales()->where('status', 'completed');
        
        $cashSalesCount = (clone $salesQuery)->where('payment_method', 'cash')->count();
        $cashSalesSum = (clone $salesQuery)->where('payment_method', 'cash')->sum('grand_total');

        $nonCashSalesCount = (clone $salesQuery)->where('payment_method', '!=', 'cash')->count();
        $nonCashSalesSum = (clone $salesQuery)->where('payment_method', '!=', 'cash')->sum('grand_total');

        $totalSalesCount = $salesQuery->count();
        $totalSalesSum = $salesQuery->sum('grand_total');

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
