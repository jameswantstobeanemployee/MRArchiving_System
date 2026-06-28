<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDrive extends Model
{
    protected $fillable = [
        'name', 'drive_path', 'total_space', 'used_space',
        'is_primary', 'status', 'last_scanned_at'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    public static function getPrimary(): ?self
    {
        return self::where('is_primary', true)->where('status', 'active')->first();
    }

    public function getUsedPercentageAttribute(): float
    {
        if ($this->total_space == 0) return 0;
        return round(($this->used_space / $this->total_space) * 100, 1);
    }

    public function getAvailableSpaceAttribute(): int
    {
        return max(0, $this->total_space - $this->used_space);
    }

    public function getTotalSpaceFormattedAttribute(): string
    {
        return $this->formatBytes($this->total_space);
    }

    public function getUsedSpaceFormattedAttribute(): string
    {
        return $this->formatBytes($this->used_space);
    }

    public function getAvailableSpaceFormattedAttribute(): string
    {
        return $this->formatBytes($this->available_space);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1099511627776) return number_format($bytes / 1099511627776, 2) . ' TB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        return number_format($bytes / 1024, 2) . ' KB';
    }

    public function backupConfigurations()
    {
        return $this->hasMany(BackupConfiguration::class, 'destination_drive_id');
    }
}
