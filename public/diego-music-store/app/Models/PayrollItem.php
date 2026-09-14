<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'branch_id',
        'basic_salary',
        'allowance_amount',
        'allowance_details',
        'overtime_amount',
        'overtime_details',
        'commission_amount',
        'kpi_bonus_amount',
        'violation_deduction_amount',
        'other_deduction_amount',
        'deduction_details',
        'net_salary',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'float',
        'allowance_amount' => 'float',
        'allowance_details' => 'array',
        'overtime_amount' => 'float',
        'overtime_details' => 'array',
        'commission_amount' => 'float',
        'kpi_bonus_amount' => 'float',
        'violation_deduction_amount' => 'float',
        'other_deduction_amount' => 'float',
        'deduction_details' => 'array',
        'net_salary' => 'float',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
