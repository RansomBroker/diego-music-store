<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class UpdateEmployee
{
    /**
     * Execute the action to update an employee record.
     *
     * @param  Employee  $employee
     * @param  array<string, mixed>  $data
     * @return Employee
     */
    public function execute(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($data);
            return $employee->fresh();
        });
    }
}
