<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CheckoutHistory extends Model
{
    protected $table = 'checkout_history';

    protected $fillable = [
        'archived_chart_id', 'checked_out_by', 'checked_out_at',
        'expected_return_date', 'purpose', 'department', 'person',
        'returned_by', 'returned_at', 'notes', 'status'
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'returned_at' => 'datetime',
        'expected_return_date' => 'date',
    ];

    public function archivedChart()
    {
        return $this->belongsTo(ArchivedChart::class);
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active' && $this->expected_return_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) return 0;
        return (int) Carbon::today()->diffInDays($this->expected_return_date);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                     ->where('expected_return_date', '<', Carbon::today());
    }
}