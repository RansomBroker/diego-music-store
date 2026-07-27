<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'sales_quotation_id',
        'created_by',
        'posted_by',
        'invoice_number',
        'invoice_date',
        'due_date',
        'payment_type', // Tunai, Kredit
        'status', // draft, posted, cancelled
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        'notes',
        'posted_at',
        'journal_no',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'posted_at' => 'datetime',
        'subtotal' => 'integer',
        'discount_value' => 'integer',
        'discount_amount' => 'integer',
        'tax_rate' => 'integer',
        'tax_amount' => 'integer',
        'shipping_cost' => 'integer',
        'grand_total' => 'integer',
    ];

    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'INV-' . $date . '-';

        $lastRecord = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNum = intval(substr($lastRecord->invoice_number, strlen($prefix)));
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

    public function salesQuotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }
}
