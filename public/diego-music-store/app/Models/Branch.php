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
}
