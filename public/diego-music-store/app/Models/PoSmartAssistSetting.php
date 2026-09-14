<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PoSmartAssistSetting extends Model
{
    use HasFactory;

    protected $table = 'po_smart_assist_settings';

    protected $fillable = [
        'is_enabled',
        'min_buffer_percentage',
        'min_buffer_nominal',
        'include_pending_pos',
        'include_sales_projection',
        'warning_threshold_days',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'min_buffer_percentage' => 'integer',
        'min_buffer_nominal' => 'integer',
        'include_pending_pos' => 'boolean',
        'include_sales_projection' => 'boolean',
        'warning_threshold_days' => 'integer',
    ];

    /**
     * Get active settings with safe default fallbacks.
     */
    public static function getSettings(): static
    {
        try {
            if (Schema::hasTable('po_smart_assist_settings')) {
                $setting = static::first();
                if ($setting) {
                    return $setting;
                }
            }
        } catch (\Throwable $e) {
            // Fallback if DB table is not yet migrated
        }

        return new static([
            'is_enabled' => true,
            'min_buffer_percentage' => 20,
            'min_buffer_nominal' => 5000000,
            'include_pending_pos' => true,
            'include_sales_projection' => true,
            'warning_threshold_days' => 30,
        ]);
    }
}
