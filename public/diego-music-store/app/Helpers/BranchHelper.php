<?php

namespace App\Helpers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BranchHelper
{
    /**
     * Determine if a user has permissions or roles granting access to all active branches.
     */
    public static function canAccessAllBranches(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        // 1. Management and administrative roles with global branch access
        $managementRoles = [
            'owner', 'Owner',
            'super_admin', 'Super Admin', 'super-admin',
            'admin', 'Admin',
            'manager', 'Manager',
        ];
        if ($user->hasRole($managementRoles)) {
            return true;
        }

        // 2. Hak akses / permissions that grant branch management or switching all branches
        $branchPermissions = [
            'master.branches',
            'utility.store',
            'branch.switch',
        ];

        foreach ($branchPermissions as $perm) {
            if ($user->can($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a user can switch to a specific branch based on role, permission, or assignment.
     */
    public static function canUserSwitchToBranch(User $user, int|Branch $branch): bool
    {
        $branchId = $branch instanceof Branch ? $branch->id : (int) $branch;

        // Verify the target branch actually exists and is active
        $targetExists = Branch::query()->whereKey($branchId)->where('is_active', true)->exists();
        if (!$targetExists) {
            return false;
        }

        // 1. If user can access all branches via role or hak akses
        if (static::canAccessAllBranches($user)) {
            return true;
        }

        // 2. Otherwise, user must be explicitly assigned to this branch
        return $user->branches()
            ->where('branches.id', $branchId)
            ->where('branches.is_active', true)
            ->exists();
    }

    /**
     * Resolve the active branch without granting unassigned users an implicit branch.
     */
    public static function getActiveBranchId(): int
    {
        $user = Auth::user();
        abort_unless($user, 403, 'Autentikasi diperlukan untuk mengakses cabang.');

        $sessionBranchId = session('pos_active_branch_id');
        $isManagement = static::canAccessAllBranches($user);

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
     * Query only active branches available to the current user according to role & hak akses.
     */
    public static function getAllowedBranchesQuery(?User $user = null)
    {
        $user = $user ?? Auth::user();

        if ($user && static::canAccessAllBranches($user)) {
            return Branch::query()->where('is_active', true)->orderBy('name');
        }

        if (!$user) {
            return Branch::query()->whereRaw('1 = 0');
        }

        return $user->branches()->where('branches.is_active', true)->orderBy('name');
    }
}
