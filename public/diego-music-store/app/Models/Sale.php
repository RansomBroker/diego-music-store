<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'cash_session_id',
        'customer_id',
        'sales_rep_id',
        'invoice_number',
        'invoice_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_method',
        'status',
        'created_by',
        'sale_category',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'grand_total' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public static function generateInvoiceNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = 'INV-' . $dateStr . '-';

        $lastSale = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastSale) {
            $lastNum = intval(substr($lastSale->invoice_number, -4));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function getPiutangAmount(): float
    {
        $journalEntries = JournalEntry::where('reference_type', 'Sales')
            ->where('reference_id', $this->id)
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        $piutangAccount = Account::where('code', '1-1200')->first();
        if ($piutangAccount) {
            foreach ($journalEntries as $je) {
                $items = JournalItem::where('journal_entry_id', $je->id)
                    ->where('account_id', $piutangAccount->id)
                    ->get();
                foreach ($items as $item) {
                    $totalDebit += floatval($item->debit);
                    $totalCredit += floatval($item->credit);
                }
            }
        }

        $remaining = max(0, $totalDebit - $totalCredit);
        if ($totalDebit > 0) {
            return $remaining;
        }

        if (str_contains(strtolower($this->payment_method), 'piutang') || str_contains(strtolower($this->payment_method), 'credit')) {
            return floatval($this->grand_total);
        }

        return 0;
    }
}
