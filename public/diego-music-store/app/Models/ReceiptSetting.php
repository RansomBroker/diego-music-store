<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'store_display_name',
        'header_text',
        'footer_text',
        'paper_width',
        'show_logo',
        'show_customer',
        'show_cashier',
        'show_tax_details',
        'invoice_footer_notes',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_customer' => 'boolean',
        'show_cashier' => 'boolean',
        'show_tax_details' => 'boolean',
    ];

    /**
     * Relasi ke Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
