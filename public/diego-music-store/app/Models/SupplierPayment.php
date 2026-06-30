<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_no',
        'payment_date',
        'supplier_id',
        'branch_id',
        'account_id',
        'payment_method',
        'payment_reference',
        'total_amount',
        'notes',
        'status', // draft, posted, cancelled
        'posted_at',
        'journal_no',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'posted_at' => 'datetime',
        'total_amount' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (empty($payment->payment_no)) {
                $payment->payment_no = static::generatePaymentNo();
            }
        });
    }

    public static function generatePaymentNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'SP-' . $date . '-';

        $lastPayment = static::where('payment_no', 'like', $prefix . '%')
            ->orderBy('payment_no', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNum = intval(substr($lastPayment->payment_no, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierPaymentItem::class);
    }
}
