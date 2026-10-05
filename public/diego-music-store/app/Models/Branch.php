<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store_name',
        'sku_prefix',
        'journal_prefix',
        'logo_path',
        'address',
        'phone',
        'email',
        'city',
        'province',
        'postal_code',
        'npwp',
        'bank_info',
        'receipt_header',
        'receipt_footer',
        'latitude',
        'longitude',
        'attendance_radius_meters',
        'shift_start_time',
        'shift_end_time',
        'manager_id',
        'fonnte_token',
        'fonnte_whatsapp_number',
        'is_whatsapp_enabled',
        'is_active',
        'inventory_account_id',
        'interbranch_receivable_account_id',
        'interbranch_payable_account_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'attendance_radius_meters' => 'integer',
        'is_whatsapp_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the manager user assigned to this branch.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users assigned to this branch.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    /**
     * Get the inventory account for this branch.
     */
    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    /**
     * Get the inter-branch receivable account for this branch.
     */
    public function interbranchReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'interbranch_receivable_account_id');
    }

    /**
     * Get the inter-branch payable account for this branch.
     */
    public function interbranchPayableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'interbranch_payable_account_id');
    }
}
