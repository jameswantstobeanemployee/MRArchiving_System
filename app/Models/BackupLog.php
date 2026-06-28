<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $table = 'backup_logs';

    protected $fillable = [
        'backup_configuration_id', 'status', 'start_time',
        'end_time', 'files_count', 'total_size', 'error_message'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function configuration()
    {
        return $this->belongsTo(BackupConfiguration::class, 'backup_configuration_id');
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->end_time) return null;
        $seconds = $this->start_time->diffInSeconds($this->end_time);
        if ($seconds < 60) return "{$seconds}s";
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        return "{$minutes}m {$secs}s";
    }
}