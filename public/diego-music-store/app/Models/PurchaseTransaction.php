<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'transaction_date',
        'po_id',
        'supplier_id',
        'branch_id',
        'warehouse_id',
        'purchase_type', // Tunai, Kredit
        'invoice_number',
        'delivery_note_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount',
        'shipping_cost',
        'other_cost',
        'tax_amount', // PPN
        'pph_amount', // PPh
        'grand_total',
        'status', // draft, posted, cancelled
        'shipping_borne_by',
        'shipping_carrier_name',
        'posted_at',
        'journal_no',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'posted_at' => 'datetime',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'shipping_cost' => 'integer',
        'other_cost' => 'integer',
        'tax_amount' => 'integer',
        'pph_amount' => 'integer',
        'grand_total' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($pt) {
            if (empty($pt->transaction_no)) {
                $pt->transaction_no = static::generateTransactionNo();
            }
        });
    }

    public static function generateTransactionNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'PT-' . $date . '-';

        $lastPt = static::where('transaction_no', 'like', $prefix . '%')
            ->orderBy('transaction_no', 'desc')
            ->first();

        if ($lastPt) {
            $lastNum = intval(substr($lastPt->transaction_no, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseTransactionDetail::class);
    }

    public function supplierPaymentItems(): HasMany
    {
        return $this->hasMany(SupplierPaymentItem::class);
    }

    /**
     * Get the remaining unpaid balance for this credit purchase transaction.
     */
    public function getRemainingUnpaidAmount(): int
    {
        if ($this->purchase_type !== 'Kredit') {
            return 0;
        }

        // Sum up all payments made for this purchase from POSTED payments
        $paid = $this->supplierPaymentItems()
            ->whereHas('supplierPayment', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('amount_paid');

        return max(0, $this->grand_total - $paid);
    }
}
