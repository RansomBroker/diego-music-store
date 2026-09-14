<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCashAdvance extends Model
{
    protected $fillable = [
        'advance_number',
        'employee_id',
        'branch_id',
        'request_date',
        'amount',
        'tenor_months',
        'monthly_installment',
        'paid_amount',
        'remaining_amount',
        'status',
        'approved_by',
        'approved_at',
        'disbursed_at',
        'reason',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateAdvanceNumber(): string
    {
        $prefix = 'ADV-' . now()->format('Ym') . '-';
        $last = self::where('advance_number', 'like', $prefix . '%')
            ->orderBy('advance_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->advance_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }
}
