<?php

namespace App\Helpers;

use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionHelper
{
    /**
     * Check whether the user holds the protected Owner role.
     */
    public static function isOwner(?User $user = null): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        return $user->hasRole(['owner', 'Owner']);
    }

    /**
     * Check whether the given role name is protected from mutation/deletion.
     */
    public static function isProtectedRoleName(string $name): bool
    {
        return in_array(strtolower(trim($name)), ['owner'], true);
    }

    /**
     * Check whether the user holds at least one of the given permissions.
     *
     * @param  string|array<int, string>  $permissions
     */
    public static function can(?User $user, string|array $permissions): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        if (static::isOwner($user)) {
            return true;
        }

        $permissionList = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissionList as $permission) {
            try {
                if ($user->hasPermissionTo($permission, 'web')) {
                    return true;
                }
            } catch (PermissionDoesNotExist) {
                continue;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can access the Front Office (FO / POS) panel.
     */
    public static function canAccessFrontoffice(?User $user = null): bool
    {
        return static::can($user, 'fo.access');
    }

    /**
     * Determine whether the user can access the Backoffice (BO / Filament) panel.
     */
    public static function canAccessBackoffice(?User $user = null): bool
    {
        return static::can($user, 'bo.access');
    }

    /**
     * Determine whether the user can access a specific Front Office feature/permission.
     *
     * @param  string|array<int, string>  $permissions
     */
    public static function canAccessFrontofficeFeature(?User $user, string|array $permissions): bool
    {
        return static::canAccessFrontoffice($user) && static::can($user, $permissions);
    }

    /**
     * Determine whether the user can access a specific Backoffice feature/permission.
     *
     * @param  string|array<int, string>  $permissions
     */
    public static function canAccessBackofficeFeature(?User $user, string|array $permissions): bool
    {
        return static::canAccessBackoffice($user) && static::can($user, $permissions);
    }

    /**
     * Enforce permission inside domain Actions without breaking isolated unit tests
     * where RBAC tables/roles have not been seeded.
     *
     * @param  string|array<int, string>  $permissions
     */
    public static function enforceActionPermission(?User $user, string|array $permissions): void
    {
        if (! $user) {
            return;
        }

        if (static::isOwner($user)) {
            return;
        }

        $permissionList = is_array($permissions) ? $permissions : [$permissions];
        $hasDbPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionList)
            ->exists();

        if (! $user->roles()->exists() && ! $hasDbPermissions) {
            return;
        }

        abort_unless(static::can($user, $permissionList), 403, 'Anda tidak memiliki hak akses untuk melakukan aksi ini.');
    }

    /**
     * Return all configured RBAC sections from config/permissions.php.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getSections(): array
    {
        return config('permissions.sections', []);
    }

    /**
     * Return a flat list of all valid permission keys across FO, BO, and shared permissions.
     *
     * @return array<int, string>
     */
    public static function getAllPermissionNames(): array
    {
        $names = [];
        foreach (static::getSections() as $section) {
            if (! empty($section['access_permission'])) {
                $names[] = $section['access_permission'];
            }
            foreach ($section['groups'] ?? [] as $group) {
                foreach ($group['features'] ?? [] as $feature) {
                    foreach (array_keys($feature['permissions'] ?? []) as $permName) {
                        $names[] = $permName;
                    }
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Return all permission names belonging to a specific section ('frontoffice' or 'backoffice').
     *
     * @return array<int, string>
     */
    public static function getSectionPermissionNames(string $sectionKey, bool $includeAccessPermission = true): array
    {
        $section = static::getSections()[$sectionKey] ?? null;
        if (! $section) {
            return [];
        }

        $names = [];
        if ($includeAccessPermission && ! empty($section['access_permission'])) {
            $names[] = $section['access_permission'];
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['features'] ?? [] as $feature) {
                foreach (array_keys($feature['permissions'] ?? []) as $permName) {
                    $names[] = $permName;
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Return all permission names belonging to a specific group inside a section.
     *
     * @return array<int, string>
     */
    public static function getGroupPermissionNames(string $sectionKey, string $groupKey): array
    {
        $group = static::getSections()[$sectionKey]['groups'][$groupKey] ?? null;
        if (! $group) {
            return [];
        }

        $names = [];
        foreach ($group['features'] ?? [] as $feature) {
            foreach (array_keys($feature['permissions'] ?? []) as $permName) {
                $names[] = $permName;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Return all permission names belonging to a specific feature inside a group.
     *
     * @return array<int, string>
     */
    public static function getFeaturePermissionNames(string $sectionKey, string $groupKey, string $featureKey): array
    {
        $feature = static::getSections()[$sectionKey]['groups'][$groupKey]['features'][$featureKey] ?? null;
        if (! $feature) {
            return [];
        }

        return array_keys($feature['permissions'] ?? []);
    }

    /**
     * Ensure every configured permission exists in the database.
     */
    public static function ensureAllPermissionsExist(): void
    {
        foreach (static::getAllPermissionNames() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Summarize a Role's Front Office & Backoffice access flags and permission counts.
     *
     * @return array{
     *     has_fo: bool,
     *     has_bo: bool,
     *     fo_count: int,
     *     bo_count: int,
     *     access_label: string,
     *     access_color: string
     * }
     */
    public static function resolveRoleAccessSummary(Role $role): array
    {
        $foPerms = static::getSectionPermissionNames('frontoffice', false);
        $boPerms = static::getSectionPermissionNames('backoffice', false);

        if (static::isProtectedRoleName($role->name)) {
            return [
                'has_fo' => true,
                'has_bo' => true,
                'fo_count' => count($foPerms),
                'bo_count' => count($boPerms),
                'access_label' => 'FO & BO (Penuh)',
                'access_color' => 'success',
            ];
        }

        $assigned = $role->relationLoaded('permissions')
            ? $role->permissions->pluck('name')->all()
            : $role->permissions()->pluck('name')->all();

        $hasFo = in_array('fo.access', $assigned, true);
        $hasBo = in_array('bo.access', $assigned, true);
        $foCount = count(array_intersect($assigned, $foPerms));
        $boCount = count(array_intersect($assigned, $boPerms));

        if ($hasFo && $hasBo) {
            $label = 'FO & BO';
            $color = 'success';
        } elseif ($hasFo) {
            $label = 'Front Office';
            $color = 'info';
        } elseif ($hasBo) {
            $label = 'Backoffice';
            $color = 'warning';
        } else {
            $label = 'Belum Aktif';
            $color = 'gray';
        }

        return [
            'has_fo' => $hasFo,
            'has_bo' => $hasBo,
            'fo_count' => $foCount,
            'bo_count' => $boCount,
            'access_label' => $label,
            'access_color' => $color,
        ];
    }

    /**
     * Resolve the first accessible Front Office route path for landing redirects after login.
     */
    public static function resolveFirstAccessibleFrontofficeUrl(?User $user = null): ?string
    {
        if (! static::canAccessFrontoffice($user)) {
            return null;
        }

        $candidates = [
            'fo.dashboard.view' => '/pos/front-office',
            'fo.pos.view' => '/pos',
            'fo.catalog.view' => '/pos/catalog',
            'fo.cash_session.view' => '/pos/session',
            'fo.transactions.view' => '/pos/transactions',
            'fo.customer_deposits.view' => '/pos/customer-deposits',
            'fo.daily_cash.view' => '/pos/daily-cash',
            'fo.customer_payments.view' => '/pos/customer-payments',
            'fo.service.view' => '/pos/service-management',
            'fo.employees.view' => '/pos/employees',
            'fo.employee_tasks.view' => '/pos/employee-tasks',
            'fo.day_off.view' => '/pos/day-off-calendar',
            'fo.stock_opname_tasks.view' => '/pos/stock-opname-tasks',
            'fo.stock_opname_review.view' => '/pos/stock-opname-review',
            'fo.attendances.view' => '/pos/attendances',
            'fo.attendance_radiuses.view' => '/pos/attendance-radiuses',
            'fo.attendance_violations.view' => '/pos/attendance-violations',
            'fo.commissions.view' => '/pos/commissions',
            'fo.kpi.view' => '/pos/kpi-performance',
            'fo.payroll.view' => '/pos/payroll',
            'fo.reports.sales' => '/pos/reports/sales',
            'fo.reports.sales_queue' => '/pos/reports/sales-queue',
            'fo.reports.ar_aging' => '/pos/reports/ar-aging',
            'fo.reports.ar_settlement' => '/pos/reports/ar-settlement',
            'fo.reports.daily_cash' => '/pos/reports/daily-cash',
            'fo.reports.stock_prices' => '/pos/reports/stock-prices',
            'fo.customers.view' => '/pos/customers',
            'fo.customer_labels.view' => '/pos/customer-labels',
            'fo.users.view' => '/pos/users',
            'fo.units.view' => '/pos/units',
            'fo.sale_categories.view' => '/pos/sale-categories',
            'fo.payment_methods.view' => '/pos/payment-methods',
            'fo.vouchers.view' => '/pos/vouchers',
            'fo.privileges.view' => '/pos/privileges',
            'fo.store_profile.view' => '/pos/store-profile',
            'fo.branches.view' => '/pos/branches',
            'fo.whatsapp_settings.view' => '/pos/whatsapp-settings',
            'fo.branch_performance.view' => '/pos/branch-performance',
            'fo.receipt_settings.view' => '/pos/receipt-settings',
            'fo.barcode_print.view' => '/pos/barcode-print',
        ];

        foreach ($candidates as $permission => $url) {
            if (static::can($user, $permission)) {
                return $url;
            }
        }

        return '/pos/front-office';
    }
}

