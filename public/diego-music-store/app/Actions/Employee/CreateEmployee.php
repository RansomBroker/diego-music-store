<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CreateEmployee
{
    /**
     * Execute the action to create an employee record.
     *
     * @param  array<string, mixed>  $data
     * @return Employee
     */
    public function execute(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // Auto-generate NIK if empty
            if (empty($data['nik'])) {
                $lastId = Employee::withTrashed()->max('id') ?? 0;
                $data['nik'] = 'EMP-' . str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);
            }

            return Employee::create($data);
        });
    }
}
