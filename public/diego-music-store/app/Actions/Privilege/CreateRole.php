<?php

namespace App\Actions\Privilege;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateRole
{
    /**
     * Execute role creation with optional assigned permissions.
     */
    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $guard = $data['guard_name'] ?? 'web';
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $guard,
            ]);

            if (! empty($data['permissions']) && is_array($data['permissions'])) {
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
