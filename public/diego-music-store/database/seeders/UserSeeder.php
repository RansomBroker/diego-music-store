<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure standard roles exist
        $roles = ['owner', 'admin', 'cashier', 'sales', 'technician'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $branches = Branch::all();

        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin Backoffice',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin', 'owner']);
        if ($branches->isNotEmpty()) {
            $admin->branches()->sync($branches->pluck('id'));
        }

        // 2. Kasir User
        $kasir = User::updateOrCreate(
            ['email' => 'kasir@admin.com'],
            [
                'name' => 'Kasir Utama',
                'username' => 'kasir',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $kasir->syncRoles(['sales', 'cashier']);
        if ($branches->isNotEmpty()) {
            $kasir->branches()->sync($branches->pluck('id'));
        }

        // 3. Diego Admin User
        $diegoAdmin = User::updateOrCreate(
            ['email' => 'admin@diegomusic.com'],
            [
                'name' => 'Diego Admin',
                'username' => 'diegoadmin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $diegoAdmin->syncRoles(['admin', 'owner']);
        if ($branches->isNotEmpty()) {
            $diegoAdmin->branches()->sync($branches->pluck('id'));
        }

        // 4. Owner User
        $ownerUser = User::updateOrCreate(
            ['email' => 'owner@admin.com'],
            [
                'name' => 'Owner Diego Music Store',
                'username' => 'owner',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $ownerUser->syncRoles(['owner', 'admin']);
        if ($branches->isNotEmpty()) {
            $ownerUser->branches()->sync($branches->pluck('id'));
        }

        $users = [$admin, $kasir, $diegoAdmin, $ownerUser];
        foreach ($users as $u) {
            Employee::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'nik' => 'EMP-' . str_pad((string) $u->id, 4, '0', STR_PAD_LEFT),
                    'name' => $u->name,
                    'email' => $u->email,
                    'branch_id' => $branches->first()?->id,
                    'monthly_off_days_quota' => 4,
                    'basic_salary' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
