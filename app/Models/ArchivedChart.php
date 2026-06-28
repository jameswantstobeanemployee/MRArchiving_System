<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ArchivedChart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'case_number', 'admission_date', 'discharge_date',
        'archived_date', 'physical_location_id', 'digital_copy_path',
        'digital_copy_size', 'compression_status',  // <-- add this
        'total_pages', 'archived_by', 'status',
        'retention_period_years', 'retention_end_date',
        'destroyed_date', 'destroyed_reason', 'destroyed_by', 'notes'
    ];

    protected $casts = [
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'archived_date' => 'date',
        'retention_end_date' => 'date',
        'destroyed_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function physicalLocation()
    {
        return $this->belongsTo(FolderBox::class, 'physical_location_id');
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function destroyedBy()
    {
        return $this->belongsTo(User::class, 'destroyed_by');
    }

    public function checkoutHistory()
    {
        return $this->hasMany(CheckoutHistory::class);
    }

    public function currentCheckout()
    {
        return $this->hasOne(CheckoutHistory::class)->where('status', 'active')->latest();
    }

    public function locationHistory()
    {
        return $this->hasMany(LocationHistory::class);
    }

    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out';
    }

    public function isDestroyed(): bool
    {
        return $this->status === 'destroyed';
    }

    public function getDaysUntilRetentionAttribute(): ?int
    {
        if (!$this->retention_end_date) return null;
        return (int) Carbon::now()->diffInDays($this->retention_end_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->retention_end_date) return false;
        return $this->retention_end_date->isPast();
    }

    public function getRetentionLabelAttribute(): string
    {
        if ($this->retention_period_years === null) return 'Permanent';
        return "{$this->retention_period_years} years";
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->digital_copy_size;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('case_number', 'like', "%{$term}%")
              ->orWhereHas('patient', function ($pq) use ($term) {
                  $pq->where('first_name', 'like', "%{$term}%")
                     ->orWhere('last_name', 'like', "%{$term}%")
                     ->orWhere('medical_record_number', 'like', "%{$term}%");
              });
        });
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->whereNotNull('retention_end_date')
                     ->whereBetween('retention_end_date', [
                         Carbon::today(),
                         Carbon::today()->addDays($days)
                     ]);
    }
}
