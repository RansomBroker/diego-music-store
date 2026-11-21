<?php

namespace Database\Seeders;

use App\Helpers\PermissionHelper;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Sync all system permissions from config/permissions.php via PermissionHelper
        PermissionHelper::ensureAllPermissionsExist();
        $allPermissions = PermissionHelper::getAllPermissionNames();

        // Remove obsolete permissions that are no longer defined in config/permissions.php
        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $allPermissions)
            ->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Standard system roles (do not recreate retired admin role)
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $adminRole = Role::query()->where('name', 'admin')->where('guard_name', 'web')->first();
        $adminRole?->syncPermissions([]);

        $karyawanRole = Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
        $salesRole = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        // 3. Assign permissions to standard roles
        // Owner: full access across FO, BO, and sensitive fields
        $ownerRole->syncPermissions($allPermissions);

        // Karyawan (Kasir operasional): Front Office operational permissions only (no BO, no admin/HR config, no cost/profit/salary)
        $karyawanRole->syncPermissions([
            'fo.access',
            'fo.dashboard.view',
            'fo.catalog.view',
            'fo.pos.view',
            'fo.pos.checkout',
            'fo.pos.discount',
            'fo.pos.hold',
            'fo.pos.print',
            'fo.cash_session.view',
            'fo.cash_session.open',
            'fo.cash_session.close',
            'fo.transactions.view',
            'fo.transactions.return',
            'fo.transactions.settle',
            'fo.customer_deposits.view',
            'fo.customer_deposits.create',
            'fo.customer_deposits.edit',
            'fo.daily_cash.view',
            'fo.daily_cash.create',
            'fo.customer_payments.view',
            'fo.customer_payments.create',
            'fo.service.view',
            'fo.service.edit',
            'fo.employee_tasks.view',
            'fo.day_off.view',
            'fo.stock_opname_tasks.view',
            'fo.attendances.view',
            'fo.commissions.view',
            'fo.kpi.view',
            'fo.reports.sales',
            'fo.reports.sales_queue',
            'fo.reports.ar_aging',
            'fo.reports.ar_settlement',
            'fo.reports.daily_cash',
            'fo.reports.stock_prices',
            'fo.customers.view',
            'fo.customers.create',
            'fo.customers.edit',
            'fo.barcode_print.view',
        ]);

        // Sales: Front Office read-only catalog & basic dashboard
        $salesRole->syncPermissions([
            'fo.access',
            'fo.dashboard.view',
            'fo.catalog.view',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
