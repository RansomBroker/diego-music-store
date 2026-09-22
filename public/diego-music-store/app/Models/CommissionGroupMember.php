<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_group_id',
        'employee_id',
        'monthly_target_amount',
    ];

    protected $casts = [
        'monthly_target_amount' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CommissionGroup::class, 'commission_group_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
