<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiHealthLog extends Model
{
    protected $fillable = [
        'scan_id', 'source', 'issue_type', 'severity',
        'issue_description', 'ai_reasoning', 'fix_action',
        'fix_payload', 'was_fixed', 'fix_status', 'fix_error', 'triggered_by'
    ];

    protected $casts = [
        'fix_payload' => 'array',
        'was_fixed'   => 'boolean',
    ];

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical'  => 'danger',
            'error'     => 'danger',
            'warning'   => 'warning',
            default     => 'info',
        };
    }
}