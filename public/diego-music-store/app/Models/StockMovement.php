<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'branch_id',
        'type', // in, out
        'quantity',
        'unit_cost',
        'hpp',
        'reference_type', // DO, Mutation, Opname, POS
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'integer',
        'hpp' => 'integer',
        'reference_id' => 'integer',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the owning reference model (DeliveryOrder, InventoryMutation, StockOpname, POS Transaction).
     */
    public function reference()
    {
        // Custom resolver for reference types stored as short strings
        switch ($this->reference_type) {
            case 'DO':
                return $this->belongsTo(DeliveryOrder::class, 'reference_id');
            case 'Mutation':
                return $this->belongsTo(InventoryMutation::class, 'reference_id');
            case 'Opname':
                return $this->belongsTo(StockOpname::class, 'reference_id');
            case 'Purchase':
                return $this->belongsTo(PurchaseTransaction::class, 'reference_id');
            // POS is not yet implemented, but we can add a placeholder or fallback
            default:
                return null;
        }
    }

    /**
     * Get the resolved reference model instance.
     */
    public function getReferenceModelAttribute()
    {
        $relation = $this->reference();
        return $relation ? $relation->first() : null;
    }

    /**
     * Get the human-readable label for the stock movement reference.
     */
    public function getReferenceLabelAttribute(): string
    {
        $ref = $this->reference_model;
        if (!$ref) {
            return "{$this->reference_type} #{$this->reference_id}";
        }

        switch ($this->reference_type) {
            case 'DO':
                return "Penerimaan DO: " . ($ref->do_number ?? "#{$this->reference_id}");
            case 'Mutation':
                return "Mutasi Barang: " . ($ref->mutation_number ?? "#{$this->reference_id}");
            case 'Opname':
                return "Stok Opname: " . ($ref->opname_number ?? "#{$this->reference_id}");
            case 'Purchase':
                return "Pembelian: " . ($ref->invoice_number ?? "#{$this->reference_id}");
            default:
                return "{$this->reference_type} #{$this->reference_id}";
        }
    }
}
