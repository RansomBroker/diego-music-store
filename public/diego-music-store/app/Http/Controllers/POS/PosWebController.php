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
        return redirect()->route('pos.reports.sales');
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
