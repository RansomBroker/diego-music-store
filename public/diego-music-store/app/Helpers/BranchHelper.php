<?php

namespace App\Helpers;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchHelper
{
    /**
     * Get current active branch ID for POS with strict security check:
     * - Owner / Admin can access any active branch stored in session.
     * - Regular staff can only access branches assigned to them in branch_user.
     *
     * @return int
     */
    public static function getActiveBranchId(): int
    {
        $user = Auth::user();

        if (!$user) {
            $sessionBranchId = session('pos_active_branch_id');
            return $sessionBranchId ?: (Branch::where('is_active', true)->value('id') ?: 1);
        }

        $sessionBranchId = session('pos_active_branch_id');

        // Owner / Admin / Super Admin can access any active branch
        if ($user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            if ($sessionBranchId && Branch::where('id', $sessionBranchId)->where('is_active', true)->exists()) {
                return (int) $sessionBranchId;
            }
            $defaultBranchId = Branch::where('is_active', true)->value('id') ?: 1;
            session(['pos_active_branch_id' => $defaultBranchId]);
            return (int) $defaultBranchId;
        }

        // Regular staff: check assigned branches in branch_user
        $userBranchIds = $user->branches()->where('is_active', true)->pluck('branches.id')->toArray();

        if (empty($userBranchIds)) {
            // Fallback if no branch assigned yet
            return Branch::where('is_active', true)->value('id') ?: 1;
        }

        if ($sessionBranchId && in_array((int)$sessionBranchId, $userBranchIds)) {
            return (int) $sessionBranchId;
        }

        // Auto fallback to user's first assigned branch
        $firstAssignedId = $userBranchIds[0];
        session(['pos_active_branch_id' => $firstAssignedId]);
        return (int) $firstAssignedId;
    }

    /**
     * Get query builder of branches accessible by current authenticated user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getAllowedBranchesQuery()
    {
        $user = Auth::user();

        if (!$user || $user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            return Branch::where('is_active', true)->orderBy('name');
        }

        return $user->branches()->where('is_active', true)->orderBy('name');
    }
}
