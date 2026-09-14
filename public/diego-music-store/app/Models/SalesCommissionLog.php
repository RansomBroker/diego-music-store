<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCommissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'sale_id',
        'commission_scheme_id',
        'date',
        'sale_amount',
        'commission_amount',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'sale_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function commissionScheme(): BelongsTo
    {
        return $this->belongsTo(CommissionScheme::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
