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
        // Ensure simplified standard roles exist
        $roles = ['owner', 'admin', 'karyawan', 'sales'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $branches = Branch::all();
        $mainBranch = $branches->first();

        // 1. Owner User (All Branches)
        $ownerUser = User::updateOrCreate(
            ['email' => 'owner@admin.com'],
            [
                'name' => 'Owner Diego Music Store',
                'username' => 'owner',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $ownerUser->syncRoles(['owner']);
        if ($branches->isNotEmpty()) {
            $ownerUser->branches()->sync($branches->pluck('id'));
        }

        // 2. Admin User (All Branches)
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin Backoffice',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);
        if ($branches->isNotEmpty()) {
            $admin->branches()->sync($branches->pluck('id'));
        }

        // 3. Diego Admin User (All Branches)
        $diegoAdmin = User::updateOrCreate(
            ['email' => 'admin@diegomusic.com'],
            [
                'name' => 'Diego Admin',
                'username' => 'diegoadmin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $diegoAdmin->syncRoles(['admin']);
        if ($branches->isNotEmpty()) {
            $diegoAdmin->branches()->sync($branches->pluck('id'));
        }

        // 4. Karyawan Kasir User (Khusus Cabang Utama)
        $karyawan = User::updateOrCreate(
            ['email' => 'kasir@admin.com'],
            [
                'name' => 'Karyawan Kasir Utama',
                'username' => 'kasir',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $karyawan->syncRoles(['karyawan']);
        if ($mainBranch) {
            $karyawan->branches()->sync([$mainBranch->id]);
        }

        // 5. Sales Staff User (Khusus Cabang Utama)
        $salesUser = User::updateOrCreate(
            ['email' => 'sales@admin.com'],
            [
                'name' => 'Staf Sales Executive',
                'username' => 'sales',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $salesUser->syncRoles(['sales', 'karyawan']);
        if ($mainBranch) {
            $salesUser->branches()->sync([$mainBranch->id]);
        }

        $users = [$ownerUser, $admin, $diegoAdmin, $karyawan, $salesUser];
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
