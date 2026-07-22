<?php

namespace App\Actions\Privilege;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UpdateRolePermissions
{
    /**
     * Execute updating role name and sync its permissions.
     *
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function execute(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            if (!empty($data['name'])) {
                $role->update(['name' => $data['name']]);
            }

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role;
        });
    }
}
