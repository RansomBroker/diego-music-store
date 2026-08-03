<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'sale_id',
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'device_name',
        'serial_number',
        'complaint',
        'technician_id',
        'status',
        'estimated_cost',
        'additional_charges',
        'total_cost',
        'notes',
        'completion_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost'     => 'decimal:2',
            'total_cost'         => 'decimal:2',
            'additional_charges' => 'array',
            'completion_date'    => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'received'      => 'Diterima',
            'diagnosing'    => 'Proses Diagnosa',
            'in_progress'   => 'Dikerjakan',
            'waiting_parts' => 'Menunggu Sparepart',
            'completed'     => 'Selesai Service',
            'picked_up'     => 'Siap / Sudah Diambil',
            'cancelled'     => 'Dibatalkan',
            default         => 'Diterima',
        };
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'received'      => 'info',
            'diagnosing'    => 'warning',
            'in_progress'   => 'primary',
            'waiting_parts' => 'amber',
            'completed'     => 'success',
            'picked_up'     => 'emerald',
            'cancelled'     => 'rose',
            default         => 'secondary',
        };
    }

    public function getTrackingUrlAttribute(): string
    {
        return url('/track-service/' . $this->ticket_code);
    }
}
