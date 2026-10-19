<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StockOpnameAssignment extends Model
{
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'stock_opname_period_id',
        'stock_opname_preset_id',
        'assigned_user_id',
        'stock_opname_id',
        'status',
        'assigned_by',
        'assigned_at',
        'started_at',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $assignment): void {
            $period = StockOpnamePeriod::find($assignment->stock_opname_period_id);
            $preset = StockOpnamePreset::find($assignment->stock_opname_preset_id);
            $user = User::with('employee')->find($assignment->assigned_user_id);

            if (!$period || !$preset || !$user) {
                throw ValidationException::withMessages([
                    'assignments' => 'Periode, lokasi, dan karyawan wajib valid.',
                ]);
            }

            if (!$assignment->exists || $assignment->isDirty([
                'stock_opname_period_id',
                'stock_opname_preset_id',
                'assigned_user_id',
            ])) {
                if ((int) $period->branch_id !== (int) $preset->branch_id) {
                    throw ValidationException::withMessages([
                        'assignments' => 'Preset lokasi harus berasal dari cabang yang sama dengan periode opname.',
                    ]);
                }

                if (!$preset->is_active) {
                    throw ValidationException::withMessages([
                        'assignments' => 'Preset lokasi yang tidak aktif tidak dapat ditugaskan.',
                    ]);
                }

                $employeeBranchId = $user->employee?->branch_id;
                $userHasBranch = $user->branches()->where('branches.id', $period->branch_id)->exists();
                if ((int) $employeeBranchId !== (int) $period->branch_id && !$userHasBranch) {
                    throw ValidationException::withMessages([
                        'assignments' => 'Karyawan harus terdaftar pada cabang yang sama dengan periode opname.',
                    ]);
                }
            }

            if (!$assignment->exists) {
                if ($period->status !== StockOpnamePeriod::STATUS_DRAFT) {
                    throw ValidationException::withMessages([
                        'assignments' => 'Penugasan baru hanya dapat ditambahkan saat periode masih berstatus draft.',
                    ]);
                }

                $assignment->status = $assignment->status ?: self::STATUS_ASSIGNED;
                $assignment->assigned_at = $assignment->assigned_at ?: now();
                $assignment->assigned_by = $assignment->assigned_by ?: auth()->id();
            }
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(StockOpnamePeriod::class, 'stock_opname_period_id');
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(StockOpnamePreset::class, 'stock_opname_preset_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
