<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'created_by',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'status', // draft, sent, approved, rejected, closed
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'integer',
        'discount_value' => 'integer',
        'discount_amount' => 'integer',
        'tax_rate' => 'integer',
        'tax_amount' => 'integer',
        'grand_total' => 'integer',
    ];

    public static function generateQuotationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'SQ-' . $date . '-';

        $lastRecord = static::where('quotation_number', 'like', $prefix . '%')
            ->orderBy('quotation_number', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNum = intval(substr($lastRecord->quotation_number, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesQuotationItem::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'sales_quotation_id');
    }
}
