<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFeaturePermission
{
    /**
     * Handle an incoming request and verify panel + feature permissions.
     */
    public function handle(Request $request, Closure $next, string $permissions, string $section = 'frontoffice'): Response
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Autentikasi diperlukan.');
        }

        $permissionList = array_values(array_filter(array_map('trim', explode('|', $permissions))));

        $allowed = $section === 'backoffice'
            ? PermissionHelper::canAccessBackofficeFeature($user, $permissionList)
            : PermissionHelper::canAccessFrontofficeFeature($user, $permissionList);

        abort_unless($allowed, 403, 'Anda tidak memiliki hak akses untuk fitur ini.');

        return $next($request);
    }
}

