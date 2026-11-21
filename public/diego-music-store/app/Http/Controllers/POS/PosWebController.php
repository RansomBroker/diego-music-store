<?php

namespace App\Http\Controllers\POS;

use App\Helpers\BranchHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosWebController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pos.login');
    }

    public function redirectReports(): RedirectResponse
    {
        $user = Auth::user();
        $reportRoutes = [
            'fo.reports.sales' => 'pos.reports.sales',
            'fo.reports.sales_queue' => 'pos.reports.sales-queue',
            'fo.reports.ar_aging' => 'pos.reports.ar-aging',
            'fo.reports.ar_settlement' => 'pos.reports.ar-settlement',
            'fo.reports.daily_cash' => 'pos.reports.daily-cash',
            'fo.reports.stock_prices' => 'pos.reports.stock-prices',
        ];

        foreach ($reportRoutes as $permission => $routeName) {
            if (\App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, $permission)) {
                return redirect()->route($routeName);
            }
        }

        abort(403, 'Anda tidak memiliki hak akses ke laporan Front Office.');
    }

    public function switchBranch(Branch $branch): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (BranchHelper::canUserSwitchToBranch($user, $branch)) {
                // Prevent branch switching for non-management cashiers with an open cash session
                if (!BranchHelper::canAccessAllBranches($user)) {
                    $currentBranchId = session('pos_active_branch_id');
                    $hasActiveSession = $currentBranchId && CashSession::query()
                        ->where('branch_id', $currentBranchId)
                        ->where('status', 'open')
                        ->where('user_id', $user->id)
                        ->exists();

                    if ($hasActiveSession && (int) $branch->id !== (int) $currentBranchId) {
                        return redirect()->back()->with('error', 'Tidak dapat berpindah cabang saat sesi kasir Anda sedang aktif.');
                    }
                }

                session(['pos_active_branch_id' => $branch->id]);
                session()->save();
            }
        }

        return redirect()->back();
    }

    public function redirectAdmin(Request $request, ?string $path = null): RedirectResponse
    {
        $queryString = $request->getQueryString();
        $targetUrl = '/backoffice' . ($path ? '/' . $path : '') . ($queryString ? '?' . $queryString : '');

        return redirect($targetUrl);
    }
}
