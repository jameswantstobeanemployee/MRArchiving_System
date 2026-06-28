<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupConfiguration extends Model
{
    protected $fillable = [
        'name', 'backup_type', 'frequency', 'day_of_week',
        'day_of_month', 'time_of_day', 'destination_drive_id',
        'retention_count', 'is_active', 'last_run_at', 'next_run_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function destinationDrive()
    {
        return $this->belongsTo(ExternalDrive::class, 'destination_drive_id');
    }

    public function logs()
    {
        return $this->hasMany(BackupLog::class);
    }

    public function latestLog()
    {
        return $this->hasOne(BackupLog::class)->latest();
    }

    public function recentLogs()
    {
        return $this->hasMany(BackupLog::class)->latest()->limit(5);
    }
    public function sourceDrives()
    {
        return $this->belongsToMany(
            ExternalDrive::class,
            'backup_configuration_source_drives'
        );
    }
}
