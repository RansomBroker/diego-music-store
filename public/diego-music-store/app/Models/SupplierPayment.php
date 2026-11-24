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
        'payment_method_id',
        'payment_reference',
        'total_amount',
        'notes',
        'status', // draft, posted, cancelled
        'posted_at',
        'journal_no',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'posted_at' => 'datetime',
        'total_amount' => 'integer',
        'payment_method_id' => 'integer',
        'journal_entry_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (empty($payment->payment_no)) {
                $payment->payment_no = static::generatePaymentNo();
            }
        });

        static::saving(function (self $payment): void {
            if (! \Illuminate\Support\Facades\Schema::hasColumn($payment->getTable(), 'payment_method_id')) {
                return;
            }
            if (! $payment->payment_method_id && ! empty($payment->payment_method)) {
                $clean = strtolower(trim((string) $payment->payment_method));
                $pm = in_array($clean, ['cash', 'tunai', 'kas'], true)
                    ? PaymentMethod::whereRaw('LOWER(code) = ?', ['cash'])->first()
                    : PaymentMethod::whereRaw('LOWER(TRIM(name)) = ?', [$clean])
                        ->orWhereRaw('LOWER(TRIM(code)) = ?', [$clean])
                        ->first();
                if ($pm) {
                    $payment->payment_method_id = $pm->id;
                }
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

    public function paymentMethodRelation(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return $this->paymentMethodRelation?->formatCheckoutLabel() ?? (string) ($this->payment_method ?: '-');
    }
}
