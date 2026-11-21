<?php

namespace App\Actions\Privilege;

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UpdateRolePermissions
{
    /**
     * Execute updating role name and sync its permissions.
     */
    public function execute(Role $role, array $data): Role
    {
        abort_if(PermissionHelper::isProtectedRoleName($role->name), 403, 'Role Owner bawaan sistem tidak dapat dimodifikasi.');

        return DB::transaction(function () use ($role, $data) {
            $guard = $role->guard_name ?: 'web';

            if (! empty($data['name']) && ! PermissionHelper::isProtectedRoleName((string) $data['name'])) {
                $role->update(['name' => $data['name']]);
            }

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $permissions = array_values(array_unique(array_filter($data['permissions'])));
                foreach ($permissions as $permission) {
                    Permission::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $guard,
                    ]);
                }
                $role->syncPermissions($permissions);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role;
        });
    }
}
