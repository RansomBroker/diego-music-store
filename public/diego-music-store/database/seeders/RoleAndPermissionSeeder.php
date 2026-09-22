<?php

namespace Database\Seeders;

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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all system permissions
        $permissions = [
            // POS & Penjualan
            'pos.access',
            'pos.discount',
            'pos.hold',
            'pos.void',

            // Kas & Keuangan
            'cash.session',
            'daily_cash.view',
            'daily_cash.manage',
            'supplier_payments.manage',

            // Data Master
            'master.customers',
            'master.users',
            'master.units',
            'master.categories',
            'master.payment_methods',

            // Utility & Pengaturan
            'utility.privileges',
            'utility.store',
            'utility.receipt',
            'utility.barcode',
            'utility.backup',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Create simplified standard roles
        $ownerRole    = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $adminRole    = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $karyawanRole = Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);
        $salesRole    = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        // 3. Assign permissions to roles
        // Owner & Admin: Full permissions
        $ownerRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);

        // Karyawan (Kasir): POS operations, session, daily cash, customers, receipt, barcode
        $karyawanRole->syncPermissions([
            'pos.access',
            'pos.discount',
            'pos.hold',
            'pos.void',
            'cash.session',
            'daily_cash.view',
            'daily_cash.manage',
            'master.customers',
            'utility.receipt',
            'utility.barcode',
        ]);

        // Sales: POS access and customers
        $salesRole->syncPermissions([
            'pos.access',
            'master.customers',
        ]);
    }
}
