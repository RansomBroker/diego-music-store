<?php

namespace Tests\Feature\Actions;

use App\Actions\Employee\CreateEmployee;
use App\Actions\Employee\UpdateEmployee;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_update_employee_actions(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG-001',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Kasir Budi',
            'username' => 'kasirbudi',
            'email' => 'budi@diegomusic.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $createData = [
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'budi@diegomusic.com',
            'address' => 'Jl. Merdeka No. 10',
            'join_date' => '2026-01-15',
            'monthly_off_days_quota' => 4,
            'basic_salary' => 3500000.00,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'is_active' => true,
        ];

        $employee = app(CreateEmployee::class)->execute($createData);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('Budi Santoso', $employee->name);
        $this->assertStringStartsWith('EMP-', $employee->nik);
        $this->assertEquals($branch->id, $employee->branch_id);
        $this->assertEquals($user->id, $employee->user_id);
        $this->assertEquals(4, $employee->monthly_off_days_quota);

        $updateData = [
            'name' => 'Budi Santoso S.Kom',
            'basic_salary' => 4000000.00,
            'monthly_off_days_quota' => 5,
        ];

        $updated = app(UpdateEmployee::class)->execute($employee, $updateData);

        $this->assertEquals('Budi Santoso S.Kom', $updated->name);
        $this->assertEquals(4000000.00, $updated->basic_salary);
        $this->assertEquals(5, $updated->monthly_off_days_quota);
    }
}
