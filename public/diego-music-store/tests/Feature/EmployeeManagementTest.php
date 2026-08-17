<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_relationships_and_role_access(): void
    {
        $roleSales = Role::firstOrCreate(['name' => 'sales']);
        
        $branch = Branch::create([
            'name' => 'Cabang Denpasar',
            'code' => 'DPS-01',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Siti Rahma',
            'username' => 'sitisales',
            'email' => 'siti@diegomusic.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->assignRole($roleSales);

        $employee = $user->employee;
        $employee->update([
            'nik' => 'EMP-0002',
            'phone' => '08987654321',
            'branch_id' => $branch->id,
            'monthly_off_days_quota' => 4,
            'basic_salary' => 3000000.00,
            'is_active' => true,
        ]);

        // Test relationships
        $this->assertNotNull($employee->user);
        $this->assertEquals('sitisales', $employee->user->username);
        $this->assertTrue($employee->user->hasRole('sales'));

        $this->assertNotNull($employee->branch);
        $this->assertEquals('Cabang Denpasar', $employee->branch->name);

        $this->assertNotNull($user->employee);
        $this->assertEquals('Siti Rahma', $user->employee->name);

        // Test soft delete
        $employee->delete();
        $this->assertSoftDeleted($employee);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_user_creation_auto_creates_employee_profile(): void
    {
        $user = User::create([
            'name' => 'Staf Baru',
            'username' => 'stafbaru',
            'email' => 'stafbaru@diegomusic.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'name' => 'Staf Baru',
            'email' => 'stafbaru@diegomusic.com',
        ]);

        $this->assertNotNull($user->fresh()->employee);
        $this->assertStringStartsWith('EMP-', $user->employee->nik);
    }

    public function test_user_seeder_syncs_all_default_users_to_employees(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->assertDatabaseHas('employees', ['email' => 'admin@admin.com']);
        $this->assertDatabaseHas('employees', ['email' => 'kasir@admin.com']);
        $this->assertDatabaseHas('employees', ['email' => 'admin@diegomusic.com']);
        $this->assertDatabaseHas('employees', ['email' => 'owner@admin.com']);
    }
}
