<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosActionOtpLog extends Model
{
    protected $fillable = [
        'pos_action_otp_id',
        'user_id',
        'branch_id',
        'action',
        'sale_id',
        'invoice_number',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function otp(): BelongsTo
    {
        return $this->belongsTo(PosActionOtp::class, 'pos_action_otp_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

