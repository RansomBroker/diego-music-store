<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_number',
        'customer_id',
        'branch_id',
        'user_id',
        'deposit_date',
        'product_type',
        'product_id',
        'product_variant_id',
        'product_name',
        'price',
        'qty',
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'account_id',
        'payment_method',
        'payment_reference',
        'notes',
        'status',
        'settled_at',
        'settled_by',
        'settlement_account_id',
        'settlement_payment_method',
        'settlement_reference',
        'settlement_notes',
        'deposit_journal_entry_id',
        'settlement_journal_entry_id',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'settled_at' => 'datetime',
        'price' => 'float',
        'qty' => 'integer',
        'total_amount' => 'float',
        'deposit_amount' => 'float',
        'remaining_amount' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($deposit) {
            if (empty($deposit->deposit_number)) {
                $deposit->deposit_number = static::generateDepositNumber();
            }
        });
    }

    /**
     * Generate sequential deposit number (e.g. DEP-20260914-0001).
     */
    public static function generateDepositNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'DEP-' . $date . '-';

        $last = static::where('deposit_number', 'like', $prefix . '%')
            ->orderBy('deposit_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = intval(substr($last->deposit_number, strlen($prefix)));
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function settlementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'settlement_account_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function depositJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'deposit_journal_entry_id');
    }

    public function settlementJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'settlement_journal_entry_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settled';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'settled' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu Pelunasan',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'settled' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
            'cancelled' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
            default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
        };
    }
}
