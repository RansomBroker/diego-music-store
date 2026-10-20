<?php

namespace App\Helpers;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchHelper
{
    /**
     * Resolve the active branch without granting unassigned users an implicit branch.
     */
    public static function getActiveBranchId(): int
    {
        $user = Auth::user();
        abort_unless($user, 403, 'Autentikasi diperlukan untuk mengakses cabang.');

        $sessionBranchId = session('pos_active_branch_id');
        $isManagement = $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']);

        if ($isManagement) {
            if ($sessionBranchId && Branch::query()->whereKey($sessionBranchId)->where('is_active', true)->exists()) {
                return (int) $sessionBranchId;
            }

            $defaultBranchId = Branch::query()->where('is_active', true)->value('id');
            abort_unless($defaultBranchId, 403, 'Belum ada cabang aktif yang tersedia.');

            session(['pos_active_branch_id' => $defaultBranchId]);

            return (int) $defaultBranchId;
        }

        $userBranchIds = $user->branches()
            ->where('branches.is_active', true)
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_unless($userBranchIds !== [], 403, 'Akun belum ditetapkan ke cabang aktif.');

        if ($sessionBranchId && in_array((int) $sessionBranchId, $userBranchIds, true)) {
            return (int) $sessionBranchId;
        }

        $firstAssignedId = $userBranchIds[0];
        session(['pos_active_branch_id' => $firstAssignedId]);

        return $firstAssignedId;
    }

    /**
     * Query only active branches available to the current user.
     */
    public static function getAllowedBranchesQuery()
    {
        $user = Auth::user();

        if ($user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin'])) {
            return Branch::query()->where('is_active', true)->orderBy('name');
        }

        if (!$user) {
            return Branch::query()->whereRaw('1 = 0');
        }

        return $user->branches()->where('branches.is_active', true)->orderBy('name');
    }
}
