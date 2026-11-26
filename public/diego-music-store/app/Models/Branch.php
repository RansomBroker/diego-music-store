<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    public const DAY_LABELS = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];

    public const DAY_SHORT_LABELS = [
        'monday' => 'Sen',
        'tuesday' => 'Sel',
        'wednesday' => 'Rab',
        'thursday' => 'Kam',
        'friday' => 'Jum',
        'saturday' => 'Sab',
        'sunday' => 'Min',
    ];

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
        'shift_schedules',
        'manager_id',
        'fonnte_token',
        'fonnte_whatsapp_number',
        'is_whatsapp_enabled',
        'is_active',
        'monthly_off_days_quota',
        'inventory_account_id',
        'interbranch_receivable_account_id',
        'interbranch_payable_account_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'attendance_radius_meters' => 'integer',
        'shift_schedules' => 'array',
        'is_whatsapp_enabled' => 'boolean',
        'is_active' => 'boolean',
        'monthly_off_days_quota' => 'integer',
    ];

    /**
     * Resolve shift start & end times (HH:MM:SS) for the given date based on day-of-week schedules,
     * falling back to the branch's default shift_start_time / shift_end_time.
     *
     * @return array{start: string, end: string}
     */
    public function getShiftTimesForDate(?\Carbon\CarbonInterface $date = null): array
    {
        $targetDate = $date ?? now();
        $isoDayMap = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];
        $dayKey = $isoDayMap[$targetDate->dayOfWeekIso] ?? 'monday';

        foreach ((array) ($this->shift_schedules ?? []) as $schedule) {
            $days = is_array($schedule['days'] ?? null) ? $schedule['days'] : [];
            if (in_array($dayKey, $days, true)) {
                $start = self::normalizeTimeToHis($schedule['start_time'] ?? null, $this->shift_start_time ?: '09:00:00');
                $end = self::normalizeTimeToHis($schedule['end_time'] ?? null, $this->shift_end_time ?: '17:00:00');

                return ['start' => $start, 'end' => $end];
            }
        }

        return [
            'start' => self::normalizeTimeToHis($this->shift_start_time, '09:00:00'),
            'end' => self::normalizeTimeToHis($this->shift_end_time, '17:00:00'),
        ];
    }

    public function getShiftScheduleSummary(): string
    {
        $schedules = array_values(array_filter(
            (array) ($this->shift_schedules ?? []),
            fn ($item) => ! empty($item['days']) && (! empty($item['start_time']) || ! empty($item['end_time']))
        ));

        if (empty($schedules)) {
            $start = $this->shift_start_time ? substr($this->shift_start_time, 0, 5) : '09:00';
            $end = $this->shift_end_time ? substr($this->shift_end_time, 0, 5) : '17:00';

            return "{$start} - {$end}";
        }

        $parts = [];
        foreach ($schedules as $schedule) {
            $dayLabels = array_map(
                fn ($day) => self::DAY_SHORT_LABELS[$day] ?? ucfirst((string) $day),
                (array) ($schedule['days'] ?? [])
            );
            $start = ! empty($schedule['start_time']) ? substr((string) $schedule['start_time'], 0, 5) : '09:00';
            $end = ! empty($schedule['end_time']) ? substr((string) $schedule['end_time'], 0, 5) : '17:00';
            $parts[] = implode(',', $dayLabels) . " {$start}-{$end}";
        }

        return implode(' | ', $parts);
    }

    protected static function normalizeTimeToHis(?string $time, string $fallback): string
    {
        $value = trim((string) ($time ?: $fallback));
        if ($value === '') {
            return $fallback;
        }

        $parts = explode(':', $value);
        $hour = str_pad((string) (int) ($parts[0] ?? 0), 2, '0', STR_PAD_LEFT);
        $minute = str_pad((string) (int) ($parts[1] ?? 0), 2, '0', STR_PAD_LEFT);
        $second = str_pad((string) (int) ($parts[2] ?? 0), 2, '0', STR_PAD_LEFT);

        return "{$hour}:{$minute}:{$second}";
    }

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

    /**
     * Get the SKU sequence counters associated with this branch.
     */
    public function skuSequences(): HasMany
    {
        return $this->hasMany(SkuSequence::class);
    }
}
